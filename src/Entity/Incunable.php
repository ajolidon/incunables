<?php

namespace App\Entity;

use App\Helper\EquatableInterface;
use App\Helper\MatchingLocationsTrait;
use App\Helper\PreferredTitleTrait;
use App\Helper\SlugTrait;
use App\Helper\TitleSortableInterface;
use App\Helper\UpdatableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IncunableRepository")
 */
class Incunable extends UpdatableEntity implements EquatableInterface, TitleSortableInterface
{
    use PreferredTitleTrait;
    use MatchingLocationsTrait;
    use SlugTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $systemNumber;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\IncunableRelation", mappedBy="incunable", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $relations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Title", mappedBy="incunables", cascade={"persist"})
     */
    private $titles;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Language", inversedBy="languageIncunables", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="incunable_languages")
     */
    private $languages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Language", inversedBy="translationIncunables", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="incunable_translations")
     */
    private $translations;

    /**
     * @var Location[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Location", mappedBy="incunables", cascade={"persist", "remove"})
     */
    protected $locations;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $yearOfPublicationFrom;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $yearOfPublicationTo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $signature;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $edition;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Imprint", mappedBy="incunables", cascade={"persist", "remove"})
     */
    private $imprints;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pages;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $illustrations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $size;

    /**
     * @ORM\Column(type="json_array")
     */
    private $physicalNotes;

    /**
     * @ORM\Column(type="json_array")
     */
    private $signatureFormulas;

    /**
     * @ORM\Column(type="json_array")
     */
    private $imprintNotes;

    /**
     * @ORM\Column(type="json_array")
     */
    private $notes;

    /**
     * @ORM\Column(type="json_array")
     */
    private $contains;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Reference", mappedBy="incunables", cascade={"persist", "remove"})
     */
    private $additionalReferences;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Digitalisation", mappedBy="incunables")
     */
    private $digitalisations;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $provenance;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $cover;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $bookBlock;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $furtherNotes;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastModified;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Scan", mappedBy="incunable", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $scans;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $swissbibSystemNumber;

    public function __construct()
    {
        $this->relations = new ArrayCollection();
        $this->titles = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->imprints = new ArrayCollection();
        $this->clearArrays();
        $this->additionalReferences = new ArrayCollection();
        $this->digitalisations = new ArrayCollection();
        $this->scans = new ArrayCollection();
    }

    public function clearArrays(){
        $this->physicalNotes = [];
        $this->signatureFormulas = [];
        $this->imprintNotes = [];
        $this->notes = [];
        $this->contains = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSystemNumber(): ?int
    {
        return $this->systemNumber;
    }

    public function setSystemNumber(int $systemNumber): self
    {
        $this->systemNumber = $systemNumber;

        return $this;
    }

    /**
     * @return Collection|IncunableRelation[]
     */
    public function getRelations($isMainEntry = null): Collection
    {
        if(is_null($isMainEntry)) {
            return $this->relations;
        }

        $relations = new ArrayCollection();
        foreach($this->getRelations() as $relation){
            if($relation->getIsMainEntry() == $isMainEntry){
                $relations->add($relation);
            }
        }

        return $relations;
    }

    public function addRelation(IncunableRelation $relation): self
    {
        $relation->setIncunable($this);

        $this->updateAdd($relation);

        if (!$this->hasRelation($relation)) {
            $this->relations[] = $relation;
            $relation->setIncunable($this);
        }

        return $this;
    }

    public function removeRelation(IncunableRelation $relation): self
    {
        $this->updateRemove($relation);

        $relation->setIncunable(null);

        if ($this->hasRelation($relation)) {
            foreach($this->getRelations() as $existingRelation){
                if($existingRelation->equals($relation)){
                    $this->relations->removeElement($existingRelation);
                    // set the owning side to null (unless already changed)
                    if ($existingRelation->getIncunable() === $this) {
                        $existingRelation->setIncunable(null);
                    }
                }
            }
        }

        return $this;
    }

    public function hasRelation(IncunableRelation $relation): bool
    {
        foreach($this->getRelations() as $existingRelation)
        {
            if($existingRelation->equals($relation))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @return IncunableRelation[]
     */
    public function getRelatedWorks()
    {
        $relations = [];
        foreach($this->getRelations() as $relation)
        {
            if(!is_null($relation->getWork())){
                $relations[] = $relation;
            }
        }

        return $relations;
    }

    /**
     * @param null $isMainEntry
     * @param int|null $type
     * @return Collection|IncunableRelation[]
     */
    public function getDirectlyRelatedSubjects($isMainEntry = null, int $type = null): Collection
    {
        $relations = [];
        foreach($this->getRelations($isMainEntry) as $relation)
        {
            if(is_null($relation->getWork()) && !is_null($relation->getSubject()) && (is_null($type) || $relation->getSubject()->getType() == $type)){
                $relations[] = $relation;
            }
        }

        return new ArrayCollection($relations);
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
            $title->addIncunable($this);
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
                    $existingTitle->removeIncunable($this);
                }
            }
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

    public function equals(EquatableInterface $other): bool
    {
        if($other instanceof Incunable)
        {
            return $this->getSystemNumber() == $other->getSystemNumber();
        }

        return false;
    }

    protected function getUpdateGetters(): array
    {
        return [
            Title::class => [
                'get' => 'getTitles',
                'remove' => 'removeTitle',
            ],
            IncunableRelation::class => [
                'get' => 'getRelations',
                'remove' => 'removeRelation',
            ],
        ];
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function updateHash(): string
    {
        return $this->hash;
    }

    /**
     * @return Collection|Language[]
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): self
    {
        if (!$this->languages->contains($language)) {
            $this->languages[] = $language;
        }

        return $this;
    }

    public function removeLanguage(Language $language): self
    {
        if ($this->languages->contains($language)) {
            $this->languages->removeElement($language);
        }

        return $this;
    }

    /**
     * @return Collection|Language[]
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(Language $translation): self
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
        }

        return $this;
    }

    public function removeTranslation(Language $translation): self
    {
        if ($this->translations->contains($translation)) {
            $this->translations->removeElement($translation);
        }

        return $this;
    }

    /**
     * @return Collection|Location[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    /**
     * @param Location[] $locations
     * @return Incunable
     */
    public function updateLocations(array $locations): self
    {
        foreach($this->getLocations() as $location){
            $this->removeLocation($location);
        }

        foreach($locations as $location){
            $this->addLocation($location);
        }

        return $this;
    }

    public function addLocation(Location $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations[] = $location;
            $location->addIncunable($this);
        }

        return $this;
    }

    protected function removeLocation(Location $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
            $location->removeIncunable($this);
        }

        return $this;
    }

    public function getYearOfPublicationFrom(): ?int
    {
        return $this->yearOfPublicationFrom;
    }

    public function setYearOfPublicationFrom(?int $yearOfPublicationFrom): self
    {
        $this->yearOfPublicationFrom = $yearOfPublicationFrom;

        return $this;
    }

    public function getYearOfPublicationTo(): ?int
    {
        return $this->yearOfPublicationTo;
    }

    public function setYearOfPublicationTo(?int $yearOfPublicationTo): self
    {
        $this->yearOfPublicationTo = $yearOfPublicationTo;

        return $this;
    }

    public function hasMultipleSignatures(): bool
    {
        return mb_strstr($this->signature, ' ; ') !== false;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    public function getEdition(): ?string
    {
        return $this->edition;
    }

    public function setEdition(?string $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * @return Collection|Imprint[]
     */
    public function getImprints(): Collection
    {
        return $this->imprints;
    }

    public function addImprint(Imprint $imprint): self
    {
        if (!$this->hasImprint($imprint)) {
            $this->imprints[] = $imprint;
            $imprint->addIncunable($this);
        }

        return $this;
    }

    protected function hasImprint(Imprint $imprint){
        foreach($this->getImprints() as $existing){
            if($imprint->getHash() == $existing->getHash()){
                return true;
            }
        }

        return false;
    }

    public function removeImprint(Imprint $imprint): self
    {
        if ($this->imprints->contains($imprint)) {
            $this->imprints->removeElement($imprint);
            $imprint->removeIncunable($this);
        }

        return $this;
    }

    public function getPages(): ?string
    {
        return $this->pages;
    }

    public function setPages(?string $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function getIllustrations(): ?string
    {
        return $this->illustrations;
    }

    public function setIllustrations(?string $illustrations): self
    {
        $this->illustrations = $illustrations;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getPhysicalNotes(): array
    {
        return $this->physicalNotes;
    }

    public function addPhysicalNote(?string $physicalNote): self
    {
        foreach($this->physicalNotes as $existing){
            if($existing == $physicalNote){
                return $this;
            }
        }

        $this->physicalNotes[] = $physicalNote;

        return $this;
    }

    public function getSignatureFormulas(): array
    {
        return $this->signatureFormulas;
    }

    public function addSignatureFormula($signatureFormula): self
    {
        foreach($this->signatureFormulas as $existing)
        {
            if($existing == $signatureFormula){
                return $this;
            }
        }

        $this->signatureFormulas[] = $signatureFormula;

        return $this;
    }

    public function getImprintNotes(): array
    {
        return $this->imprintNotes;
    }

    public function addImprintNote($imprintNote): self
    {
        foreach($this->imprintNotes as $existing){
            if($existing == $imprintNote){
                return $this;
            }
        }

        $this->imprintNotes[] = $imprintNote;

        return $this;
    }

    public function getNotes(): array
    {
        return $this->notes;
    }

    public function addNote($note): self
    {
        foreach($this->notes as $existing){
            if($existing == $note){
                return $this;
            }
        }

        $this->notes[] = $note;

        return $this;
    }

    public function getContains(): array
    {
        return $this->contains;
    }

    public function addContains($contains): self
    {
        foreach($this->contains as $existing){
            if($existing == $contains){
                return $this;
            }
        }

        $this->contains[] = $contains;

        return $this;
    }

    /**
     * @return Collection|Reference[]
     */
    public function getAdditionalReferences(): Collection
    {
        return $this->additionalReferences;
    }

    public function addAdditionalReference(Reference $additionalReference): self
    {
        foreach($this->getAdditionalReferences() as $existing){
            if($existing->equals($additionalReference)){
                return $this;
            }
        }

        $this->additionalReferences->add($additionalReference);
        $additionalReference->addIncunable($this);

        return $this;
    }

    public function removeAdditionalReference(Reference $additionalReference): self
    {
        foreach($this->getAdditionalReferences() as $existing){
            if($existing->equals($additionalReference)){
                $this->additionalReferences->removeElement($existing);
                $existing->removeIncunable($this);
                return $this;
            }
        }

        return $this;
    }

    /**
     * @return Collection|Digitalisation[]
     */
    public function getDigitalisations(): Collection
    {
        return $this->digitalisations;
    }

    public function addDigitalisation(Digitalisation $digitalisation): self
    {
        foreach($this->getDigitalisations() as $existing){
            if($existing->equals($digitalisation)){
                return $this;
            }
        }

        $this->digitalisations->add($digitalisation);
        $digitalisation->addIncunable($this);

        return $this;
    }

    public function removeDigitalisation(Digitalisation $digitalisation): self
    {
        foreach($this->getDigitalisations() as $existing){
            if($existing->equals($digitalisation)){
                $this->digitalisations->removeElement($existing);
                $existing->removeIncunable($this);
                return $this;
            }
        }

        return $this;
    }

    public function getProvenance(): ?string
    {
        return $this->provenance;
    }

    public function setProvenance(?string $provenance): self
    {
        $this->provenance = $provenance;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(?string $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    public function getBookBlock(): ?string
    {
        return $this->bookBlock;
    }

    public function setBookBlock(?string $bookBlock): self
    {
        $this->bookBlock = $bookBlock;

        return $this;
    }

    public function getFurtherNotes(): ?string
    {
        return $this->furtherNotes;
    }

    public function setFurtherNotes(?string $furtherNotes): self
    {
        $this->furtherNotes = $furtherNotes;

        return $this;
    }

    public function getLastModified(): ?\DateTimeInterface
    {
        return $this->lastModified;
    }

    public function setLastModified(\DateTimeInterface $lastModified): self
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * @return Collection|Scan[]
     */
    public function getScans(): Collection
    {
        return $this->scans;
    }

    public function addScan(Scan $scan): self
    {
        if (!$this->hasScan($scan)) {
            $this->scans[] = $scan;
            $scan->setIncunable($this);
            /*
            if($scan->getModified() > $this->getLastModified()){
                $this->setLastModified($scan->getModified());
            }
            */
        }

        return $this;
    }

    public function hasScan(Scan $scan): bool
    {
        foreach($this->getScans() as $existing)
        {
            if($existing->getPath() == $scan->getPath())
            {
                return true;
            }
        }

        return false;
    }

    public function removeScan(Scan $scan): self
    {
        foreach($this->getScans() as $existing){
            if($existing->getPath() == $scan->getPath()){
                $this->scans->removeElement($existing);
                break;
            }
        }

        return $this;
    }

    public function getSwissbibSystemNumber(): ?string
    {
        return $this->swissbibSystemNumber;
    }

    public function setSwissbibSystemNumber(string $swissbibSystemNumber): self
    {
        $this->swissbibSystemNumber = $swissbibSystemNumber;

        return $this;
    }
}
