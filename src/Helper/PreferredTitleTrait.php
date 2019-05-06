<?php

namespace App\Helper;


use App\Entity\Title;
use Doctrine\Common\Collections\Collection;

trait PreferredTitleTrait
{
    /**
     * @param int|null $type
     * @return Title[]|Collection
     */
    abstract public function getTitles(int $type = null): Collection;

    public function getPreferredTitle(): ?Title
    {
        foreach($this->getTitles(Title::TYPE_PREFERRED) as $title)
        {
            return $title;
        }

        foreach($this->getTitles(Title::TYPE_UNIFORM) as $title)
        {
            return $title;
        }

        foreach($this->getTitles(Title::TYPE_PRESENT) as $title)
        {
            return $title;
        }

        foreach($this->getTitles(Title::TYPE_VARIANT) as $title)
        {
            return $title;
        }

        if($this->getTitles()->count() > 0){
            return $this->getTitles()->first();
        }

        return new Title();
    }

    public function __toString()
    {
        if(empty($this->getPreferredTitle()))
        {
            return '';
        }

        return $this->getPreferredTitle()->__toString();
    }
}