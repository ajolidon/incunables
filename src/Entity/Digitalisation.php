<?php

namespace App\Entity;

use App\Helper\EquatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DigitalisationRepository")
 */
class Digitalisation implements EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Incunable", inversedBy="digitalisations")
     */
    private $incunables;

    public function __construct()
    {
        $this->incunables = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->updateHash();
    }

    public function equals(EquatableInterface $other): bool
    {
        if($other instanceof Digitalisation)
        {
            return $this->getHash() == $other->getHash();
        }

        return false;
    }

    public function updateHash(): string
    {
        $arr = [
            $this->getTitle(),
            $this->getUrl(),
        ];

        $this->hash = md5(join('|', $arr));

        return $this->hash;
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
}
