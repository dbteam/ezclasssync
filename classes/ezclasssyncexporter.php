<?php


class eZClassSyncExporter
{

    private $_class = null;
    private $_classLoaded = false;
    private $_classData;

    public function loadById($id)
    {
        if ($this->_classLoaded === false) {
            $this->_class = eZContentClass::fetch($id);
            $this->_classLoaded = true;
            $this->_classData = new eZClassSyncData();
            $this->_classData->loadFromClass($this->_class);
        }

        return $this->classLoaded();
    }

    public function className() {
        return $this->_class->Identifier;
    }

    public function classLoaded()
    {
        return ($this->_classLoaded && is_object($this->_classData));
    }

    public function export2json() {
        return $this->_classData->export2json();
    }
}
