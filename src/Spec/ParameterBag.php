<?php


namespace Wave\Swagger\Generator\Spec;


class ParameterBag extends GenericBag {

    const ADD_COLLISION_MERGE    = 1;
    const ADD_COLLISION_PREFER_NEW = 2;
    const ADD_COLLISION_PREFER_OLD = 3;

    /** @var Parameter[] */
    protected $_data = array();

    public function __construct(array $parameters = array()) {

        foreach($parameters as $parameter){
            $this->add(new Parameter($parameter));
        }

    }


    public function add($new){

        if(!$new instanceof Parameter)
            throw new \InvalidArgumentException("ParameterBag::add() requires instance of Parameter");

        foreach($this->_data as $i => $parameter){
            foreach($parameter->names as $name){
                foreach($new->names as $new_name){
                    if($name === $new_name){
                        // existing parameter isn't sure where it should be - so use this one instead
                        if($parameter->in === Parameter::IN_GUESS || $parameter->in === $new->in){
                            $this->_data[$i] = $new->merge($parameter);
                            return;
                        }
                    }
                }
            }

        }

        $this->_data[] = $new;
    }

    public function merge(GenericBag $bag){

        if(!$bag instanceof ParameterBag)
            throw new \InvalidArgumentException("ParameterBag::merge() can only be merged with another ParameterBag");


        foreach($bag->all() as $parameter){
            $this->add($parameter);
        }
    }

    public function toArray(){

        return array_map(function(Parameter $param){
            return $param->toArray();
        }, $this->_data);

    }

}