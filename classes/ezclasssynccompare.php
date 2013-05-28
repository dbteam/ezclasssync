<?php

/**
 * Class eZClassSyncCompare
 *
 * @property eZContentClass _originalClass
 */
class eZClassSyncCompare
{

    public $classIdentifier = 'new_class';

    public $attributesToDrop = array();
    public $attributesToAdd = array();
    public $attributesToUpdate = array();
    public $attributePropertiesToUpdate = array();
    public $attributeTranslationsToUpdate = array();
    public $classPropertiesToUpdate = array();
    public $classTranslationsToUpdate = array();
    public $classToUpdate = false;

    private $_syncClass = array();
    private $_originalClass = null;
    private $_classAttrList = array();
    private $_attrList = array();
    private $_datatypes = null;
    private $_originalClassDefaultLang = 'eng-GB';
    private $_originalClassLangs = array('eng-GB');

    public $compareClassResults = array();
    public $compareAttributesResults = array();

    public function __construct()
    {
        eZDataType::loadAndRegisterAllTypes();
        $this->_datatypes = eZDataType::registeredDataTypes();

        $this->_classAttrList = array(
            'identifier'          => 'new_class',
            'initial_language_id' => 2,
            'is_container'        => 0,
            'always_available'    => 0,
            'contentobject_name'  => '?',
            'language_mask'       => 4,
            'remote_id'           => md5(rand(0, 100)),
            'sort_field'          => 11,
            'sort_order'          => 1,
            'url_alias_name'      => '?',
        );

        $this->_attrList = array(
            'data_type_string'         => 'ezstring',
            'is_information_collector' => 0,
            'is_required'              => 0,
            'is_searchable'            => 1,
            'can_translate'            => 1,
            'position'                 => -1,
            'category'                 => '',
            'data_float_1'             => 0,
            'data_float_2'             => 0,
            'data_float_3'             => 0,
            'data_float_4'             => 0,
            'data_int1'                => 0,
            'data_int2'                => 0,
            'data_int3'                => 0,
            'data_int4'                => 0,
            'data_text1'               => 1,
            'data_text2'               => 1,
            'data_text3'               => 1,
            'data_text4'               => 1,
            'data_text5'               => 1,
        );
    }

    public function underscore2CamelCase($str)
    {
        // Split string in words.
        $words = explode('_', strtolower($str));

        $return = '';
        foreach ($words as $word) {

            // special conditions for eZp
            if (in_array($word, array('id', 'url'))) {
                $return .= strtoupper($word);
            } elseif ($word == 'contentobject') {
                $return .= 'ContentObject';
            } else {
                $return .= ucfirst(trim($word));
            }
        }

        return $return;
    }

    public function underscore2Name($str)
    {
        $words = explode('_', strtolower($str));

        $return = '';
        foreach ($words as $word) {
            $return .= ' ' . ucfirst(trim($word));
        }

        //(?<!\ ) - http://us.php.net/manual/en/regexp.reference.assertions.php
        $return = preg_replace('/(?<!\ )[0-9]/', ' $0', $return);

        return trim($return);
    }

    private function _loadSyncData($file)
    {
        $this->_syncClass = json_decode(file_get_contents($file), true);

        if (!is_array($this->_syncClass)) {
            return false;
        }

        $this->classIdentifier = $this->_syncClass['identifier'];

        // add position attribute
        $i = 0;
        foreach ($this->_syncClass['attributes'] as $k => $v) {
            $this->_syncClass['attributes'][$k]['position'] = ++$i;
        }

        return true;
    }

    private function _loadOriginalClass()
    {
        $this->_originalClass = eZContentClass::fetchByIdentifier($this->classIdentifier, true, eZContentClass::VERSION_STATUS_DEFINED);
        $this->_originalClassDefaultLang = $this->_originalClass->attribute('top_priority_language_locale');

        $this->_originalClassLangs = array();
        foreach ($this->_originalClass->attribute('prioritized_languages') as $lang => $params) {
            $this->_originalClassLangs[$params->ID] = $lang;
        }
    }

    public function compare($file)
    {
        if (!$this->_loadSyncData($file)) {
            return -1;
        }

        $this->_loadOriginalClass();
        if (!is_object($this->_originalClass)) {
            return -2;
        }

        // compare
        $diff = $this->_compareClassParams();
        $diff += $this->_compareClassAttributes();

        return $diff;
    }

    private function _compareClassParams()
    {
        $differences = 0;
        // reset array
        $this->compareClassResults = array();

        // compare
        foreach ($this->_classAttrList as $attrName => $defaultValue) {
            $ezAttrName = $this->underscore2CamelCase($attrName);
            $isDefault = array_key_exists($attrName, $this->_syncClass);
            $value = ($isDefault) ? $this->_syncClass[$attrName] : $defaultValue;
            $classValue = $this->_originalClass->$ezAttrName;
            $isSame = ($value == $classValue);
            if (!$isSame) {
                $differences++;
                $this->classPropertiesToUpdate[] = $attrName;
            }

            $this->compareClassResults[] = array(
                'param'      => $attrName,
                'isDefault'  => $isDefault,
                'value'      => var_export($value, true),
                'classValue' => var_export($classValue, true),
                'isSame'     => (int)$isSame,
            );
        }

        // compare translations
        foreach (array('name' => 'nameList', 'description' => 'descriptionList') as $name => $attr) {
            foreach ($this->_originalClassLangs as $langCode) {
                $desc = $this->_originalClass->$attr();
                $value = null;
                if (!empty($this->_syncClass['translation'])) {
                    if (!empty($this->_syncClass['translation'][$langCode])
                        && !empty($this->_syncClass['translation'][$langCode][$name])
                    ) {
                        $value = $this->_syncClass['translation'][$langCode][$name];
                    }
                }

                $classValue = (array_key_exists($langCode, $desc)) ? $desc[$langCode] : '—';
                $isSame = ($value == $classValue);
                if (!$isSame) {
                    $differences++;
                    $this->classTranslationsToUpdate[] = array(ucfirst($name), empty($value) ? ucfirst($name)
                        : $value, $langCode);
                }

                $this->compareClassResults[] = array(
                    'param'      => $name . ':' . $langCode,
                    'value'      => (empty($value)) ? '—' : $value,
                    'classValue' => $classValue,
                    'isDefault'  => (int)(!empty($value)),
                    'isSame'     => (int)$isSame,
                );
            }
        }

        if ($differences > 0) {
            $this->classToUpdate = true;
        }

        return $differences;
    }

    private function _compareClassAttributes()
    {
        $differences = 0;

        // reset array
        $this->compareAttributesResults = array();
        $existingInClass = array();

        // first pass foreach existing class attributes and their position
        foreach ($this->_originalClass->fetchAttributes() as $attribute) {

            $values = array();
            $existsInSync = array_key_exists($attribute->Identifier, $this->_syncClass['attributes']);

            $propertiesToUpdate = array();
            foreach ($this->_attrList as $attrName => $defaultValue) {
                $ezAttrName = $this->underscore2CamelCase($attrName);
                $isDefault = $existsInSync && array_key_exists($attrName, $this->_syncClass['attributes'][$attribute->Identifier]);
                $value = ($isDefault) ? $this->_syncClass['attributes'][$attribute->Identifier][$attrName]
                    : $defaultValue;
                $classValue = $attribute->$ezAttrName;
                $isSame = ($existsInSync && $value == $classValue);
                if (!$isSame) {
                    $differences++;
                    $propertiesToUpdate[] = $attrName;
                }

                $values[] = array(
                    'param'      => $attrName,
                    'isDefault'  => $isDefault || !$existsInSync,
                    'value'      => ($existsInSync) ? var_export($value, true) : '—',
                    'classValue' => var_export($classValue, true),
                    'isSame'     => (int)$isSame,
                    'side'       => ($existsInSync) ? 'b' : 'r', // on which side data will be: left, right both
                );
            }

            // compare translations
            foreach (array('name' => 'nameList', 'description' => 'descriptionList') as $name => $attr) {
                foreach ($this->_originalClassLangs as $langCode) {
                    $desc = $attribute->$attr();
                    $value = null;
                    if (!empty($this->_syncClass['attributes'][$attribute->Identifier]['translation'])) {
                        if (!empty($this->_syncClass['attributes'][$attribute->Identifier]['translation'][$langCode])
                            && !empty($this->_syncClass['attributes'][$attribute->Identifier]['translation'][$langCode][$name])
                        ) {
                            $value = $this->_syncClass['attributes'][$attribute->Identifier]['translation'][$langCode][$name];
                        }
                    }
                    $classValue = (array_key_exists($langCode, $desc)) ? $desc[$langCode] : '—';
                    $isSame = ($value == $classValue);
                    if (!$isSame) {
                        $differences++;
                        // data for sync
                        $this->attributeTranslationsToUpdate[$attribute->Identifier][] = array(ucfirst($name), (empty($value))
                            ? $attribute->Identifier : $value, $langCode);
                    }

                    $values[] = array(
                        'param'      => $name . ':' . $langCode,
                        'value'      => (empty($value)) ? '—' : $value,
                        'classValue' => $classValue,
                        'isDefault'  => (int)(!empty($value)),
                        'isSame'     => (int)$isSame,
                        'side'       => 'b',
                        'drop'       => (int)(!in_array($langCode, $this->_syncClass['languages']))
                    );
                }
            }


            // ok
            if (count($propertiesToUpdate) && $existsInSync) {
                $this->attributesToUpdate[] = $attribute->Identifier;
                $this->attributePropertiesToUpdate[$attribute->Identifier] = $propertiesToUpdate;
            }

            if ($existsInSync) {
                $existingInClass[] = $attribute->Identifier;
            } else {
                $this->attributesToDrop[] = $attribute->Identifier;
            }

            $this->compareAttributesResults[] = array(
                'identifier' => $attribute->Identifier . (($existsInSync) ? '' : ' [to drop]'),
                'values'     => $values,
            );
        }

        // still we can have some attributes that are in sync class, but not in original class
        foreach ($this->_syncClass['attributes'] as $identifier => $attributes) {

            if (in_array($identifier, $existingInClass)) {
                continue;
            }

            $values = array();

            foreach ($this->_attrList as $attrName => $defaultValue) {
                $differences++;
                $isDefault = array_key_exists($attrName, $attributes);

                $values[] = array(
                    'param'      => $attrName,
                    'isDefault'  => (int)$isDefault,
                    'value'      => ($isDefault) ? var_export($attributes[$attrName], true) : $defaultValue,
                    'classValue' => '—',
                    'isSame'     => 0,
                    'side'       => 'l'
                );
            }

            $this->compareAttributesResults[] = array(
                'identifier' => $identifier . ' [new]',
                'values'     => $values,
            );

            $this->attributesToAdd[] = $identifier;
        }

        return $differences;
    }

    public function storeChanges()
    {
        $stored = false;
        $result = array();

        if ($this->classToUpdate) {
            foreach ($this->classPropertiesToUpdate as $attribute) {
                $this->_originalClass->setAttribute($attribute, $this->_syncClass[$attribute]);
                $result[] = 'Property ' . $attribute . ' changed';
                $stored = true;
            }
        }

        $classAttributes = $this->_originalClass->fetchAttributes();

        if (!empty($this->attributesToDrop)) {
            $keysToRemove = array();
            foreach ($classAttributes as $k => $attribute) {
                /* @var $attribute eZContentClassAttribute */
                if (in_array($attribute->Identifier, $this->attributesToDrop)) {
                    $result[] = 'Property ' . $attribute->Identifier . ' deleted';
                    $stored = true;
                    if (!$attribute->removeThis(true)) {
                        $dataType = $attribute->dataType();
                        $removeInfo = $dataType->classAttributeRemovableInformation($attribute);
                        $result[] = $removeInfo;
                    } else {
                        $keysToRemove[] = $k;
                    }
                }
            }

            // remove attributes to don't store them again!
            foreach ($keysToRemove as $k) {
                unset($classAttributes[$k]);
            }
        }

        // class desc translation
        foreach ($this->classTranslationsToUpdate as $params) {
            $func = 'set' . $params[0];
            $this->_originalClass->$func($params[1], $params[2]);
            $result[] = $params[0] . ' translation changed for ' . $params[2];
            $stored = true;
        }

        // attributes
        if (!empty($this->attributesToUpdate)) {
            foreach ($classAttributes as $attribute) {
                /* @var $attribute eZContentClassAttribute */
                if (in_array($attribute->Identifier, $this->attributesToUpdate)) {
                    foreach ($this->attributePropertiesToUpdate[$attribute->Identifier] as $property) {
                        $eZName = $this->underscore2CamelCase($property);
                        $attribute->$eZName = $this->_syncClass['attributes'][$attribute->Identifier][$property];
                        $result[] = 'Attribute ' . $attribute->Identifier . ' property ' . $property . ' changed';
                        $stored = true;
                    }
                }
            }
        }

        // attributes translation
        foreach ($classAttributes as $k => $attribute) {
            if (array_key_exists($attribute->Identifier, $this->attributeTranslationsToUpdate)) {
                foreach ($this->attributeTranslationsToUpdate[$attribute->Identifier] as $params) {
                    $func = 'set' . $params[0];
                    $classAttributes[$k]->$func($params[1], $params[2]);
                    $result[] = 'attribute ' . $attribute->Identifier . ' ' . $params[0]
                        . ' translation changed for ' . $params[2];
                    $stored = true;
                }
            }
        }

        // attributes to add
        if (!empty($this->attributesToAdd)) {
            foreach ($this->attributesToAdd as $attrName) {
                $newAttribute = eZContentClassAttribute::create($this->_originalClass->ID, $this->_syncClass['attributes'][$attrName]['data_type_string'], array(), $this->_originalClassDefaultLang);
                $newAttribute->Identifier = $attrName;

                foreach ($this->_attrList as $param => $v) {
                    $eZName = $this->underscore2CamelCase($param);
                    $newAttribute->$eZName = $this->_syncClass['attributes'][$attrName][$param];
                }

                // translations
                foreach ($this->_originalClassLangs as $lang) {
                    foreach (array('name' => 'setName', 'description' => 'setDescription') as $keys => $params) {
                        $translation = $this->underscore2Name($attrName);
                        if (!empty($this->_syncClass['attributes'][$attrName]['translation'][$lang][$keys])) {
                            $translation = $this->_syncClass['attributes'][$attrName]['translation'][$lang][$keys];
                        }
                        $newAttribute->$params($translation, $lang);
                    }
                }
                $dataType = $newAttribute->dataType();
                $dataType->initializeClassAttribute($newAttribute);
                $newAttribute->setAttribute('version', eZContentClass::VERSION_STATUS_DEFINED);
                $newAttribute->store();
                $classAttributes[] = $newAttribute;
                $result[] = 'New attribute ' . $attrName . ' added';
                $stored = true;
            }
        }

        if ($stored) {
            $this->_originalClass->storeVersioned($classAttributes, eZContentClass::VERSION_STATUS_DEFINED);
//            $this->_loadOriginalClass();
        }

        foreach ($this->_originalClassLangs as $id => $lang) {
            if (!in_array($lang, $this->_syncClass['languages'])) {
                if ($this->removeLanguage($id)) {
                    $result[] = 'Language ' . $lang . ' will be dropped';
                } else {
                    $result[] = 'Cannot remove language ' . $lang;
                }
            }
        }

        $defaultLang = array_shift(@array_values($this->_syncClass['languages']));
        $language = eZContentLanguage::fetchByLocale($defaultLang);

        if ($this->_originalClassDefaultLang != $language->Locale) {
            $this->setClassInitialLang($language->Locale);
            $result[] = $language->Locale . ' set as main laguage';
        }

        return $result;
    }


    /// helpers

    public function setClassInitialLang($newInitialLanguageID)
    {
        $this->_originalClass->setAttribute('initial_language_id', $newInitialLanguageID);
        $this->_originalClass->setAlwaysAvailableLanguageID($newInitialLanguageID);
    }

    public function removeLanguage($languageID)
    {
        return $this->_originalClass->removeTranslation($languageID);
    }
}
