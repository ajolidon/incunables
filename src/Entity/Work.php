<?php

namespace App\Entity;

use App\Helper\EquatableInterface;
use App\Helper\PreferredTitleTrait;
use App\Helper\SlugTrait;
use App\Helper\TitleSortableInterface;
use App\Helper\UpdatableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkRepository")
 */
class Work extends UpdatableEntity implements EquatableInterface, TitleSortableInterface
{
    use PreferredTitleTrait;
    use SlugTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $gnd;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Title", mappedBy="works", cascade={"persist"})
     */
    private $titles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\IncunableRelation", mappedBy="work", cascade={"persist"})
     */
    private $relations;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    public function __construct()
    {
        $this->titles = new ArrayCollection();
        $this->relations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Collection|Title[]
     */
    public function getTitles(int $type = null): Collection
    {
        if(is_null($type)) {
            return $this->titles;
        }

        $titles = [];
        foreach($this->getTitles() as $title)
        {
            if($title->getType() == $type){
                $titles[] = $title;
            }
        }

        return new ArrayCollection($titles);
    }

    public function addTitle(Title $title): self
    {
        $this->updateAdd($title);

        if (!$this->hasTitle($title)) {
            $this->titles[] = $title;
            $title->addWork($this);
        }

        return $this;
    }

    public function removeTitle(Title $title): self
    {
        $this->updateRemove($title);

        if ($this->hasTitle($title)) {
            foreach($this->getTitles() as $existingTitle){
                if($existingTitle->equals($title)){
                    $this->titles->removeElement($existingTitle);
                    $existingTitle->removeWork($this);
                }
            }
        }

        return $this;
    }

    /**
     * @param Title[] $titles
     * @return Work
     */
    public function mergeTitles(array $titles): self
    {
        foreach($this->getTitles() as $title){
            $this->updateAdd($title);
        }

        foreach($titles as $title)
        {
            $this->addTitle($title);
        }

        return $this;
    }

    public function hasTitle(Title $title){
        foreach($this->getTitles() as $otherTitle){
            if($otherTitle->equals($title)){
                return true;
            }
        }

        return false;
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

    protected function getUpdateGetters(): array
    {
        return [
            Title::class => [
                'get' => 'getTitles',
                'remove' => 'removeTitle',

            ],
        ];
    }

    public function equals(EquatableInterface $other): bool
    {
        if($other instanceof Work)
        {
            if(!empty($this->getGnd()) && !empty($other->getGnd())){
                return $this->getGnd() == $other->getGnd();
            }

            return $this->getHash() == $other->getHash();
        }

        return false;
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
        $arr = [];
        foreach($this->getTitles() as $title)
        {
            $arr[] = $title->getHash();
        }

        $this->hash = md5(join('|', $arr));

        return $this->hash;
    }
}
