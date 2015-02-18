<?php


namespace Wave\Swagger\Generator\Spec\Parameter;

use Wave\Swagger\Generator\Spec\Parameter;

class BodyParameter extends Parameter {


    public function __get($property) {
        if(isset($this->shim_properties[$property])){
            return $this->getFromSchema($this->shim_properties[$property]);
        }
        return parent::__get($property);
    }

    public function __set($property, $value){

        if(isset($this->shim_properties[$property])){
            $this->setOnSchema($this->shim_properties[$property], $value);
        }
        else {
            parent::__set($property, $value);
        }

    }

    private function getFromSchema($property){
        if(isset($this->_data['schema'], $this->_data['schema'][$property])){
            return $this->_data['schema'][$property];
        }
        return null;
    }

    private function setOnSchema($property, $value) {

        if(!isset($this->_data['schema']))
            $this->_data['schema'] = array();

        $this->_data['schema'][$property] = $value;

    }



}
