<?php

namespace App\Service;

use App\Entity\Country;
use App\Entity\Incunable;
use App\Entity\RelationSubject;
use App\Entity\SearchEntry;
use App\Entity\Title;
use App\Helper\Search\SearchResults;
use App\Helper\StringHelper;
use App\Repository\SearchEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchService
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var IncunableService
     */
    private $incunableService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(EntityManagerInterface $manager, IncunableService $incunableService, TranslatorInterface $translator)
    {
        $this->manager = $manager;
        $this->incunableService = $incunableService;
        $this->translator = $translator;
    }

    public function search(string $query): Searchresults
    {
        /* @var SearchEntryRepository $repo */
        $repo = $this->manager->getRepository(SearchEntry::class);

        return new SearchResults($repo->findByQuery($query));
    }

    public function updateSearchIndex(Incunable $incunable)
    {
        /* @var SearchEntry[] */
        $searchEntries = [];

        $searchEntries[] = $this->createSearchEntry('SystemNumber', $incunable->getSystemNumber(), 0, $incunable);


        $preferredTitle = $incunable->getPreferredTitle();

        $searchEntries[] = $this->createSearchEntry('Bevorzugter Titel', $preferredTitle->getValue(), 0, $incunable);


        foreach($incunable->getTitles() as $title)
        {
            if(!$title->equals($preferredTitle)){
                $searchEntries[] = $this->createSearchEntry($title->getIntroductoryText(), $title->getValue(), 10, $incunable);
            }
        }

        if(!empty($incunable->getYearOfPublicationFrom()) && !empty($incunable->getYearOfPublicationTo()))
        {
            $from = $incunable->getYearOfPublicationFrom();
            $to = $incunable->getYearOfPublicationTo();
            $searchEntries[] = $this->createSearchEntry('Publikation', $from . ' - ' . $to, 20, $incunable);

            for($year = $incunable->getYearOfPublicationFrom() + 1; $year < $incunable->getYearOfPublicationTo(); $year++)
            {
                $searchEntries[] = $this->createSearchEntry('Publikation', $from . ' - ' . $to . ' (' . $year . ')', 20, $incunable);
            }
        }elseif(!empty($incunable->getYearOfPublicationFrom())){
            $year = $incunable->getYearOfPublicationFrom();
            $searchEntries[] = $this->createSearchEntry('Publikation', $year, 20, $incunable);
        }elseif(!empty($incunable->getYearOfPublicationTo())){
            $year = $incunable->getYearOfPublicationTo();
            $searchEntries[] = $this->createSearchEntry('Publikation', $year, 20, $incunable);
        }

        if(!empty($incunable->getEdition())) {
            $searchEntries[] = $this->createSearchEntry('Ausgabe', $incunable->getEdition(), 20, $incunable);
        }

        if(!empty($incunable->getPages())) {
            $searchEntries[] = $this->createSearchEntry('Umfang', $incunable->getPages(), 20, $incunable);
        }

        if(!empty($incunable->getBookBlock())){
            $searchEntries[] = $this->createSearchEntry('Buchblock', $incunable->getBookBlock(), 20, $incunable);
        }


        foreach($incunable->getContains() as $value) {
            $searchEntries[] = $this->createSearchEntry('Enthält', $value, 20, $incunable);
        }


        if(!empty($incunable->getCover())){
            $searchEntry = new SearchEntry();
            $searchEntry->setField("Einband");
            $searchEntry->setValue($incunable->getCover());
            $searchEntry->setPriority(20);
            $searchEntry->setIncunable($incunable);
            $searchEntries[] = $searchEntry;
        }


        if(!empty($incunable->getIllustrations())){
            $searchEntry = new SearchEntry();
            $searchEntry->setField("Illustrationen");
            $searchEntry->setValue($incunable->getIllustrations());
            $searchEntry->setPriority(20);
            $searchEntry->setIncunable($incunable);
            $searchEntries[] = $searchEntry;
        }


        foreach($incunable->getImprintNotes() as $value) {
            $searchEntries[] = $this->createSearchEntry('Weiteres zum Impressum', $value, 20, $incunable);
        }



        foreach($incunable->getNotes() as $value) {
            $searchEntries[] = $this->createSearchEntry('Weiteres zur Ausgabe', $value, 20, $incunable);
        }


        if(!empty($incunable->getFurtherNotes())){
            $searchEntries[] = $this->createSearchEntry('Weiteres zum Exemplar', $incunable->getFurtherNotes(), 20, $incunable);
        }

        foreach($incunable->getPhysicalNotes() as $value) {
            $searchEntries[] = $this->createSearchEntry('Weitere physische Beschreibung', $value, 20, $incunable);
        }

        if(!empty($incunable->getProvenance())){
            $searchEntry = new SearchEntry();
            $searchEntry->setField("Provenienz");
            $searchEntry->setValue($incunable->getProvenance());
            $searchEntry->setPriority(20);
            $searchEntry->setIncunable($incunable);
            $searchEntries[] = $searchEntry;
        }

        if(!empty($incunable->getSignature())){
            $searchEntry = new SearchEntry();
            $searchEntry->setField("Signatur");
            $searchEntry->setValue($incunable->getSignature());
            $searchEntry->setPriority(20);
            $searchEntry->setIncunable($incunable);
            $searchEntries[] = $searchEntry;
        }


        foreach($incunable->getSignatureFormulas() as $value) {
            $searchEntries[] = $this->createSearchEntry('Signaturformel', $value, 20, $incunable);
        }

        if(!empty($incunable->getSize())){
            $searchEntry = new SearchEntry();
            $searchEntry->setField("Masse");
            $searchEntry->setValue($incunable->getSize());
            $searchEntry->setPriority(20);
            $searchEntry->setIncunable($incunable);
            $searchEntries[] = $searchEntry;
        }

        foreach($incunable->getLanguages() as $language){
            $searchEntries[] = $this->createSearchEntry('Sprache', $this->translator->trans($language->getName(), [], null, 'de'), 20, $incunable);
        }

        foreach($incunable->getTranslations() as $translation){
            $searchEntries[] = $this->createSearchEntry('Übersetzung', $this->translator->trans($translation->getName(), [], null, 'de'), 20, $incunable);
        }

        foreach($incunable->getImprints() as $imprint){
            if(!empty($imprint->getYear())) {
                $searchEntry = new SearchEntry();
                $searchEntry->setField("Impressum (Jahr)");
                $searchEntry->setValue($imprint->getYear());
                $searchEntry->setPriority(20);
                $searchEntry->setIncunable($incunable);
                $searchEntries[] = $searchEntry;
            }

            if(!empty($imprint->getLocation())) {
                $searchEntry = new SearchEntry();
                $searchEntry->setField("Impressum (Ort)");
                $searchEntry->setValue($imprint->getLocation());
                $searchEntry->setPriority(20);
                $searchEntry->setIncunable($incunable);
                $searchEntries[] = $searchEntry;
            }

            if(!empty($imprint->getPrinter())) {
                $searchEntry = new SearchEntry();
                $searchEntry->setField("Impressum (Drucker)");
                $searchEntry->setValue($imprint->getPrinter());
                $searchEntry->setPriority(20);
                $searchEntry->setIncunable($incunable);
                $searchEntries[] = $searchEntry;
            }
        }

        foreach($incunable->getScans() as $scan){
            if(!empty($scan->getDescription()))
            {
                $searchEntries[] = $this->createSearchEntry('Beschrieb Digitalisat', $scan->getDescription(), 20, $incunable);
            }
        }

        /* @var Country[] $countries */
        $countries = [];
        foreach($incunable->getLocations() as $location)
        {
            $searchEntries[] = $this->createSearchEntry('Ort', $location->getName(), 50, $incunable);

            foreach($location->getCountries() as $country)
            {
                $countries[$country->getId()] = $country;
            }
        }

        foreach($countries as $country)
        {
            $searchEntries[] = $this->createSearchEntry('Land', $this->translator->trans($country->getName(), [], null, 'de'), 50, $incunable);
        }

        foreach($incunable->getRelations() as $relation){
            $priority = 20;
            $type = "Haupteintrag";
            if(!$relation->getIsMainEntry()){
                $type = "Nebeneintrag";
                $priority = 30;
            }

            if(!empty($relation->getWork())){
                $work = $relation->getWork();

                if(!empty($work->getGnd())) {
                    $searchEntry = new SearchEntry();
                    $searchEntry->setField('Werk (' . $type . ') - GND');
                    $searchEntry->setValue($work->getGnd());
                    $searchEntry->setPriority($priority);
                    $searchEntry->setIncunable($incunable);
                    $searchEntries[] = $searchEntry;
                }

                $preferredTitle = $work->getPreferredTitle();

                $searchEntry = new SearchEntry();
                $searchEntry->setField('Werk (' . $type . ') - Bevorzugter Titel');
                $searchEntry->setValue($preferredTitle->getValue());
                $searchEntry->setPriority($priority);
                $searchEntry->setIncunable($incunable);
                $searchEntries[] = $searchEntry;

                foreach($work->getTitles() as $title)
                {
                    if(!$title->equals($preferredTitle)){
                        $searchEntry = new SearchEntry();
                        $searchEntry->setField('Werk (' . $type . ') - ' . $title->getIntroductoryText());
                        $searchEntry->setValue($title->getValue());
                        $searchEntry->setPriority($priority + 10);
                        $searchEntry->setIncunable($incunable);
                        $searchEntries[] = $searchEntry;
                    }
                }
            }

            if(!empty($relation->getSubject())){
                $subject = $relation->getSubject();
                $subjectType = 'Person';
                if($subject->getType() == RelationSubject::TYPE_CORPORATION){
                    $subjectType = 'Körperschaft';
                }

                if(!empty($subject->getGnd())) {
                    $searchEntry = new SearchEntry();
                    $searchEntry->setField($subjectType . ' (' . $type . ') - GND');
                    $searchEntry->setValue($subject->getGnd());
                    $searchEntry->setPriority($priority);
                    $searchEntry->setIncunable($incunable);
                    $searchEntries[] = $searchEntry;
                }

                $searchEntry = new SearchEntry();
                $searchEntry->setField($subjectType . ' (' . $type . ')');
                $searchEntry->setValue($subject->__toString());
                $searchEntry->setPriority($priority);
                $searchEntry->setIncunable($incunable);
                $searchEntries[] = $searchEntry;
            }
        }

        $usedIds = [];

        foreach($searchEntries as $searchEntry)
        {
            $searchEntry = $this->findExistingOrPersistSearchEntry($searchEntry);
            $usedIds[] = $searchEntry->getId();
        }

        foreach($this->manager->getRepository(SearchEntry::class)->findBy(['incunable' => $incunable]) as $searchEntry){
            /* @var SearchEntry $searchEntry */
            if(!in_array($searchEntry->getId(), $usedIds)){
                $this->manager->remove($searchEntry);
            }
        }

        $this->manager->flush();
    }

    protected function createSearchEntry(string $field, $value, $priority, Incunable $incunable): SearchEntry
    {
        $searchEntry = new SearchEntry();
        $searchEntry->setField($field);
        $searchEntry->setValue(StringHelper::normalize($value));
        $searchEntry->setPriority($priority);
        $searchEntry->setIncunable($incunable);
        return $searchEntry;
    }

    protected function findExistingOrPersistSearchEntry(SearchEntry $searchEntry): SearchEntry
    {
        /* @var SearchEntry $existing */
        $existing = $this->manager->getRepository(SearchEntry::class)->findOneBy(['hash' => $searchEntry->getHash()]);
        if(!empty($existing)){
            return $existing;
        }

        $this->manager->persist($searchEntry);
        $this->manager->flush();
        return $searchEntry;
    }

    /*
    protected function addSearchEntry(SearchEntry $searchEntry, array &$searchEntries){
        if(mb_strlen($searchEntry->getValue()) <= 255){
            $searchEntries[] = $searchEntry;
            return;
        }

        $words = explode(" ", $searchEntry->getValue());

        $first = true;

        while(!empty($words)){
            $value = "";
            while(mb_strlen(trim($value . ' ' . $words[0])) <= 255 - 6){
                $value .= ' ' . array_shift($words);
                $value = trim($value);
                if($first){
                    $value .= ' [...]';
                    $first = false;
                }else{
                    $value = '[...] ' . $value;
                }

                $partialSearchEntry = new SearchEntry();
                $partialSearchEntry->setField($searchEntry->getField());
                $partialSearchEntry->setValue($value);
                $partialSearchEntry->setPriority($searchEntry->getPriority());
                $partialSearchEntry->setIncunable($searchEntry->getIncunable());
                $searchEntries[] = $partialSearchEntry;

                dump($value);
            }
        }
    }
    */
}