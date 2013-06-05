<?php

class eZClassSyncDataAttribute extends eZClassSyncDataParams
{

    /**
     * @param $class
     * @param $parent eZClassSyncDataClass
     */
    public function fillFromClass($class, $parent)
    {
//        $this->_data['Identifier'] = $class->Identifier;

        foreach ($this->getDefinitions() as $property => $value) {
            $this->_data[$property] = $class->$property;
        }

        $this->languages = $parent->languages;
        $this->defaultLanguage = $parent->defaultLanguage;

        $names = $class->nameList();
        $descriptions = $class->descriptionList();

        foreach ($this->languages as $lang) {
            $this->_nameLang[$lang] = (!empty($names[$lang])) ? $names[$lang] : '';
            $this->_descriptionLang[$lang] = (!empty($descriptions[$lang])) ? $descriptions[$lang] : '';
        }

        return $this;
    }

    public function fillFromArray($attributeName, $attributeData, $parent)
    {
        foreach ($this->getDefinitions() as $property => $value) {
            if ($value['json'] !== null) {
                $this->_data[$property] = (array_key_exists($value['json'], $attributeData))
                    ? $attributeData[$value['json']] : $value['default'];
            }
        }

        $this->_data['Identifier'] = $attributeName;

        //todo: directly navigate parent, just keep reference in obj
        $this->languages = $parent->languages;
        $this->defaultLanguage = $parent->defaultLanguage;

        $translations = $attributeData['translation'];

        foreach ($this->languages as $lang) {
            $this->_nameLang[$lang] = (!empty($translations[$lang]['name']))
                ? $translations[$lang]['name'] : '';
            $this->_descriptionLang[$lang] = (!empty($translations[$lang]['description']))
                ? $translations[$lang]['description'] : '';
        }

        return $this;
    }

    public static function getDefinitions()
    {
        return array(
            'Identifier'             => array('json' => null, 'default' => 'new_attribute'),
            'DataTypeString'         => array('json' => 'data_type_string', 'default' => 'ezstring'),
            'IsInformationCollector' => array('json' => 'is_information_collector', 'default' => 0),
            'IsRequired'             => array('json' => 'is_required', 'default' => 0),
            'IsSearchable'           => array('json' => 'is_searchable', 'default' => 1),
            'CanTranslate'           => array('json' => 'can_translate', 'default' => 1),
            'Position'               => array('json' => null, 'default' => -1),
            'Category'               => array('json' => 'category', 'default' => ''),
            'DataFloat1'             => array('json' => 'data_float_1', 'default' => 0),
            'DataFloat2'             => array('json' => 'data_float_2', 'default' => 0),
            'DataFloat3'             => array('json' => 'data_float_3', 'default' => 0),
            'DataFloat4'             => array('json' => 'data_float_4', 'default' => 0),
            'DataInt1'               => array('json' => 'data_int1', 'default' => 0),
            'DataInt2'               => array('json' => 'data_int2', 'default' => 0),
            'DataInt3'               => array('json' => 'data_int3', 'default' => 0),
            'DataInt4'               => array('json' => 'data_int4', 'default' => 0),
            'DataText1'              => array('json' => 'data_text1', 'default' => 1),
            'DataText2'              => array('json' => 'data_text2', 'default' => 1),
            'DataText3'              => array('json' => 'data_text3', 'default' => 1),
            'DataText4'              => array('json' => 'data_text4', 'default' => 1),
            'DataText5'              => array('json' => 'data_text5', 'default' => 1),
        );
    }
}
