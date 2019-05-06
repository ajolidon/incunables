<?php

namespace App\Helper;


trait ConstantTrait
{
    public function getConstants(string $prefix): array
    {
        $prefix = strtoupper($prefix);

        $reflection = new \ReflectionClass($this);
        $constants = [];

        foreach($reflection->getConstants() as $name => $value){
            if(substr($name, 0, strlen($prefix) + 1) == $prefix . '_'){
                $constants[substr($name, strlen($prefix) + 1)] = $value;
            }
        }

        return $constants;
    }

    public function getConstantsReverse(string $prefix): array
    {
        return array_flip($this->getConstants($prefix));
    }

    public function getConstantValueByName(string $prefix, $name)
    {
        $name = strtoupper($name);

        foreach($this->getConstants($prefix) as $key => $value){
            if($key === $name){
                return $value;
            }
        }

        return null;
    }

    public function getConstantNameByValue(string $prefix, $value)
    {
        foreach($this->getConstants($prefix) as $key => $v){
            if($value === $v){
                return $key;
            }
        }

        return null;
    }
}