<?php


namespace App\EventListener;

use App\Entity\Work;
use App\Helper\HashableInterface;
use App\Helper\UpdatableEntity;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class EntityChange
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->updateEntity($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->updateEntity($args);
    }

    protected function updateEntity(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if($entity instanceof UpdatableEntity) {
            $entity->cleanUp();
        }

        if($entity instanceof HashableInterface)
        {
            $entity->updateHash();
        }
    }
}