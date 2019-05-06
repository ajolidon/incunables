<?php

namespace App\Entity;

use App\Helper\EquatableInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearchEntryRepository")
 */
class SearchEntry implements EquatableInterface
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
    private $field;

    /**
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Incunable")
     * @ORM\JoinColumn(nullable=false)
     */
    private $incunable;

    /**
     * @ORM\Column(type="smallint")
     */
    private $priority;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getIncunable(): ?Incunable
    {
        return $this->incunable;
    }

    public function setIncunable(?Incunable $incunable): self
    {
        $this->incunable = $incunable;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getHash(): ?string
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
            $this->getIncunable()->getId(),
            $this->getPriority(),
            $this->getField(),
            $this->getValue(),
        ];

        $this->hash = md5(join('|', $arr));

        return $this->hash;
    }


}
