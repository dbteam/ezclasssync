<?php

class eZClassSyncData
{

    /** @var eZClassSyncDataClass */
    private $_classData;
    private $_attributesData = array();

    public $Identifier = 'new_class';
    public $ID = null;

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
                        $this->loadFromString($string);
                    } else {
                        $this->loadFromFile($data);
                    }
                }

            }
        }
    }

    public function getClassName()
    {
//        if (!empty($this->_classData)) {
        return $this->_classData->Identifier;
//        }
    }

    public function loadFromFile($src) { }

    public function loadFromString($string) { }

    public function loadFromClass($class)
    {
        if (!is_object($class)) {
            return false;
        }

        $this->_classData = new eZClassSyncDataClass();
        $this->_classData->fillFromClass($class);

        foreach ($class->fetchAttributes() as $attribute) {
            $attr = new eZClassSyncDataAttribute();
            $this->_attributesData[] = $attr->fillFromClass($attribute, $this->_classData);
        }

        return true;
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
