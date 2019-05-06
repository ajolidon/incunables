<?php

namespace App\Entity;

use App\Helper\SlugTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 */
class Location
{
    use SlugTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $gnd;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Country", mappedBy="locations")
     * @ORM\OrderBy({"name"="ASC"})
     */
    private $countries;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Incunable", inversedBy="locations")
     */
    private $incunables;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\RelationSubject", inversedBy="locations")
     * @ORM\OrderBy({"type"="ASC", "name"="ASC"})
     */
    private $subjects;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
        $this->incunables = new ArrayCollection();
        $this->subjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Collection|Country[]
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    /**
     * @param Country[] $countries
     */
    public function updateCountries(array $countries)
    {
        foreach($this->getCountries() as $country){
            $this->removeCountry($country);
        }

        foreach($countries as $country){
            $this->addCountry($country);
        }
    }

    protected function addCountry(Country $country): self
    {
        if (!$this->countries->contains($country)) {
            $this->countries[] = $country;
            $country->addLocation($this);
        }

        return $this;
    }

    public function removeCountry(Country $country): self
    {
        if ($this->countries->contains($country)) {
            $this->countries->removeElement($country);
            $country->removeLocation($this);
        }

        return $this;
    }

    /**
     * @return Collection|Incunable[]
     */
    public function getIncunables(): Collection
    {
        return $this->incunables;
    }

    public function addIncunable(Incunable $incunable): self
    {
        if (!$this->incunables->contains($incunable)) {
            $this->incunables[] = $incunable;
        }

        return $this;
    }

    public function removeIncunable(Incunable $incunable): self
    {
        if ($this->incunables->contains($incunable)) {
            $this->incunables->removeElement($incunable);
        }

        return $this;
    }

    /**
     * @return Collection|RelationSubject[]
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(RelationSubject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects[] = $subject;
        }

        return $this;
    }

    public function removeSubject(RelationSubject $subject): self
    {
        if ($this->subjects->contains($subject)) {
            $this->subjects->removeElement($subject);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
