<?php

class eZClassSync
{

    const DASH = '—';
    const SIDE_LEFT = 'l';
    const SIDE_RIGHT = 'r';
    const SIDE_BOTH = 'b';

    public $attributesToAdd = array();
    public $attributesToUpdate = array();
    public $attributesToDelete = array();

    private $_classParamsToUpdate = array();
    private $_classTranslationsToUpdate = array();
    private $_attributeParamsToUpdate = array();
    private $_attributeTranslationsToUpdate = array();
    private $_classParams = array();
    private $_attributeParams = array();

    /**
     * @var eZClassSyncData
     */
    public $jsonClass = null;
    /**
     * @var eZClassSyncData
     */
    public $originalClass = null;
    /**
     * @var eZContentClass
     */
    private $_contentClass = null;

    private $_compared = false;
    private $_classToInstall = false;
    private $_synced = false;

    public function __construct($jsonSrc = null)
    {
        if (!empty($jsonSrc)) {
            $this->jsonLoad($jsonSrc);
        }
    }

    public function jsonLoad($jsonSrc)
    {
        $data = json_decode(file_get_contents($jsonSrc), true);
        $this->jsonClass = new eZClassSyncData($data);

        $class = eZContentClass::fetchByIdentifier($this->jsonClass->getClass()->Identifier, true, eZContentClass::VERSION_STATUS_DEFINED);
        if ($class instanceof eZContentClass) {
            $this->_contentClass = $class;
            $this->originalClass = new eZClassSyncData($class);
            $this->jsonClass->getClass()->ID = $this->originalClass->getClass()->ID;
        }

        $_compared = false;
    }

    public function getClassName()
    {
        return $this->jsonClass->getClassName();
    }

    /**
     * @return bool
     */
    public function compare()
    {
        if ($this->_compared) {
            return true;
        }
        $this->_compared = true;

        if ($this->jsonClass === null) {
            // not yet loaded
            return false;
        }

        if ($this->originalClass === null) {
            // this will be an installation only
            $this->_classToInstall = true;

            foreach ($this->jsonClass->attributeNames() as $attribute) {
                $this->_attributeParams[] = array(
                    'identifier' => $attribute . ' [to install]',
                );
            }

            return true;
        }

        // compare class
        // 1: first, compare class properties
        foreach ($this->jsonClass->getClass()->getDefinitions() as $param => $options) {
            $value = $this->jsonClass->getClass()->$param;
            $classValue = $this->originalClass->getClass()->$param;
            $areSame = ($value == $classValue);

            if (!$areSame) {
                $this->_classParamsToUpdate[] = $param; //$options['json']; // underscored
            }

            $this->_classParams[] = array(
                'param'      => $param,
                'value'      => $value,
                'classValue' => $classValue,
                'isSame'     => $areSame,
                'isDefault'  => 1, //todo: remove
            );
        }

        // 2: compare class properties translations
        // get both languages
        $leftLanguages = $this->jsonClass->getClass()->languages;
        $rightLanguages = $this->originalClass->getClass()->languages;

        // merge to have all possible languages
        $bothLanguages = array_unique(array_merge($leftLanguages, $rightLanguages));

        foreach ($bothLanguages as $lang) {
            foreach (array('Name', 'Description') as $param) {
                $function = 'get' . $param;
                $value = (in_array($lang, $leftLanguages)) ? $this->jsonClass->getClass()->$function($lang)
                    : self::DASH;
                $classValue = (in_array($lang, $rightLanguages)) ? $this->originalClass->getClass()->$function($lang)
                    : self::DASH;

                $areSame = ($value == $classValue);
                if (!$areSame) {
                    $this->_classTranslationsToUpdate[] = array(
                        'lang'  => $lang,
                        'param' => 'set' . $param,
                        'value' => $value
                    );
                }

                $this->_classParams[] = array(
                    'param'      => '<code>' . $lang . '</code> ' . $param,
                    'value'      => $value,
                    'classValue' => $classValue,
                    'isSame'     => $areSame,
                    'isDefault'  => 1, //todo: remove
                );
            }
        }

        // 3: ok, now time for attributes:
        $leftAttributesList = $this->jsonClass->attributeNames();
        $rightAttributesList = $this->originalClass->attributeNames();

        // merge to have all possible languages
        $bothAttributesList = array_unique(array_merge($leftAttributesList, $rightAttributesList));

        foreach ($bothAttributesList as $attribute) {

            $params = array();
            $update = false;
            $identifier = $attribute;
            $side = self::SIDE_BOTH;

            if (!$this->jsonClass->hasAttribute($attribute)) {
                $this->attributesToDelete[] = $attribute;
                $identifier .= ' [drop]';
                $side = self::SIDE_LEFT;
            } elseif (!$this->originalClass->hasAttribute($attribute)) {
                $this->attributesToAdd[] = $attribute;
                $identifier .= ' [add]';
                $side = self::SIDE_RIGHT;
            }

            foreach (eZClassSyncDataAttribute::getDefinitions() as $param => $properties) {

                $value = ($this->jsonClass->hasAttribute($attribute)) ? $this->jsonClass->attribute($attribute)->$param
                    : self::DASH;
                $classValue = ($this->originalClass->hasAttribute($attribute))
                    ? $this->originalClass->attribute($attribute)->$param
                    : self::DASH;

                $areSame = ($value == $classValue);
                if (!$areSame && $side == self::SIDE_BOTH) {
                    $this->_attributeParamsToUpdate[] = array('attr' => $attribute, 'param' => $param);
                    $update = true;
                }

                $params[] = array(
                    'param'      => $param,
                    'value'      => $value,
                    'classValue' => $classValue,
                    'isSame'     => $areSame,
                    'isDefault'  => 1, //todo: remove
                );
            }

            // we still can access languages :)
            // todo: check that this loop can be made as another method, sice we've already got nearly same one above
            foreach ($bothLanguages as $lang) {
                foreach (array('Name', 'Description') as $param) {
                    $function = 'get' . $param;
                    $value = (in_array($lang, $leftLanguages) && $this->jsonClass->hasAttribute($attribute))
                        ? $this->jsonClass->attribute($attribute)->$function($lang)
                        : self::DASH;
                    $classValue = (in_array($lang, $rightLanguages) && $this->originalClass->hasAttribute($attribute))
                        ? $this->originalClass->attribute($attribute)->$function($lang)
                        : self::DASH;

                    $areSame = ($value == $classValue);
                    if (!$areSame && $side == self::SIDE_BOTH) {
                        $this->_attributeTranslationsToUpdate[] = array(
                            'attr'  => $attribute,
                            'lang'  => $lang,
                            'param' => 'set' . $param,
                            'value' => $value
                        );
                        $update = true;
                    }

                    $params[] = array(
                        'param'      => '<code>' . $lang . '</code> ' . $param,
                        'value'      => $value,
                        'classValue' => $classValue,
                        'isSame'     => $areSame,
                        'isDefault'  => 1, //todo: remove
                    );
                }
            }

            if ($update) {
                $this->attributesToUpdate[] = $attribute;
            }

            $this->_attributeParams[] = array(
                'identifier' => $identifier,
                'values'     => $params,
                'side'       => $side,
            );
        }

        // ok, attributes are now finished too

        return true;
    }

    public function sync()
    {
        $this->compare();

        if ($this->_synced) {
            return null;
        }
        $this->_synced = true;
        $result = array();

        if ($this->_classToInstall) {

            /**
             * INSTALL
             */

            $language = eZContentLanguage::topPriorityLanguage();
            $result[] = 'Attempting to install class <code>' . $this->jsonClass->getClassName() . '</code>, language '
                . $language->Name . ' (' . $language->Locale . ') used as default one';

            $user = eZUser::currentUser();
            $user_id = $user->attribute('contentobject_id');
            $this->_contentClass = eZContentClass::create($user_id, array(), $language->Locale);
            $this->_contentClass->setName($this->jsonClass->getClassName(), $language->Locale);

            foreach (eZClassSyncDataClass::getDefinitions() as $param => $options) {
//                if ($param == 'RemoteID') {
//                    continue;
//                }
                if ($options['json'] != null) {
                    $this->_contentClass->$param = $this->jsonClass->getClass()->$param;
                }
            }

            // update class translations
            foreach ($this->jsonClass->getClass()->languages as $translationData) {
                $this->_contentClass->setName($translationData['value'], $translationData['lang']);
                $this->_contentClass->setDescription($translationData['value'], $translationData['lang']);
            }

            $this->_contentClass->store();
            $editLanguageID = eZContentLanguage::idByLocale($language->Locale);
            $this->_contentClass->setAlwaysAvailableLanguageID($editLanguageID);
            $ClassID = $this->_contentClass->attribute('id');
            $ClassVersion = $this->_contentClass->attribute('version');
            $ingroup = eZContentClassClassGroup::create($ClassID, $ClassVersion, 1, 'Content');
            $ingroup->store();

            $classAttributes = array();

            // add attributes
            foreach ($this->jsonClass->attributeNames() as $attrName) {
                $newAttribute = eZContentClassAttribute::create($this->_contentClass->ID, $this->jsonClass->attribute($attrName)->DataTypeString, array(), $this->jsonClass->getClass()->defaultLanguage);
                $newAttribute->Identifier = $attrName;

                foreach (eZClassSyncDataAttribute::getDefinitions() as $param => $data) {
                    $newAttribute->$param = $this->jsonClass->attribute($attrName)->$param;
                }

                foreach ($this->jsonClass->getClass()->languages as $lang) {
                    $newAttribute->setName($this->jsonClass->attribute($attrName)->getName($lang), $lang);
                    $newAttribute->setDescription($this->jsonClass->attribute($attrName)->getDescription($lang), $lang);
                }

                $dataType = $newAttribute->dataType();
                $dataType->initializeClassAttribute($newAttribute);
                $newAttribute->setAttribute('version', eZContentClass::VERSION_STATUS_DEFINED);
                $newAttribute->store();
                $classAttributes[] = $newAttribute;

                $result[] = 'Attribute ' . $attrName . ' added';
            }

            $this->_contentClass->storeVersioned($classAttributes, eZContentClass::VERSION_STATUS_DEFINED);

            $result[] = 'Class saved';

        } elseif ($this->getTotalDifferences() > 0) {

            /**
             * UPDATE
             */

            // update class params
            foreach ($this->_classParamsToUpdate as $param) {
                $this->_contentClass->$param = $this->jsonClass->getClass()->$param;
                $result[] = 'Class property <code>' . $param
                    . '</code> changed to ' . htmlspecialchars($this->jsonClass->getClass()->$param);
            }
            // update class translations
            foreach ($this->_classTranslationsToUpdate as $translationData) {
                $this->_contentClass->$translationData['param']($translationData['value'], $translationData['lang']);
                $result[] = 'Translation updated for ' . str_replace('set', '', $translationData['param']);
            }

            // attributes
            $classAttributes = $this->_contentClass->fetchAttributes();

            // remove old ones
            for ($k = count($classAttributes) - 1; $k >= 0; $k--) {
                if (in_array($classAttributes[$k]->Identifier, $this->attributesToDelete)) {
                    $result[] = 'Attribute ' . $classAttributes[$k]->Identifier . ' (' . $k . ') deleted';
                    unset($classAttributes[$k]);
                }
            }

            // update
            foreach ($classAttributes as $k => $v) {
                if (in_array($v->Identifier, $this->attributesToUpdate)) {
                    //params
                    foreach ($this->_attributeParamsToUpdate as $data) {
                        if ($data['attr'] == $v->Identifier) {
                            $classAttributes[$k]->$data['param'] = $this->jsonClass->attribute($v->Identifier)->$data['param'];
                            $result[]
                                = 'Attribute <code>' . $v->Identifier . '</code> (' . $k . ') param <code>'
                                . $data['param']
                                . '</code> updated to ' . htmlspecialchars($classAttributes[$k]->$data['param']);
                        }
                    }

                    // translations
                    foreach ($this->_attributeTranslationsToUpdate as $data) {
                        if ($data['attr'] == $v->Identifier) {
                            $classAttributes[$k]->$data['param']($data['value'], $data['lang']);
                            $result[] = 'Attribute ' . $v->Identifier . ' <code>'
                                . str_replace('set', '', $data['param']) . '</code> translation updated';
                        }
                    }
                }
            }

            // add new ones
            foreach ($this->attributesToAdd as $attrName) {
                $newAttribute = eZContentClassAttribute::create($this->_contentClass->ID, $this->jsonClass->attribute($attrName)->DataTypeString, array(), $this->jsonClass->getClass()->defaultLanguage);
                $newAttribute->Identifier = $attrName;

                foreach (eZClassSyncDataAttribute::getDefinitions() as $param => $data) {
                    $newAttribute->$param = $this->jsonClass->attribute($attrName)->$param;
                }

                foreach ($this->jsonClass->getClass()->languages as $lang) {
                    $newAttribute->setName($this->jsonClass->attribute($attrName)->getName($lang), $lang);
                    $newAttribute->setDescription($this->jsonClass->attribute($attrName)->getDescription($lang), $lang);
                }

                $dataType = $newAttribute->dataType();
                $dataType->initializeClassAttribute($newAttribute);
                $newAttribute->setAttribute('version', eZContentClass::VERSION_STATUS_DEFINED);
                $newAttribute->store();
                $classAttributes[] = $newAttribute;

                $result[] = 'Attribute ' . $attrName . ' added';
            }

            $this->_contentClass->storeVersioned($classAttributes, eZContentClass::VERSION_STATUS_DEFINED);
            // todo remove languages

        } else {

        }

        return $result;
    }

//    private function _setClassInitialLang($newInitialLanguageID)
//    {
//        $this->_contentClass->setAttribute('initial_language_id', $newInitialLanguageID);
//        $this->_contentClass->setAlwaysAvailableLanguageID($newInitialLanguageID);
//    }
//
//    private function _removeLanguage($languageID)
//    {
//        return $this->_contentClass->removeTranslation($languageID);
//    }

    public function getClassParamCompareResults()
    {
        return $this->_classParams;
    }

    public function getClassAttributesCompareResults()
    {
        return $this->_attributeParams;
    }

    public function getTotalDifferences()
    {
        return count($this->_classParamsToUpdate)
        + count($this->_classTranslationsToUpdate)
        + count($this->_attributeParamsToUpdate)
        + count($this->_attributeTranslationsToUpdate)
        + count($this->attributesToAdd)
        + count($this->attributesToDelete);
    }
}
