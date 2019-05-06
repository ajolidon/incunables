<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LanguageRepository")
 */
class Language
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Incunable", mappedBy="languages")
     */
    private $languageIncunables;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Incunable", mappedBy="translations")
     */
    private $translationIncunables;

    public function __construct()
    {
        $this->languageIncunables = new ArrayCollection();
        $this->translationIncunables = new ArrayCollection();
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
     * @return Collection|Incunable[]
     */
    public function getLanguageIncunables(): Collection
    {
        return $this->languageIncunables;
    }

    public function addLanguageIncunable(Incunable $languageIncunable): self
    {
        if (!$this->languageIncunables->contains($languageIncunable)) {
            $this->languageIncunables[] = $languageIncunable;
            $languageIncunable->addLanguage($this);
        }

        return $this;
    }

    public function removeLanguageIncunable(Incunable $languageIncunable): self
    {
        if ($this->languageIncunables->contains($languageIncunable)) {
            $this->languageIncunables->removeElement($languageIncunable);
            $languageIncunable->removeLanguage($this);
        }

        return $this;
    }

    /**
     * @return Collection|Incunable[]
     */
    public function getTranslationIncunables(): Collection
    {
        return $this->translationIncunables;
    }

    public function addTranslationIncunable(Incunable $translationIncunable): self
    {
        if (!$this->translationIncunables->contains($translationIncunable)) {
            $this->translationIncunables[] = $translationIncunable;
            $translationIncunable->addTranslation($this);
        }

        return $this;
    }

    public function removeTranslationIncunable(Incunable $translationIncunable): self
    {
        if ($this->translationIncunables->contains($translationIncunable)) {
            $this->translationIncunables->removeElement($translationIncunable);
            $translationIncunable->removeTranslation($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
