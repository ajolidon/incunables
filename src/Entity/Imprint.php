<?php

namespace App\Entity;

use App\Helper\EquatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImprintRepository")
 */
class Imprint implements EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $printer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Incunable", inversedBy="imprints")
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getPrinter(): ?string
    {
        return $this->printer;
    }

    public function setPrinter(?string $printer): self
    {
        $this->printer = $printer;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getHash(): string
    {
        return $this->updateHash();
    }

    public function equals(EquatableInterface $other): bool
    {
        // TODO: Implement equals() method.
    }

    public function updateHash(): string
    {
        $arr = [
            $this->location,
            $this->printer,
            $this->year,
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

    public function __toString(){
        $imprint = "";
        if(!empty($this->getLocation())){
            $imprint = $this->getLocation();
        }

        if(!empty($this->getPrinter(). $this->getYear())){
            $imprint .= " : ";

            if(!empty($this->getPrinter())){
                $imprint .= $this->getPrinter();

                if(!empty($this->getYear())){
                    $imprint .= ", ";
                }
            }

            if(!empty($this->getYear())){
                $imprint .= $this->getYear();
            }
        }

        return $imprint;
    }
}
