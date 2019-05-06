<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RelationTypeRepository")
 */
class RelationType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3, unique=true)
     */
    private $abbreviation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\IncunableRelation", mappedBy="type")
     */
    private $relations;

    public function __construct()
    {
        $this->relations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): self
    {
        $this->abbreviation = $abbreviation;

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

    /**
     * @return Collection|IncunableRelation[]
     */
    public function getRelatedWorks(): Collection
    {
        return $this->relatedWorks;
    }

    public function addRelatedWork(IncunableRelation $relatedWork): self
    {
        if (!$this->relatedWorks->contains($relatedWork)) {
            $this->relatedWorks[] = $relatedWork;
            $relatedWork->setRelationType($this);
        }

        return $this;
    }

    public function removeRelatedWork(IncunableRelation $relatedWork): self
    {
        if ($this->relatedWorks->contains($relatedWork)) {
            $this->relatedWorks->removeElement($relatedWork);
            // set the owning side to null (unless already changed)
            if ($relatedWork->getRelationType() === $this) {
                $relatedWork->setRelationType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|IncunableRelation[]
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function addRelation(IncunableRelation $relation): self
    {
        if (!$this->relations->contains($relation)) {
            $this->relations[] = $relation;
            $relation->setType($this);
        }

        return $this;
    }

    public function removeRelation(IncunableRelation $relation): self
    {
        if ($this->relations->contains($relation)) {
            $this->relations->removeElement($relation);
            // set the owning side to null (unless already changed)
            if ($relation->getType() === $this) {
                $relation->setType(null);
            }
        }

        return $this;
    }
}
