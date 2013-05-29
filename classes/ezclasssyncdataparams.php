<?php

class eZClassSyncDataParams
{

    public $languages = array();
    public $defaultLanguage = array();
    protected $_nameLang = array();
    protected $_descriptionLang = array();
    protected $_data = array();

    public function getName($lang)
    {
        return (!empty($this->_nameLang[$lang])) ? $this->_nameLang[$lang]
            : $this->_nameLang[$this->defaultLanguage];
    }

    public function getDescription($lang)
    {
        return (!empty($this->_descriptionLang[$lang])) ? $this->_descriptionLang[$lang]
            : $this->_descriptionLang[$this->defaultLanguage];
    }

    public function setName($value, $lang)
    {
        if (in_array($lang, $this->languages)) {
            $this->_nameLang[$lang] = $value;
        }
    }

    public function setDescription($value, $lang)
    {
        if (in_array($lang, $this->languages)) {
            $this->_descriptionLang[$lang] = $value;
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->getClassParamNames())) {
            $this->_data[$name] = $value;
        }

    }

    public function __get($name)
    {
        if (in_array($name, $this->getClassParamNames())) {
            return $this->_data[$name];
        }

        return null;
    }

    public function getClassParamNames()
    {
        return array_keys($this->getDefinitions());
    }

    public function getDefinitions()
    {
        return array();
    }
}
