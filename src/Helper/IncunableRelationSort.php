<?php

namespace App\Helper;


use App\Entity\IncunableRelation;
use App\Entity\Title;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class IncunableRelationSort implements TitleSortableInterface
{
    use PreferredTitleTrait;

    /**
     * @var IncunableRelation
     */
    private $relation;

    public function __construct(IncunableRelation $relation)
    {
        $this->relation = $relation;
    }

    /**
     * @param int|null $type
     * @return Collection|Title[]
     */
    public function getTitles(int $type = null): Collection
    {
        if(empty($this->relation->getIncunable())){
            return new ArrayCollection();
        }

        return $this->relation->getIncunable()->getTitles();
    }

    /**
     * @return IncunableRelation
     */
    public function getRelation()
    {
        return $this->relation;
    }
}