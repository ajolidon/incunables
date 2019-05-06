<?php

namespace App\Helper;


use App\Entity\Incunable;
use App\Entity\IncunableRelation;

abstract class UpdatableEntity
{
    private $updateItems = [];

    protected function updateAdd(EquatableInterface $object)
    {
        $class = get_class($object);
        $this->ensureExists($class);

        if(!$this->updateHas($object)){
            $this->updateItems[$class][] = $object;
        }
    }

    protected function updateRemove(EquatableInterface $object)
    {
        $class = get_class($object);
        $this->ensureExists($class);

        if($this->updateHas($object)){
            foreach($this->updateItems[$class] as $key => $existing){
                /* @var \App\Helper\EquatableInterface $existing */
                if($existing->equals($object)){
                    unset($this->updateItems[$class][$key]);
                    $this->updateItems[$class] = array_values($this->updateItems[$class]);
                    return;
                }
            }
        }
    }

    protected function updateHas(EquatableInterface $object)
    {
        $class = get_class($object);
        $this->ensureExists($class);

        foreach($this->updateItems[$class] as $existing){
            /* @var \App\Helper\EquatableInterface $existing */
            if($existing->equals($object)){
                return true;
            }
        }

        return false;
    }

    public function cleanUp(){
        foreach($this->getUpdateGetters() as $class => $methods) {
            $this->ensureExists($class);

            $getMethod = $methods['get'];
            $removeMethod = $methods['remove'];

            foreach ($this->$getMethod() as $existing) {
                $found = false;
                foreach ($this->updateItems[$class] as $other) {
                    if ($other->equals($existing)) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $this->$removeMethod($existing);
                }
            }
        }
    }

    public function getAddedItems($class)
    {
        $this->ensureExists($class);
        return $this->updateItems[$class];
    }

    /**
     * @return array
     */
    abstract protected function getUpdateGetters(): array;

    private function ensureExists(string $class){
        if(!isset($this->updateItems[$class])){
            $this->updateItems[$class] = [];
        }
    }

}