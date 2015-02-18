<?php


namespace Wave\Swagger\Generator\Spec;


class GenericBag extends AbstractDefinition {

    public function add($item){
        $this->_data[] = $item;
    }

    public function merge(GenericBag $bag){
        $this->_data = array_merge($this->_data, $bag->_data);
    }

    public function all(){
        return $this->_data;
    }

}