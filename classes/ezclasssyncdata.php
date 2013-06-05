<?php

class eZClassSyncData
{

    /** @var eZClassSyncDataClass */
    private $_classData;
    private $_attributesData = array();

    public function __construct($data = null)
    {
        if ($data !== null) {
            if ($data instanceof eZContentClass) {
                $this->loadFromClass($data);
            } else {
                if (is_object($data)) {
                    //not supported
                } else {
                    if (is_array($data)) {
                        $this->loadFromString($data);
                    } else {
                        $this->loadFromFile($data);
                    }
                }
            }
        }
    }

    public function getClassName()
    {
        return $this->_classData->Identifier;
    }

    public function loadFromFile($src)
    {
        $this->loadFromString(json_decode(file_get_contents($jsonSrc), true));
    }

    public function loadFromString($string)
    {
        $this->_classData = new eZClassSyncDataClass();
        $this->_classData->fillFromArray($string);

        $i = 0;
        foreach ($string['attributes'] as $attributeName => $attributeData) {
            $attr = new eZClassSyncDataAttribute();
            $this->_attributesData[$attributeName] = $attr->fillFromArray($attributeName, $attributeData, $this->_classData);
            $this->_attributesData[$attributeName]->Position = ++$i;
        }
    }

    public function loadFromClass($class)
    {
        if (!is_object($class)) {
            return false;
        }

        $this->_classData = new eZClassSyncDataClass();
        $this->_classData->fillFromClass($class);

        foreach ($class->fetchAttributes() as $attribute) {
            $attr = new eZClassSyncDataAttribute();
            $this->_attributesData[$attribute->Identifier] = $attr->fillFromClass($attribute, $this->_classData);
        }

        return true;
    }

    public function attributeNames()
    {
        return array_keys($this->_attributesData);
    }

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->_attributesData);
    }

    /**
     * @param $name
     *
     * @return eZClassSyncDataAttribute
     */
    public function attribute($name)
    {
        return ($this->hasAttribute($name)) ? $this->_attributesData[$name] : null;
    }

    public function getClass()
    {
        return $this->_classData;
    }

    public function export2json()
    {
        $exportData = array();

        // class data
        foreach ($this->_classData->getDefinitions() as $key => $data) {
            if (!empty($data['json'])) {
                $exportData[$data['json']] = $this->_classData->$key;
            }
        }

        foreach ($this->_classData->languages as $lang) {
            $exportData['languages'][] = $lang;
            $exportData['translation'][$lang]['name'] = $this->_classData->getName($lang);
            $exportData['translation'][$lang]['description'] = $this->_classData->getDescription($lang);
        }

        // attributes
        $exportData['attributes'] = array();
        foreach ($this->_attributesData as $position => $data) {
            $attr = array();
            foreach ($data->getDefinitions() as $key => $value) {
                if (!empty($value['json'])) {
                    $attr[$value['json']] = $data->$key;
                }
            }
            $attr['translation'] = array();
            foreach ($this->_classData->languages as $lang) {
                $attr['translation'][$lang]['name'] = $data->getName($lang);
                $attr['translation'][$lang]['description'] = $data->getDescription($lang);
            }

            $exportData['attributes'][$data->Identifier] = $attr;
        }


        return $exportData;
    }

}
