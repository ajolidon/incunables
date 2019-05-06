<?php

namespace App\Entity;

use App\Helper\ConstantTrait;
use App\Helper\EquatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TitleRepository")
 */
class Title implements EquatableInterface
{
    use ConstantTrait;

    // Use this type, if the title value is in field 130
    const TYPE_UNIFORM = 0;

    // Use this type, if the title value is in field 240
    const TYPE_PREFERRED = 1;

    // Use this type, if the title value is in field 245
    const TYPE_PRESENT = 2;

    // Use this type, if the title value is in field 246 and second indicator is 0
    const TYPE_PARTIAL = 3;

    // Use this type, if the title value is in field 246 and second indicator is 3
    const TYPE_VARIANT = 4;

    // Use this type, if the title value is in field 246 and second indicator is empty
    const TYPE_OTHER = 5;

    // Use this type, if the title value is in field 730
    const TYPE_UNIFORM_FURTHER = 6;

    
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
     * @ORM\Column(type="text", name="title_value")
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $introductoryText;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Incunable", inversedBy="titles", cascade={"persist"})
     */
    private $incunables;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Work", inversedBy="titles", cascade={"persist"})
     */
    private $works;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    public function __construct()
    {
        $this->incunables = new ArrayCollection();
        $this->works = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type     Use `TYPE_UNIFORM` if source of title is field 130
     *                      Use `TYPE_PREFERED` if source of title is field 240
     *                      Use `TYPE_PRESENT` if source of title is field 245
     *                      Use `TYPE_PARTIAL` if source of title is field 246 and second indicator is `0`
     *                      Use `TYPE_VARIANT` if source of title is field 246 and second indicator is `3`
     *                      Use `TYPE_OTHER` if source of title is field 246 and second indicator is empty
     *                      Use `TYPE_UNIFORM_FURTHER` if source of title is field 730
     * @return Title
     */
    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue($includeIntroductoryText = false): ?string
    {
        if(!$includeIntroductoryText)
        {
            return $this->value;
        }

        $introductoryText = $this->getIntroductoryText();
        if(empty($introductoryText))
        {
            return $this->value;
        }

        return $introductoryText . ': ' . $this->value;
    }

    /**
     * @param string $value     Use `$a` if source of title is field 130 or 240
     *                          Use `$a : $b / $c ; $i. $j.` if source of title is field 245
     *                          Use `$a : $b. $n. $p.` if source of title is field 246
     * @return Title
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getIntroductoryText(): ?string
    {
        if($this->type == self::TYPE_UNIFORM)
        {
            return "Einheitstitel";
        }

        if($this->type == self::TYPE_PREFERRED)
        {
            return "Bevorzugter Titel";
        }

        if($this->type == self::TYPE_PRESENT)
        {
            return "Vorlagetitel";
        }

        if($this->type == self::TYPE_PARTIAL)
        {
            return "Teil des Titels";
        }

        if($this->type == self::TYPE_VARIANT)
        {
            return "Variante des Titels";
        }

        if($this->type == self::TYPE_UNIFORM_FURTHER)
        {
            return "Weiterer Einheitstitel";
        }

        if($this->type == self::TYPE_OTHER && !empty($this->introductoryText)) {
            return $this->introductoryText;
        }

        return 'Weiterer Titel';
    }

    /**
     * @param string|null $introductoryText     Use `$i` if source of title is field 246 and second indicator is empty.
     *                                          Don't set introductory text otherwise.
     * @return Title
     */
    public function setIntroductoryText(?string $introductoryText): self
    {
        $this->introductoryText = $introductoryText;

        return $this;
    }

    public function equals(EquatableInterface $other): bool
    {
        if($other instanceof Title) {
            return $this->getHash() == $other->getHash();
        }

        return false;
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
     * @return Collection|Work[]
     */
    public function getWorks(): Collection
    {
        return $this->works;
    }

    public function addWork(Work $work): self
    {
        if (!$this->works->contains($work)) {
            $this->works[] = $work;
        }

        return $this;
    }

    public function removeWork(Work $work): self
    {
        if ($this->works->contains($work)) {
            $this->works->removeElement($work);
        }

        return $this;
    }

    public function __toString()
    {
        if(empty($this->value)){
            return "";
        }
        return $this->getValue(false);
    }

    public function getHash(): ?string
    {
        return $this->updateHash();
    }

    public function updateHash(): string
    {
        $arr = [
            $this->type,
            $this->value,
            $this->introductoryText,
        ];

        $this->hash = md5(join('|', $arr));

        return $this->hash;
    }
}
