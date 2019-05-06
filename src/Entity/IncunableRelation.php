<?php

namespace App\Entity;

use App\Helper\EquatableInterface;
use App\Helper\HashableInterface;
use App\Helper\PreferredTitleTrait;
use App\Helper\TitleSortableInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="incunable_relation_unique",
 *            columns={"incunable_id", "subject_id", "work_id", "type_id"})
 *    }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\IncunableRelationRepository")
 */
class IncunableRelation implements EquatableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RelationSubject", inversedBy="relations", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Incunable", inversedBy="relations", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $incunable;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Work", inversedBy="relations", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $work;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RelationType", inversedBy="relations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMainEntry = false;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?RelationSubject
    {
        return $this->subject;
    }

    public function setSubject(?RelationSubject $subject): self
    {
        if(!empty($this->subject)){
            $this->getSubject()->removeRelation($this);
        }

        $this->subject = $subject;

        if(!empty($subject)) {
            $subject->addRelation($this);
        }

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

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function setWork(?Work $work): self
    {
        if(!empty($this->work))
        {
            $this->getWork()->removeRelation($this);
        }

        $this->work = $work;

        if(!empty($work)){
            $work->addRelation($this);
        }
        return $this;
    }

    public function getType(): ?RelationType
    {
        return $this->type;
    }

    public function setType(?RelationType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function equals(EquatableInterface $other): bool
    {
        if($other instanceof IncunableRelation) {
            return $this->getHash() == $other->getHash();
        }

        return false;
    }

    public function getIsMainEntry(): ?bool
    {
        return $this->isMainEntry;
    }

    public function setIsMainEntry(bool $isMainEntry): self
    {
        $this->isMainEntry = $isMainEntry;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->updateHash();
    }

    public function updateHash(): string
    {
        $arr = [
            empty($this->getIncunable()) ? null : $this->getIncunable()->getHash(),
            empty($this->getType()) ? null : $this->getType()->getAbbreviation(),
            empty($this->getSubject()) ? null : $this->getSubject()->getHash(),
            empty($this->getWork()) ? null : $this->getWork()->getHash(),
            $this->getIsMainEntry() ? '1' : '0',
        ];

        $this->hash = md5(join('|', $arr));

        return $this->hash;
    }
}
