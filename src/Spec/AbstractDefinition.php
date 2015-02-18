<?php


namespace Wave\Swagger\Generator\Spec;


abstract class AbstractDefinition {

    protected $_data = array();

    public function __set($property, $value){
        $method = 'set' . str_replace(' ', '', ucwords(str_replace('-', ' ', $property)));
        if(method_exists($this, $method))
            $this->_data[$property] = $this->$method($value);
        else {
            $this->_data[$property] = $value;
        }
    }

    public function __get($property){
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('-', ' ', $property)));
        if(method_exists($this, $method))
            return $this->$method();
        else {
            return $this->__isset($property) ? $this->_data[$property] : null;
        }
    }

    public function __isset($property){
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('-', ' ', $property)));
        if(method_exists($this, $method))
            return $this->$method();
        else
            return isset($this->_data[$property]);
    }

    public function toArray(){
        $self = array();
        foreach(array_keys($this->_data) as $key) {
            if ($this->$key instanceof AbstractDefinition)
                $self[$key] = $this->$key->toArray();
            else
                $self[$key] = $this->$key;

            if(empty($self[$key]))
                unset($self[$key]);

        }
        return $self;
    }

}