<?php

namespace App\Entity;

use App\Helper\ConstantTrait;
use App\Helper\EquatableInterface;
use App\Helper\MatchingLocationsTrait;
use App\Helper\SlugTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RelationSubjectRepository")
 */
class RelationSubject implements EquatableInterface
{
    use ConstantTrait;
    use MatchingLocationsTrait;
    use SlugTrait;

    const TYPE_PERSON = 0;
    const TYPE_CORPORATION = 1;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $addition;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $counting;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $biographical;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $gnd;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\IncunableRelation", mappedBy="subject", cascade={"persist"})
     */
    private $relations;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $placeOfBirth;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $placeOfDeath;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $placeOfBusiness;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secondPlaceOfBusiness;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Location", mappedBy="subjects", cascade={"persist", "remove"})
     */
    private $locations;

    public function __construct()
    {
        $this->relations = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddition(): ?string
    {
        return $this->addition;
    }

    public function setAddition(?string $addition): self
    {
        $this->addition = $addition;

        return $this;
    }

    public function getCounting(): ?string
    {
        return $this->counting;
    }

    public function setCounting(?string $counting): self
    {
        $this->counting = $counting;

        return $this;
    }

    public function getBiographical(): ?string
    {
        return $this->biographical;
    }

    public function setBiographical(?string $biographical): self
    {
        $this->biographical = $biographical;

        return $this;
    }

    public function getGnd(): ?string
    {
        return $this->gnd;
    }

    public function setGnd(?string $gnd): self
    {
        $this->gnd = $gnd;

        return $this;
    }

    /**
     * @return IncunableRelation[]|Collection
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function addRelation(IncunableRelation $relation): self
    {
        if(!$this->relations->contains($relation))
        {
            $this->relations->add($relation);
        }

        return $this;
    }

    public function removeRelation(IncunableRelation $relation): self
    {
        if($this->relations->contains($relation))
        {
            $this->relations->removeElement($relation);
        }

        return $this;
    }

    public function equals(EquatableInterface $other): bool
    {
        if($other instanceof RelationSubject)
        {
            if(!empty($this->getGnd()) && !empty($other->getGnd())){
                return $this->getGnd() == $other->getGnd();
            }

            return $this->getHash() == $other->getHash();
        }

        return false;
    }

    public function hasAnyLocation(): bool
    {
        return !$this->locations->isEmpty();
    }

    public function getLocationByName(string $name): ?Location
    {
        foreach($this->getLocations() as $location)
        {
            if($location->getName() == $name){
                return $location;
            }
        }

        return null;
    }

    public function getLocationTypes(Location $location): array
    {
        $types = [];
        if($location->getName() == $this->getPlaceOfBirth())
        {
            $types[] = 'PLACE_OF_BIRTH';
        }

        if($location->getName() == $this->getPlaceOfDeath())
        {
            $types[] = 'PLACE_OF_DEATH';
        }

        if($location->getName() == $this->getPlaceOfBusiness() || $location->getName() == $this->getSecondPlaceOfBusiness())
        {
            $types[] = 'PLACE_OF_BUSINESS';
        }

        return $types;
    }

    public function getPlaceOfBirth(): ?string
    {
        return $this->placeOfBirth;
    }

    public function setPlaceOfBirth(?Location $placeOfBirth): self
    {
        $this->handleLocationChange($this->placeOfBirth, $placeOfBirth);
        $this->placeOfBirth = $placeOfBirth->getName();

        return $this;
    }

    public function getPlaceOfDeath(): ?string
    {
        return $this->placeOfDeath;
    }

    public function setPlaceOfDeath(?Location $placeOfDeath): self
    {
        $this->handleLocationChange($this->placeOfDeath, $placeOfDeath);
        $this->placeOfDeath = $placeOfDeath->getName();

        return $this;
    }

    public function getPlaceOfBusiness(): ?string
    {
        return $this->placeOfBusiness;
    }

    public function setPlaceOfBusiness(?Location $placeOfBusiness): self
    {
        $this->handleLocationChange($this->placeOfBusiness, $placeOfBusiness);
        $this->placeOfBusiness = $placeOfBusiness->getName();

        return $this;
    }

    public function getSecondPlaceOfBusiness(): ?string
    {
        return $this->secondPlaceOfBusiness;
    }

    public function setSecondPlaceOfBusiness(?Location $secondPlaceOfBusiness): self
    {
        $this->handleLocationChange($this->secondPlaceOfBusiness, $secondPlaceOfBusiness);
        $this->secondPlaceOfBusiness = $secondPlaceOfBusiness->getName();

        return $this;
    }

    protected function handleLocationChange(?Location $old, ?Location $new)
    {
        if(!empty($old)){
            $this->removeLocation($old);
        }

        if(!empty($new)){
            $this->addLocation($new);
        }
    }

    /**
     * @return Collection|Location[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function mergeLocations(RelationSubject $subject){
        if(!empty($subject->getPlaceOfBirth())){
            $this->placeOfBirth = $subject->getPlaceOfBirth();
        }

        if(!empty($subject->getPlaceOfDeath())){
            $this->placeOfDeath = $subject->getPlaceOfDeath();
        }

        if(!empty($subject->getPlaceOfBusiness())){
            $this->placeOfBusiness = $subject->getPlaceOfBusiness();
        }

        if(!empty($subject->getSecondPlaceOfBusiness())){
            $this->secondPlaceOfBusiness = $subject->getSecondPlaceOfBusiness();
        }

        $names = [
            $this->getPlaceOfBirth(),
            $this->getPlaceOfDeath(),
            $this->getPlaceOfBusiness(),
            $this->getSecondPlaceOfBusiness(),
        ];

        foreach($this->getLocations() as $location){
            if(!in_array($location->getName(), $names))
            {
                $this->removeLocation($location);
            }
        }

        foreach($subject->getLocations() as $location)
        {
            if(in_array($location->getName(), $names))
            {
                $this->addLocation($location);
            }
        }
    }

    protected function addLocation(Location $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations[] = $location;
            //$location->addSubject($this);
        }

        return $this;
    }

    protected function removeLocation(Location $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
            $location->removeSubject($this);
        }

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->updateHash();
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function updateHash(): string
    {
        $arr = [
            $this->type,
            $this->name,
            $this->addition,
            $this->counting,
            $this->biographical,
        ];

        $this->hash = md5(join('|', $arr));

        return $this->hash;
    }

    public function __toString()
    {
        $arr = [];
        if(!empty($this->getName())){
            $arr[] = $this->getName();
        }

        if(!empty($this->getCounting())){
            $arr[] = $this->getCounting();
        }

        if(!empty($this->getAddition())){
            $arr[] = $this->getAddition();
        }

        if(!empty($this->getBiographical())){
            $arr[] = $this->getBiographical();
        }

        return join(', ', $arr);
    }
}
