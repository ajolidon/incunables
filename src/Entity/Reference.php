<?php

namespace App\Entity;

use App\Helper\EquatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReferenceRepository")
 */
class Reference implements EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $source;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $identifier;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Incunable", inversedBy="additionalReferences")
     */
    private $incunables;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    public function __construct()
    {
        $this->incunables = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

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

    public function getHash(): ?string
    {
        return $this->updateHash();
    }

    public function equals(EquatableInterface $other): bool
    {
        if($other instanceof Reference)
        {
            return $this->getHash() == $other->getHash();
        }

        return false;
    }

    public function updateHash(): string
    {
        $arr = [
            $this->source,
            $this->identifier,
            $this->url,
        ];

        $this->hash = md5(join('|', $arr));

        return $this->hash;
    }


}
