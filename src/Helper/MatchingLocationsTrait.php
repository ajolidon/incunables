<?php

namespace App\Helper;


use App\Entity\Location;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait MatchingLocationsTrait
{
    /**
     * @return Collection|Location[]
     */
    abstract public function getLocations(): Collection;

    /**
     * @param Collection $locations
     * @return Collection|Location[]
     */
    public function getMatchingLocations(Collection $locations): Collection
    {
        $result = [];

        foreach($this->getLocations() as $location)
        {
            if($locations->contains($location)){
                $result[] = $location;
            }
        }

        return new ArrayCollection($result);
    }
}