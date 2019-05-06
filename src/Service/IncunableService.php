<?php

namespace App\Service;


use App\Entity\Country;
use App\Entity\Digitalisation;
use App\Entity\Incunable;
use App\Entity\IncunableRelation;
use App\Entity\RelationSubject;
use App\Entity\RelationType;
use App\Entity\Title;
use App\Entity\Work;
use App\Helper\IncunableRelationSort;
use App\Helper\TitleSortableInterface;
use App\Helper\WorkRelationSort;
use App\Repository\IncunableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class IncunableService
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var IncunableRepository
     */
    private $incunableRepo;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(EntityManagerInterface $manager, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator)
    {
        $this->manager = $manager;
        $this->incunableRepo = $manager->getRepository(Incunable::class);
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
    }

    public function getFurtherParticipants(Incunable $incunable): array
    {
        $participants = [];

        foreach($incunable->getDirectlyRelatedSubjects() as $relation)
        {
            $participants[] = '<a href="' . $this->urlGenerator->generate('subject_show', ['slug' => $relation->getSubject()->getSlug('__toString'), 'id' => $relation->getSubject()->getId()]) . '">' . $relation->getSubject() . '</a> (' . $this->translator->trans($relation->getType()->getName()) . ')';
        }

        return $participants;
    }

    public function getDigitalisations(Incunable $incunable): array
    {
        $result = [];
        foreach($incunable->getDigitalisations() as $digitalisation)
        {
            $result[] = '<a target="_blank" href="' . $digitalisation->getUrl() . '">' . $digitalisation->getTitle() . '</a>';
        }

        return $result;
    }

    public function getAdditionalReferences(Incunable $incunable): array
    {
        $result = [];
        foreach($incunable->getAdditionalReferences() as $reference)
        {
            $text = '<span class="text-muted">' . $reference->getSource() . ':</span> ';
            if(!empty($reference->getUrl())){
                $text .='<a target="_blank" href="' . $reference->getUrl() . '">' . $reference->getIdentifier() . '</a>';
            }else{
                $text .= $reference->getIdentifier();
            }

            $result[] = $text;
        }

        return $result;
    }

    public function getPhysicalNotes(Incunable $incunable): array
    {
        $physicalNotes = [];

        $physicalNote = "";
        if(!empty($incunable->getPages())){
            $physicalNote .= $incunable->getPages();
        }

        if(!empty($incunable->getIllustrations() . $incunable->getSize())){
            $physicalNote .= " : ";

            if(!empty($incunable->getIllustrations())) {
                $physicalNote .= $incunable->getIllustrations();

                if (!empty($incunable->getSize())) {
                    $physicalNote .= " ; ";
                }
            }

            if(!empty($incunable->getSize())){
                $physicalNote .= $incunable->getSize();
            }
        }

        if(!empty($physicalNote)){
            $physicalNotes[] = $physicalNote;
        }

        $physicalNotes = array_merge($physicalNotes, $incunable->getPhysicalNotes());

        return $physicalNotes;
    }

    public function getNewestIncunables(int $amount = 5)
    {
        return $this->incunableRepo->findNewest($amount);
    }

    public function getAuthor(Incunable $incunable): ?RelationSubject
    {
        foreach($incunable->getRelations() as $relation)
        {
            if(empty($relation->getWork()) && $relation->getType()->getAbbreviation() == 'aut')
            {
                return $relation->getSubject();
            }
        }

        return null;
    }

    public function getWorkAuthor(Work $work): ?RelationSubject
    {
        foreach($work->getRelations() as $relation)
        {
            if(!empty($relation->getType()) && $relation->getType()->getAbbreviation() == 'aut')
            {
                return $relation->getSubject();
            }
        }

        return null;
    }

    public function getNormalizedDigitalisationTitle(Digitalisation $digitalisation)
    {
        $re = '/^Digitalisat \((.+?)\)$/m';

        return preg_replace($re, "$1", $digitalisation->getTitle());
    }

    public function getPresentTitle(Incunable $incunable): ?Title
    {
        $titles = $incunable->getTitles(Title::TYPE_PRESENT);
        if($titles->isEmpty()){
            return null;
        }

        return $titles->first();
    }

    /**
     * @param Incunable $incunable
     * @return Collection|IncunableRelation[]
     */
    public function getRelatedWorks(Incunable $incunable): array
    {
        /* @var Collection|WorkRelationSort[] $sortedRelations */
        $sortedRelations = [];
        foreach($incunable->getRelatedWorks() as $relation){
            $sortedRelations[$relation->getWork()->getId()] = new WorkRelationSort($relation);
        }

        $sortedRelations = $this->sortTitles(new ArrayCollection(array_values($sortedRelations)));

        $relations = new ArrayCollection();
        foreach($sortedRelations as $sortedRelation){
            $relations->add($sortedRelation->getRelation());
        }

        $result = [];
        foreach($relations as $relation)
        {
            /* @var IncunableRelation $relation */
            $text = '';
            if(!empty($this->getWorkAuthor($relation->getWork())))
            {
                $text .= '<small class="text-muted">' . $this->getWorkAuthor($relation->getWork()) . '</small><br>';
            }

            $text .= '<a href="' . $this->urlGenerator->generate('work_show', ['slug' => $relation->getWork()->getSlug('preferredTitle'), 'id' => $relation->getWork()->getId()]) . '">' . $relation->getWork()->getPreferredTitle() . '</a>';

            $result[] = $text;
            
        }

        return $result;
    }

    /**
     * @param RelationSubject $subject
     * @return Collection|IncunableRelation[]
     */
    public function getSubjectRelationIncunables(RelationSubject $subject): Collection
    {
        /* @var Collection|IncunableRelationSort[] $sortedRelations */
        $sortedRelations = new ArrayCollection();
        foreach($subject->getRelations() as $relation){
            if(empty(($relation->getWork()))){
                $sortedRelations->add(new IncunableRelationSort($relation));
            }
        }

        $sortedRelations = $this->sortTitles($sortedRelations);

        $relations = new ArrayCollection();
        foreach($sortedRelations as $sortedRelation){
            $relations->add($sortedRelation->getRelation());
        }

        return $relations;
    }

    /**
     * @param RelationSubject $subject
     * @return Collection|IncunableRelation[]
     */
    public function getSubjectRelationWorks(RelationSubject $subject): Collection
    {
        /* @var Collection|WorkRelationSort[] $sortedRelations */
        $sortedRelations = [];
        foreach($subject->getRelations() as $relation){
            if(!empty(($relation->getWork()))){
                $sortedRelations[$relation->getWork()->getId()] = new WorkRelationSort($relation);
            }
        }

        $sortedRelations = $this->sortTitles(new ArrayCollection(array_values($sortedRelations)));

        $relations = new ArrayCollection();
        foreach($sortedRelations as $sortedRelation){
            $relations->add($sortedRelation->getRelation());
        }

        return $relations;
    }

    /**
     * @param Incunable $incunable
     * @return Title[]
     */
    public function getFurtherTitles(Incunable $incunable): array
    {
        $preferredTitle = $incunable->getPreferredTitle();

        /* @var \App\Entity\Title[] $titles */
        $titles = $incunable->getTitles()->toArray();
        foreach($titles as $key => $title)
        {
            if($title->equals($preferredTitle) || $title->getType() == Title::TYPE_PRESENT || $title->getType() == Title::TYPE_PREFERRED)
            {
                unset($titles[$key]);
            }
        }

        foreach($titles as $key => $title){
            $titles[$key] = $title->getValue(true);
        }

        return array_values($titles);
    }

    /**
     * @param Work $work
     * @return Title[]
     */
    public function getWorkFurtherTitles(Work $work): array
    {
        $preferredTitle = $work->getPreferredTitle();

        /* @var \App\Entity\Title[] $titles */
        $titles = $work->getTitles()->toArray();
        foreach($titles as $key => $title)
        {
            if($title->equals($preferredTitle))
            {
                unset($titles[$key]);
            }
        }

        return array_values($titles);
    }

    public function getPublication(Incunable $incunable): ?string
    {
        if(empty($incunable->getYearOfPublicationFrom()))
        {
            return null;
        }

        if(empty($incunable->getYearOfPublicationTo())){
            return $incunable->getYearOfPublicationFrom();
        }

        return $incunable->getYearOfPublicationFrom() . ' - ' . $incunable->getYearOfPublicationTo();
    }

    /**
     * @param Collection|TitleSortableInterface[] $titleSortable
     * @return Collection|TitleSortableInterface[]
     */
    public function sortTitles(Collection $titleSortable): Collection
    {
        $result = $titleSortable->toArray();

        usort($result, function(TitleSortableInterface $a, TitleSortableInterface $b){
            $titleA = mb_strtolower($this->removePartToBeIgnored($a->getPreferredTitle()));
            $titleB = mb_strtolower($this->removePartToBeIgnored($b->getPreferredTitle()));
            if($titleA == $titleB){
                return 0;
            }

            return $titleA < $titleB ? -1 : 1;
        });

        return new ArrayCollection($result);
    }

    protected function removePartToBeIgnored(?string $value)
    {
        if(empty($value)){
            return $value;
        }

        $value = preg_replace("/\[/m", "", $value);
        $value = preg_replace("/\]/m", "", $value);

        $re = '/^(<<.+?>>)(.+)$/m';

        $value = preg_replace($re, "$2", $value);

        return trim(preg_replace($re, "$2", $value));
    }

    /**
     * @param Country $country
     * @return Collection|Incunable[]
     */
    public function getIncunablesByCountry(Country $country): Collection
    {
        $incunables = [];
        foreach($country->getLocations() as $location)
        {
            foreach($location->getIncunables() as $incunable)
            {
                $incunables[$incunable->getId()] = $incunable;
            }
        }

        return $this->sortTitles(new ArrayCollection(array_values($incunables)));
    }

    public function getIncunablesByWork(Work $work)
    {
        $incunables = [];
        foreach($work->getRelations() as $relation)
        {
            $incunables[$relation->getIncunable()->getId()] = $relation->getIncunable();
        }

        return $this->sortTitles(new ArrayCollection(array_values($incunables)));
    }

    public function getSubjectsByCountry(Country $country)
    {
        $subjects = [];
        foreach($country->getLocations() as $location)
        {
            foreach($location->getSubjects() as $subject){
                $subjects[$subject->getId()] = $subject;
            }
        }

        $subjects = array_values($subjects);
        usort($subjects, function(RelationSubject $a, RelationSubject $b){
            if($a->equals($b)){
                return 0;
            }

            if($a->getType() != $b->getType()){
                return $a->getType() < $b->getType() ? -1 : 1;
            }

            return $a->__toString() < $b->__toString() ? -1 : 1;
        });

        return new ArrayCollection($subjects);
    }

    /**
     * @param Collection|IncunableRelation[] $relations
     * @return Collection|IncunableRelation[]
     */
    public function sortRelationsBySubject(Collection $relations)
    {
        /* @var Collection|IncunableRelation[] $relations */
        $relations = $relations->toArray();

        $singleRelations = [];

        foreach($relations as $key => $relation)
        {
            if(!empty($relation->getSubject())){
                $singleRelations[$relation->getSubject()->getId() . '_' . $relation->getType()->getId()] = $relation;
            }
        }

        $relations = array_values($singleRelations);

        usort($relations, function(IncunableRelation $a, IncunableRelation $b){
            if($a->getSubject()->equals($b->getSubject())){
                return 0;
            }

            if($a->getSubject()->getType() != $b->getSubject()->getType()){
                return $a->getSubject()->getType() < $b->getSubject()->getType() ? -1 : 1;
            }

            $nameA = $this->removePartToBeIgnored($a->getSubject()->__toString());
            $nameB = $this->removePartToBeIgnored($b->getSubject()->__toString());

            if($nameA == $nameB){
                return 0;
            }

            return $nameA < $nameB ? -1 : 1;
        });

        return new ArrayCollection($relations);
    }
}