<?php

namespace App\Service;


use App\Entity\Country;
use App\Entity\Digitalisation;
use App\Entity\Imprint;
use App\Entity\Incunable;
use App\Entity\IncunableRelation;
use App\Entity\Location;
use App\Entity\Reference;
use App\Entity\RelationSubject;
use App\Entity\RelationType;
use App\Entity\Title;
use App\Entity\Work;
use App\Helper\HashableInterface;
use App\Helper\UpdatableEntity;
use App\Repository\DigitalisationRepository;
use App\Repository\ImprintRepository;
use App\Repository\IncunableRelationRepository;
use App\Repository\IncunableRepository;
use App\Repository\LocationRepository;
use App\Repository\ReferenceRepository;
use App\Repository\RelationSubjectRepository;
use App\Repository\RelationTypeRepository;
use App\Repository\TitleRepository;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;

class IncunableImportService
{
    protected $manager;

    /**
     * @var IncunableRepository
     */
    protected $incunableRepo;

    /**
     * @var IncunableRelationRepository
     */
    protected $relationRepo;

    /**
     * @var RelationTypeRepository
     */
    protected $relationTypeRepo;

    /**
     * @var WorkRepository
     */
    protected $workRepo;

    /**
     * @var RelationSubjectRepository
     */
    protected $subjectRepo;

    /**
     * @var TitleRepository
     */
    protected $titleRepo;

    /**
     * @var ImprintRepository
     */
    protected $imprintRepo;

    /**
     * @var LocationRepository
     */
    protected $locationRepo;

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepo;

    /**
     * @var DigitalisationRepository
     */
    protected $digitalisationRepo;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->incunableRepo = $manager->getRepository(Incunable::class);
        $this->relationRepo = $manager->getRepository(IncunableRelation::class);
        $this->workRepo = $manager->getRepository(Work::class);
        $this->subjectRepo = $manager->getRepository(RelationSubject::class);
        $this->relationTypeRepo = $manager->getRepository(RelationType::class);
        $this->titleRepo = $manager->getRepository(Title::class);
        $this->imprintRepo = $manager->getRepository(Imprint::class);
        $this->locationRepo = $manager->getRepository(Location::class);
        $this->referenceRepo = $manager->getRepository(Reference::class);
        $this->digitalisationRepo = $manager->getRepository(Digitalisation::class);
    }

    /**
     * @return Incunable[]
     */
    public function findAllIncunables(){
        return $this->incunableRepo->findBy([], ['id' => 'ASC']);
    }

    public function findAllWorks(){
        return $this->workRepo->findBy([], ['id' => 'ASC']);
    }

    public function findAllSubjects(){
        return $this->subjectRepo->findBy([], ['id' => 'ASC']);
    }

    public function findAllTitles(){
        //return $this->titleRepo->findBy([], ['id' => 'ASC']);
        return $this->titleRepo->findBy([], ['type' => 'ASC', 'value' => 'ASC', 'introductoryText' => 'ASC']);
    }

    public function findAllImprints(){
        return $this->imprintRepo->findBy([], ['id' => 'ASC']);
    }

    public function findAllLocations(){
        return $this->locationRepo->findBy([], ['id' => 'ASC']);
    }


    public function findOrCreateIncunableBySystemNumber(int $systemNumber)
    {
        $incunable = $this->incunableRepo->findOneBy(['systemNumber' => $systemNumber]);

        if(empty($incunable)){
            $incunable = new Incunable();
            $incunable->setSystemNumber($systemNumber);
        }

        return $incunable;
    }

    public function findExistingRelation(IncunableRelation $relation)
    {
        $existing = $this->relationRepo->findOneBy(['hash' => $relation->getHash()]);

        if(!empty($existing) && $existing->getId() == $relation->getId()){
            $existing = null;
        }

        if(!empty($existing)){
            $relation->setSubject(null);
            $relation->setWork(null);
            $relation->setIncunable(null);
            if(!empty($relation->getId())){
                $this->manager->remove($relation);
                $this->manager->flush();
            }

            return $existing;
        }

        $this->persist($relation);
        return $relation;
    }

    public function findExistingTitle(Title $title)
    {
        $existing = $this->titleRepo->findOneBy(['hash' => $title->getHash()]);

        if(!empty($existing) && $existing->getId() == $title->getId()){
            $existing = null;
        }

        if(!empty($existing))
        {
            return $existing;
        }

        $this->persist($title);
        return $title;
    }

    public function findExistingWork(Work $work): Work
    {
        $existing = null;

        if(!empty($work->getGnd())){
            $existing = $this->workRepo->findOneBy(['gnd' => $work->getGnd()]);
        }

        if(empty($existing)){
            $existing = $this->workRepo->findOneBy(['hash' => $work->getHash()]);
            if(!empty($existing) && empty($existing->getGnd())){
                $existing->setGnd($work->getGnd());
            }
        }

        if(!empty($existing) && $existing->getId() == $work->getId()){
            $existing = null;
        }

        if(!empty($existing))
        {
            $existing->mergeTitles($work->getTitles()->toArray());

            foreach($work->getTitles() as $title){
                $work->removeTitle($title);
            }

            foreach($work->getRelations() as $relation)
            {
                $relation->setWork($existing);
            }

            $this->persist($existing);
            return $existing;
        }

        $this->persist($work);
        return $work;
    }

    public function findExistingSubject(RelationSubject $subject): RelationSubject
    {
        $existing = null;

        if(!empty($subject->getGnd())){
            $existing = $this->subjectRepo->findOneBy(['gnd' => $subject->getGnd()]);
        }

        if(empty($existing)){
            $existing = $this->subjectRepo->findOneBy(['hash' => $subject->getHash()]);
            if(!empty($existing) && empty($existing->getGnd())){
                if(!empty($subject->getGnd())) {
                    $existing->setGnd($subject->getGnd());
                }
            }
        }

        if(!empty($existing) && $existing->getId() == $subject->getId()){
            $existing = null;
        }

        if(!empty($existing)){
            foreach($subject->getRelations() as $relation)
            {
                $relation->setSubject($existing);
                $subject->removeRelation($relation);
            }

            $existing->mergeLocations($subject);

            return $existing;
        }

        $this->persist($subject);
        return $subject;
    }

    public function findExistingImprint(Imprint $imprint): Imprint
    {
        $existing = $this->imprintRepo->findOneBy(['hash' => $imprint->getHash()]);
        if(!empty($existing)){
            return $existing;
        }

        $this->persist($imprint);
        return $imprint;
    }

    public function findExistingReference(Reference $reference): Reference
    {
        $existing = $this->referenceRepo->findOneBy(['hash' => $reference->getHash()]);
        if(!empty($existing)){
            return $existing;
        }

        $this->persist($reference);
        return $reference;
    }

    public function findExistingDigitalisation(Digitalisation $digitalisation): Digitalisation
    {
        $existing = $this->digitalisationRepo->findOneBy(['hash' => $digitalisation->getHash()]);
        if(!empty($existing)){
            return $existing;
        }

        $this->persist($digitalisation);
        return $digitalisation;
    }

    public function findRelationType(string $abbreviation): ?RelationType
    {
        return $this->relationTypeRepo->findOneBy(['abbreviation' => $abbreviation]);
    }

    public function persist($entity)
    {
        $this->manager->persist($entity);
        if($entity instanceof RelationSubject)
        {
            foreach($entity->getLocations() as $location){
                $location->addSubject($entity);
                $this->persist($location);
            }
        }

        $this->manager->flush();

        $this->manager->refresh($entity);
    }

    public function cleanUp(){
        foreach($this->relationRepo->findAll() as $relation){
            if(empty($relation->getIncunable())){
                $relation->setWork(null);
                $relation->setSubject(null);
                $relation->setType(null);
                $this->manager->persist($relation);
                $this->manager->remove($relation);
            }
        }

        $this->manager->flush();

        foreach($this->subjectRepo->findAll() as $subject){
            if($subject->getRelations()->isEmpty()){
                $this->manager->remove($subject);
            }
        }

        $this->manager->flush();

        foreach($this->workRepo->findAll() as $work){
            if($work->getRelations()->isEmpty()){
                foreach($work->getTitles() as $title){
                    $work->removeTitle($title);
                }

                $this->manager->persist($work);
                $this->manager->remove($work);
            }
        }

        $this->manager->flush();

        foreach($this->titleRepo->findAll() as $title){
            if($title->getWorks()->isEmpty() && $title->getIncunables()->isEmpty()){
                $this->manager->remove($title);
            }
        }

        $this->manager->flush();

        foreach($this->findAllImprints() as $imprint)
        {
            if($imprint->getIncunables()->isEmpty()){
                $this->manager->remove($imprint);
            }
        }

        foreach($this->findAllLocations() as $location){
            if($location->getIncunables()->isEmpty() && $location->getSubjects()->isEmpty()){
                foreach($location->getCountries() as $country){
                    $location->removeCountry($country);
                    $this->manager->persist($country);
                }
                $this->manager->remove($location);
            }
        }

        $this->manager->flush();

        $this->manager->flush();
    }
}