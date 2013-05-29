<?php

/**
 * Class eZClassSyncDataClass
 *
 * @property string Identifier
 * @property string ID
 * @property string InitialLanguageID
 * @property string IsContainer
 * @property string AlwaysAvailable
 * @property string ContentObjectName
 * @property string LanguageMask
 * @property string RemoteID
 * @property string SortField
 * @property string SortOrder
 * @property string URLAliasName
 */
class eZClassSyncDataClass extends eZClassSyncDataParams
{

    public function __construct()
    {
        foreach ($this->getDefinitions() as $property => $value) {
            $this->_data[$property] = $value['default'];
        }
        $this->RemoteID = md5(time() + rand(1, 10));
    }

    public function fillFromArray($array)
    {

    }

    public function fillFromClass($class)
    {
        foreach ($this->getDefinitions() as $property => $value) {
            $this->_data[$property] = $class->$property;
        }

        $this->defaultLanguage = $class->attribute('top_priority_language_locale');
        $names = $class->nameList();
        $descriptions = $class->descriptionList();
        foreach ($class->attribute('prioritized_languages') as $lang => $params) {
            $this->languages[$params->ID] = $lang;
            $this->_nameLang[$lang] = (!empty($names[$lang])) ? $names[$lang] : '';
            $this->_descriptionLang[$lang] = (!empty($descriptions[$lang])) ? $descriptions[$lang] : '';
        }
    }

    public function getDefinitions()
    {
        return array(
            'Identifier'        => array('json' => 'identifier', 'default' => 'new_class'),
            'ID'                => array('json' => null, 'default' => null),
            'InitialLanguageID' => array('json' => 'initial_language_id', 'default' => 2),
            'IsContainer'       => array('json' => 'is_container', 'default' => 0),
            'AlwaysAvailable'   => array('json' => 'always_available', 'default' => 0),
            'ContentObjectName' => array('json' => 'contentobject_name', 'default' => ''),
            'LanguageMask'      => array('json' => 'language_mask', 'default' => 3),
            'RemoteID'          => array('json' => 'remote_id', 'default' => null),
            'SortField'         => array('json' => 'sort_field', 'default' => 1),
            'SortOrder'         => array('json' => 'sort_order', 'default' => 1),
            'URLAliasName'      => array('json' => 'url_alias_name', 'default' => ''),
        );
    }

}
