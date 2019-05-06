<?php

namespace App\Command;

use App\Entity\Country;
use App\Entity\Digitalisation;
use App\Entity\Imprint;
use App\Entity\Incunable;
use App\Entity\IncunableRelation;
use App\Entity\Language;
use App\Entity\Location;
use App\Entity\Reference;
use App\Entity\RelationSubject;
use App\Entity\Title;
use App\Entity\Work;
use App\Exception\DataReceiverException;
use App\Repository\CountryRepository;
use App\Service\DataReceiver\AlephDataReceiver;
use App\Service\DataReceiver\DataReceiverFactory;
use App\Service\DataReceiver\SwissBibDataReceiver;
use App\Service\GndService;
use App\Service\IncunableImportService;
use App\Service\Marc21\DataField;
use App\Service\Marc21\Marc21Parser;
use App\Service\Marc21\Record;
use App\Service\ScanImportService;
use App\Service\SearchService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class ImportCommand extends Command
{
    protected static $defaultName = 'inc:import';

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var DataReceiverFactory
     */
    protected $dataReceiverFactory;

    /**
     * @var IncunableImportService
     */
    protected $incunableService;

    /**
     * @var GndService
     */
    protected $gndService;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @var bool
     */
    protected $force = false;

    /**
     * @var ScanImportService
     */
    protected $scanImportService;

    /**
     * @var string
     */
    protected $translationFile;

    /**
     * @var array
     */
    protected $translations = [];

    /**
     * @var string
     */
    protected $cacheDir;

    public function __construct(DataReceiverFactory $dataReceiverFactory, IncunableImportService $incunableService, GndService $gndService, EntityManagerInterface $manager, ScanImportService $scanImportService, KernelInterface $kernel)
    {
        parent::__construct(self::$defaultName);

        $this->dataReceiverFactory = $dataReceiverFactory;
        $this->incunableService = $incunableService;
        $this->gndService = $gndService;
        $this->manager = $manager;
        $this->scanImportService = $scanImportService;
        $this->translationFile = $kernel->getProjectDir() . '/translations/messages.de.yaml';
        $this->translations = Yaml::parseFile($this->translationFile);
        $this->cacheDir = $kernel->getCacheDir();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('force')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws DataReceiverException
     * @throws \App\Exception\Marc21Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->force = $input->getOption('force');

        $this->updateCountries();

        /* @var SwissBibDataReceiver $swissbib */

        $swissbib = $this->dataReceiverFactory->get('swissbib');
        $systemNumbers = $swissbib->receive();

        $this->io->note(count($systemNumbers) . ' incunables found');

        /* @var AlephDataReceiver $aleph */

        $aleph = $this->dataReceiverFactory->get('aleph');

        foreach($systemNumbers as $key => $systemNumber) {
            $res = [
                '/<responseDate>.*<\/responseDate>/m',
            ];

            $xml = $aleph->receive(['systemNumber' => $systemNumber['idsbb']]);
            foreach($res as $re) {
                $xml = preg_replace($re, '', $xml);
            }

            $parser = new Marc21Parser();

            if($this->handleRecord($parser->parse($xml), $systemNumber['swissbib'], md5($xml))) {
                $this->io->note("Incunable " . ($key + 1) . " of " . count($systemNumbers) . " updated.");
            }else{
                $this->io->note("Incunable " . ($key + 1) . " of " . count($systemNumbers) . " needs no data update.");
            }

        }

        $this->io->note('Cleaning up...');
        $this->incunableService->cleanUp();

        $this->io->note('Update translation file...');
        file_put_contents($this->translationFile, Yaml::dump($this->translations));

        $this->io->success('Import finished');
    }

    /**
     * @param Record $record
     * @param $hash
     * @return bool
     * @throws \Exception
     */
    protected function handleRecord(Record $record, $swissbibSystemNumber, $hash): bool
    {
        $systemNumber = (int) $record->getControlField('001')->getValue();

        $incunable = $this->incunableService->findOrCreateIncunableBySystemNumber($systemNumber);
        $incunable->setSwissbibSystemNumber($swissbibSystemNumber);

        if(!$this->force && $incunable->getHash() == $hash) {
            $this->io->note("Update scans for incunable " . $incunable->getSystemNumber() . " ...");
            $this->handleNotRecognizedFiles($this->scanImportService->updateScans($incunable));
            $this->checkModified($incunable);
            return false;
        }

        $this->io->note("Updating data for incunable " . $incunable->getSystemNumber() . ' ...');

        $incunable->setHash($hash);
        //if($record->getDateStamp() > $incunable->getLastModified()) {
            $incunable->setLastModified($record->getDateStamp());
        //}

        if(empty($incunable->getId())){
            $this->incunableService->persist($incunable);
        }else{
            $incunable->clearArrays();

            foreach($incunable->getRelations() as $relation)
            {
                $incunable->removeRelation($relation);
                $relation->setWork(null);
                $relation->setSubject(null);
                $relation->setType(null);
                $this->manager->remove($relation);
            }

            $this->manager->flush();
        }

        $this->addYears($incunable, $record);
        $this->addSignature($incunable, $record);
        $this->addEdition($incunable, $record);
        $this->addLanguages($incunable, $record);
        $this->addImprints($incunable, $record);
        $this->addPhysical($incunable, $record);
        $this->addNotes($incunable, $record);
        $this->addContains($incunable, $record);
        $this->addReferences($incunable, $record);
        $this->addDigitalisations($incunable, $record);
        $this->addFurther($incunable, $record);
        $this->addLocations($incunable, $record);
        $this->addTitles($incunable, $record);
        $this->addRelations($incunable, $record);

        $this->incunableService->persist($incunable);

        $this->io->note("Update scans for incunable " . $incunable->getSystemNumber() . " ...");
        $this->handleNotRecognizedFiles($this->scanImportService->updateScans($incunable));
        $this->checkModified($incunable);
        $this->incunableService->persist($incunable);

        return true;
    }

    protected function checkModified(Incunable $incunable)
    {
        foreach($incunable->getScans() as $scan){
            if($scan->getModified() > $incunable->getLastModified()){
                $incunable->setLastModified($scan->getModified());
            }
        }
    }

    protected function handleNotRecognizedFiles(array $notRecognizedFiles)
    {
        foreach($notRecognizedFiles as $notRecognizedFile)
        {
            $this->io->warning('File not recognized: ' . $notRecognizedFile);
        }
    }

    protected function addFurther(Incunable $incunable, Record $record)
    {
        foreach($record->getDataFields('561') as $dataField)
        {
            $incunable->setProvenance($dataField->getSubFields('a')->first()->getValue());
        }

        foreach($record->getDataFields('563') as $dataField)
        {
            $incunable->setCover($dataField->getSubFields('a')->first()->getValue());
        }

        $beginsWith = [
            'Buchblock',
            'Zustand Buchblock',
            'Zustand Buchblöcke',
        ];

        foreach($record->getDataFields('590') as $dataField)
        {

            $value = $dataField->getSubFields('a')->first()->getValue();

            $isBookBlock = false;
            foreach($beginsWith as $search){
                if(mb_strtolower(mb_substr($value, 0, mb_strlen($search))) == mb_strtolower($search)){
                    $isBookBlock = true;
                    break;
                }
            }

            if($isBookBlock){
                $incunable->setBookBlock($value);
            }else{
                $incunable->setFurtherNotes($value);
            }
        }
    }

    protected function addDigitalisations(Incunable $incunable, Record $record)
    {
        foreach($record->getDataFields('530') as $dataField){
            $title = $dataField->getSubFields('a')->first()->getValue();
            $url = $dataField->getSubFields('u')->first()->getValue();

            $digitalisation = new Digitalisation();
            $digitalisation->setTitle($title);
            $digitalisation->setUrl($url);

            $digitalisation = $this->incunableService->findExistingDigitalisation($digitalisation);
            $incunable->addDigitalisation($digitalisation);
        }

        foreach($record->getDataFields('856') as $dataField){
            $title = $dataField->getSubFields('z')->first()->getValue();
            $url = $dataField->getSubFields('u')->first()->getValue();

            $digitalisation = new Digitalisation();
            $digitalisation->setTitle($title);
            $digitalisation->setUrl($url);

            $digitalisation = $this->incunableService->findExistingDigitalisation($digitalisation);
            $incunable->addDigitalisation($digitalisation);
        }
    }

    protected function addReferences(Incunable $incunable, Record $record)
    {
        foreach($record->getDataFields('510') as $dataField){
            $reference = new Reference();
            $reference->setSource($dataField->getSubFields('a')->first()->getValue());

            if($dataField->getSubFields('c')->count() > 0) {
                $reference->setIdentifier($dataField->getSubFields('c')->first()->getValue());
            }

            if($dataField->getSubFields('u')->count() > 0) {
                $reference->setUrl($dataField->getSubFields('u')->first()->getValue());
            }

            if(empty($reference->getIdentifier())){
                if($reference->getSource() == "BnF" && !empty($reference->getUrl()))
                {
                    $re = '/^http[s]{0,1}:\/\/data.bnf.fr\/(.+?)[\/]/m';

                    preg_match_all($re, $reference->getUrl(), $matches, PREG_SET_ORDER, 0);
                }else{
                    $re = '/^ISTC\s(.+)$/m';
                    preg_match_all($re, $reference->getSource(), $matches, PREG_SET_ORDER, 0);
                }

                if (!empty($matches)) {
                    $reference->setSource("ISTC");
                    $reference->setIdentifier($matches[0][1]);
                }
            }

            $reference = $this->incunableService->findExistingReference($reference);
            $incunable->addAdditionalReference($reference);
        }
    }

    protected function addContains(Incunable $incunable, Record $record)
    {
        foreach($record->getDataFields('505') as $dataField)
        {
            $value = null;
            $previousCode = null;

            foreach($dataField->getSubFields() as $subField){
                if(empty($value)){
                    $value = $subField->getValue();
                    $previousCode = $subField->getCode();
                    continue;
                }

                if($subField->getCode() == 'r'){
                    $value .= ' / ' . $subField->getValue();
                }elseif($previousCode == 'g'){
                    $value .= ': ' . $subField->getValue();
                }elseif($previousCode == 't' || $previousCode == 'r'){
                    $value .= ' -- ' . $subField->getValue();
                }else{
                    throw new \Exception("Unknown constellation (" . $subField->getCode() . " after " . $previousCode);
                }

                $previousCode = $subField->getCode();
            }

            $incunable->addContains($value);
        }
    }

    protected function addEdition(Incunable $incunable, Record $record)
    {
        if($record->getDataFields('250')->count() > 1){
            throw new \Exception('More than one edition found.');
        }

        $edition = null;
        $dataField = $record->getDataFields('250')->first();
        if(!empty($dataField)){
            $edition = trim($dataField->getSubFields('a')->first()->getValue());
        }

        $incunable->setEdition($edition);
    }

    protected function addSignature(Incunable $incunable, Record $record)
    {
        $signature = [];
        foreach ($record->getDataFields('949') as $dataField) {
            foreach($dataField->getSubFields('j') as $subField){
                $signature[] = $subField->getValue();
            }
            foreach($dataField->getSubFields('z') as $subField){
                $signature[] = $subField->getValue();
            }
        }

        if(empty($signature)){
            $signature = null;
        }else{
            $signature = join(' ; ', $signature);
        }

        if(strlen($signature) > 255){
            throw new \Exception('Signature longer than 255 chars.');
        }

        $incunable->setSignature($signature);
    }

    protected function addYears(Incunable $incunable, Record $record){
        $header = $record->getControlField('008')->getValue();

        $yearOfPublicationFrom = trim(substr($header, 7, 4));
        $yearOfPublicationTo = trim(substr($header, 11, 4));

        if(empty($yearOfPublicationFrom)){
            $yearOfPublicationFrom = null;
        }
        if(empty($yearOfPublicationTo)){
            $yearOfPublicationTo = null;
        }

        $incunable->setYearOfPublicationFrom($yearOfPublicationFrom);
        $incunable->setYearOfPublicationTo($yearOfPublicationTo);
    }

    protected function addPhysical(Incunable $incunable, Record $record)
    {
        $dataField = $record->getDataFields('300')->first();
        if(empty($dataField)) {
            return;
            //throw new \Exception('No physical description found.');
        }

        $pages = null;
        $illustrations = null;
        $size = null;
        if(!$dataField->getSubFields('a')->isEmpty()) {
            $pages = trim($dataField->getSubFields('a')->first()->getValue());
        }
        if(!$dataField->getSubFields('b')->isEmpty()) {
            $illustrations = trim($dataField->getSubFields('b')->first()->getValue());
        }
        if(!$dataField->getSubFields('c')->isEmpty()) {
            $size = trim($dataField->getSubFields('c')->first()->getValue());
        }

        if(empty($pages)){
            $pages = null;
        }

        if(empty($illustrations)){
            $illustrations = null;
        }

        if(empty($size)){
            $size = null;
        }

        $incunable->setPages($pages);
        $incunable->setIllustrations($illustrations);
        $incunable->setSize($size);
    }

    protected function addNotes(Incunable $incunable, Record $record){
        $imprintNotesCriteria = [
            'Impressum',
            'Drucker',
            'Druckort',
            'Druckdatum',
            'Druckjahr',
            'Erscheinungsdatum',
            'Erscheinungsjahr',
            'Datierung',
            'Zur Datierung',
        ];

        foreach($record->getDataFields('500') as $dataField){
            foreach($dataField->getSubFields('a') as $subField){
                $used = false;
                $value = $subField->getValue();
                if(mb_substr($value, 0, 6) == 'Umfang' || mb_stristr($value, 'unbedruckt')) {
                    $incunable->addPhysicalNote($value);
                    $used = true;
                }

                if(mb_substr($value, 0, 14) == 'Signaturformel' || mb_stristr($value, 'Lagensignatur')){
                    $incunable->addSignatureFormula($value);
                    $used = true;
                }

                foreach($imprintNotesCriteria as $criteria){
                    if(mb_substr($value, 0, mb_strlen($criteria)) == $criteria)
                    {
                        $incunable->addImprintNote($value);
                        $used = true;
                        break;
                    }
                }

                if(!$used){
                    $incunable->addNote($value);
                }
            }
        }
    }

    protected function updateCountries(){
        /* @var CountryRepository $repo */
        $repo = $this->manager->getRepository(Country::class);

        $client = new Client();

        $response = $client->request('GET', 'https://d-nb.info/standards/vocab/gnd/geographic-area-code.html');
        $data = $response->getBody()->getContents();

        $re = '/<a href="#(X[A-Z]{1})">(.+?)<\/a>/m';
        preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0);
        foreach($matches as $match)
        {
            $country = $repo->findOneBy(['abbreviation' => $match[1]]);
            if(empty($country)){
                $country = new Country();
                $country->setAbbreviation($match[1]);
            }

            $country->setName($match[2]);
            $this->manager->persist($country);
            $this->manager->flush();
        }

        $re = '/<a href="#(X[A-Z]{1}-[A-Z]+?)">(.+?)<\/a>/m';

        preg_match_all($re, $data, $matches, PREG_SET_ORDER, 0);
        foreach($matches as $match)
        {
            $country = $repo->findOneBy(['abbreviation' => $match[1]]);
            if(empty($country)){
                $country = new Country();
                $country->setAbbreviation($match[1]);
            }

            $country->setName($match[2]);
            $this->manager->persist($country);
            $this->manager->flush();
        }
    }

    protected function addLocations(Incunable $incunable, Record $record)
    {
        $locations = [];

        foreach($record->getDataFields('751') as $dataField)
        {
            $gnd = null;

            foreach ($dataField->getSubFields('1') as $subField) {
                if(!empty($gnd)){
                    throw new \Exception('Multiple location gnd.');
                }

                $gnd = $subField->getValue();
            }

            if(empty($gnd))
            {
                throw new \Exception('Location gnd missing');
            }

            if(substr($gnd, 0, 8) != '(DE-588)') {
                throw new \Exception('Location gnd prefix is not `(DE-588)`.');
            }

            $gnd = trim(substr($gnd, 8));

            $locations[] = $this->createLocation($gnd);
        }

        $incunable->updateLocations($locations);
    }

    protected function createLocation(string $gnd): Location
    {
        $locationRepo = $this->manager->getRepository(Location::class);
        $countryRepo = $this->manager->getRepository(Country::class);

        $data = $this->gndService->get($gnd);

        $location = $locationRepo->findOneBy(['gnd' => $gnd]);
        if(empty($location))
        {
            $location = new Location();
            $location->setGnd($gnd);
        }

        $location->setName($data['preferredName']);

        $countries = [];

        foreach($data['geographicAreaCode'] as $area)
        {
            $re = '/^.+?#(X[A-Z]{1}-[A-Z]+)/m';
            preg_match_all($re, $area['id'], $matches, PREG_SET_ORDER, 0);

            if(empty($matches)){
                $re = '/^.+?#(X[A-Z]{1})/m';
                preg_match_all($re, $area['id'], $matches, PREG_SET_ORDER, 0);
            }

            if(!empty($matches)){
                $country = $countryRepo->findOneBy(['abbreviation' => $matches[0][1]]);
                if(empty($country))
                {
                    throw new \Exception('Country `' . $matches[0][1] . '` not found.');
                }

                $countries[] = $country;
            }
        }

        $location->updateCountries($countries);

        $this->manager->persist($location);
        $this->manager->flush();

        return $location;
    }

    protected function addLanguages(Incunable $incunable, Record $record)
    {
        $languageRepo = $this->manager->getRepository(Language::class);

        /* @var Language[] $languages */
        $languages = [];

        /* @var Language[] $translations */
        $translations = [];

        $abbreviation = substr($record->getControlField('008')->getValue(), 35, 3);
        if(!empty($abbreviation)){
            $language = $languageRepo->findOneBy(['abbreviation' => $abbreviation]);
            if(empty($language)){
                throw new \Exception('Language `' . $abbreviation . '` not found.');
            }

            $languages[$abbreviation] = $language;
        }

        foreach($record->getDataFields('041') as $dataField)
        {
            foreach($dataField->getSubFields('a') as $subField){
                $abbreviation = $subField->getValue();
                $language = $languageRepo->findOneBy(['abbreviation' => $abbreviation]);
                if(empty($language)){
                    throw new \Exception('Language `' . $abbreviation . '` not found.');
                }

                $languages[$abbreviation] = $language;
            }

            foreach($dataField->getSubFields('h') as $subField){
                $abbreviation = $subField->getValue();
                $language = $languageRepo->findOneBy(['abbreviation' => $abbreviation]);
                if(empty($language)){
                    throw new \Exception('Language `' . $abbreviation . '` not found.');
                }

                $translations[$abbreviation] = $language;
            }
        }

        foreach($languages as $language){
            $language->addLanguageIncunable($incunable);
        }

        foreach($translations as $language){
            $language->addTranslationIncunable($incunable);
        }
    }

    protected function addImprints(Incunable $incunable, Record $record)
    {
        foreach($record->getDataFields('264') as $dataField){
            $imprint = new Imprint();
            $location = null;
            $printer = null;
            $year = null;

            if(!$dataField->getSubFields('a')->isEmpty()){
                $location = trim($dataField->getSubFields('a')->first()->getValue());
            }

            if(!$dataField->getSubFields('b')->isEmpty()){
                $printer = trim($dataField->getSubFields('b')->first()->getValue());
            }

            if(!$dataField->getSubFields('c')->isEmpty()){
                $year = trim($dataField->getSubFields('c')->first()->getValue());
            }

            $imprint->setLocation($location);
            $imprint->setPrinter($printer);
            $imprint->setYear($year);

            $this->incunableService->findExistingImprint($imprint);
            $incunable->addImprint($imprint);
        }
    }


    protected function addRelations(Incunable $incunable, Record $record)
    {
        foreach($record->getDataFields('100') as $dataField)
        {
            $this->addRelation($incunable, $dataField);
        }

        foreach($record->getDataFields('110') as $dataField)
        {
            $this->addRelation($incunable, $dataField);
        }

        foreach($record->getDataFields('700') as $dataField)
        {
            $this->addRelation($incunable, $dataField);
        }

        foreach($record->getDataFields('710') as $dataField)
        {
            $this->addRelation($incunable, $dataField);
        }
    }

    protected function addRelation(Incunable $incunable, DataField $dataField)
    {
        $proceed = true;

        $isMainEntry = false;
        if(substr($dataField->getTag(), 0, 1) == '1'){
            $isMainEntry = true;
        }

        $relationType = null;

        $relation = new IncunableRelation();
        $relation->setIsMainEntry($isMainEntry);

        if(substr($dataField->getTag(), 0, 1) == '7' && !is_null($dataField->getIndicator2())){
            $relationType = $this->incunableService->findRelationType('aut');

            $work = new Work();

            $subFields = $dataField->getSubFields('t');
            if($subFields->isEmpty()){
                $proceed = false;
                dump("=====>");
                dump($dataField->getTag() . '-' . $dataField->getIndicator1() . '-' . $dataField->getIndicator2());
                foreach($dataField->getSubFields() as $subField){
                    dump($subField->getCode() . ': ' . $subField->getValue());
                }
                dump("-----------");
                dump("<=========");
                $this->io->error('Subfield with code `t` expected.');
            }

            if($proceed) {

                $titleValue = $subFields->first()->getValue();

                $subFields = $dataField->getSubFields('1');
                if (!$subFields->isEmpty()) {
                    $gnd = $subFields->first()->getValue();
                    if (substr($gnd, 0, 8) == '(DE-588)') {
                        $gnd = trim(substr($gnd, 8));
                        $work->setGnd($gnd);
                        $tmpValue = $this->gndService->getByKey($gnd, 'preferredName', null);
                        if (!empty($tmpValue)) {
                            $titleValue = $tmpValue;
                        }
                    }
                }

                $title = new Title();
                $title->setType(Title::TYPE_PREFERRED);
                $title->setValue($titleValue);

                $title = $this->incunableService->findExistingTitle($title);

                $work->addTitle($title);

                $work = $this->incunableService->findExistingWork($work);

                $relation->setWork($work);
            }
        }else{
            $subFields = $dataField->getSubFields('4');
            if($subFields->isEmpty()){
                //throw new \Exception('Subfield with code `4` expected.');
                $this->io->warning('Could not use field `' . $dataField->getTag() . '` because of missing relation (Subfield `4`).');
                $proceed = false;
            }else{
                $value = $subFields->first()->getValue();
                $relationType = $this->incunableService->findRelationType($value);
                if(empty($relationType)){
                    throw new \Exception('Relation type `' . $value . '` not found.');
                }

                $subFields = $dataField->getSubFields('e');
                if(!$subFields->isEmpty() && !empty($subFields->first()->getValue())){
                    $this->translations[$relationType->getName()] = $subFields->first()->getValue();
                }
            }
        }

        if($proceed) {
            $relation->setType($relationType);
            $relation->setSubject($this->createSubject($dataField));

            $relation->setIncunable($incunable);

            $relation = $this->incunableService->findExistingRelation($relation);

            $incunable->addRelation($relation);

            if(!empty($relation->getSubject())){
                foreach($relation->getSubject()->getLocations() as $location)
                {
                    $incunable->addLocation($location);
                }
            }
        }
    }

    protected function createSubject(DataField $dataField)
    {

        $subjectType = RelationSubject::TYPE_PERSON;

        if(substr($dataField->getTag(), 1) == '10'){
            $subjectType = RelationSubject::TYPE_CORPORATION;
        }

        $subject = new RelationSubject();

        $subject->setType($subjectType);

        $subFields = $dataField->getSubFields('a');
        if($subFields->isEmpty()){
            throw new \Exception('Subfield with code `a` expected.');
        }else{
            if ($subFields->count() > 1) {
                throw new \Exception('Only one subfield with code `a` expected.');
            }

            $subject->setName($subFields->first()->getValue());
        }

        $subFields = $dataField->getSubFields('b');
        if(!$subFields->isEmpty()){
            if ($subFields->count() > 1) {
                throw new \Exception('Only one subfield with code `b` expected.');
            }

            $subject->setCounting($subFields->first()->getValue());
        }

        $subFields = $dataField->getSubFields('c');
        if(!$subFields->isEmpty()){
            if ($subFields->count() > 1) {
                throw new \Exception('Only one subfield with code `c` expected.');
            }

            $subject->setAddition($subFields->first()->getValue());
        }

        $subFields = $dataField->getSubFields('d');
        if(!$subFields->isEmpty()){
            if ($subFields->count() > 1) {
                throw new \Exception('Only one subfield with code `d` expected.');
            }

            $subject->setBiographical($subFields->first()->getValue());
        }

        if(is_null($dataField->getIndicator2())){
            $subFields = $dataField->getSubFields('1');
            if(!$subFields->isEmpty()){
                $gnd = $subFields->first()->getValue();
                if(substr($gnd, 0, 8) == '(DE-588)') {
                    $gnd = trim(substr($gnd, 8));
                    $subject->setGnd($gnd);
                    $data = $this->gndService->get($gnd);
                    if(isset($data['placeOfBirth'])){
                        if(count($data['placeOfBirth']) > 1){
                            throw new \Exception('Multiple places of birth.');
                        }
                        foreach($data['placeOfBirth'] as $arr) {
                            $gnd = $this->getGndFromId($arr['id']);
                            $subject->setPlaceOfBirth($this->createLocation($gnd));
                        }
                    }

                    if(isset($data['placeOfDeath'])){
                        if(count($data['placeOfDeath']) > 1){
                            throw new \Exception('Multiple places of death.');
                        }
                        foreach($data['placeOfDeath'] as $arr) {
                            $gnd = $this->getGndFromId($arr['id']);
                            $subject->setPlaceOfDeath($this->createLocation($gnd));
                        }
                    }

                    if(isset($data['placeOfBusiness'])){
                        if(count($data['placeOfBusiness']) > 2){
                            dump($dataField->getTag());
                            foreach($dataField->getSubFields() as $subField){
                                dump($subField->getCode() . ': ' . $subField->getValue());
                            }

                            dump($data['placeOfBusiness']);
                            throw new \Exception('More than two places of business.');
                        }

                        $isFirst = true;

                        foreach($data['placeOfBusiness'] as $arr) {
                            $gnd = $this->getGndFromId($arr['id']);
                            if($isFirst) {
                                $subject->setPlaceOfBusiness($this->createLocation($gnd));
                                $isFirst = false;
                            }else{
                                $subject->setSecondPlaceOfBusiness($this->createLocation($gnd));
                            }
                        }
                    }
                }
            }
        }

        return $this->incunableService->findExistingSubject($subject);
    }

    protected function getGndFromId(string $id): ?string
    {
        $re = '/\/gnd\/([0-9a-zA-Z\-]+)$/m';
        preg_match_all($re, $id, $matches, PREG_SET_ORDER, 0);
        if(!empty($matches)){
            return $matches[0][1];
        }

        return null;
    }

    /**
     * @param Incunable $incunable
     * @param Record $record
     * @throws \Exception
     */
    protected function addTitles(Incunable $incunable, Record $record)
    {
        $addedTitles = [];
        $titleTags = ['130', '240', '245', '246', '730'];
        $dataFields = [];

        foreach($titleTags as $tag)
        {
            $dataFields = array_merge($dataFields, $record->getDataFields($tag)->toArray());
        }

        foreach($dataFields as $dataField)
        {
            $addedTitles[] = $this->addTitle($incunable, $dataField);
        }
    }

    /**
     * @param Incunable $incunable
     * @param DataField $dataField
     * @return Title
     * @throws \Exception
     */
    protected function addTitle(Incunable $incunable, DataField $dataField): Title
    {
        $title = new Title();

        if($dataField->getTag() == '130')
        {
            $title->setType(Title::TYPE_UNIFORM);
        }elseif($dataField->getTag() == '240') {
            $title->setType(Title::TYPE_PREFERRED);
        }elseif($dataField->getTag() == '245') {
            $title->setType(Title::TYPE_PRESENT);
        }elseif($dataField->getTag() == '246') {
            if($dataField->getIndicator2() === '0') {
                $title->setType(Title::TYPE_PARTIAL);
            }elseif($dataField->getIndicator2() === '3'){
                $title->setType(Title::TYPE_VARIANT);
            }elseif(is_null($dataField->getIndicator2())){
                $title->setType(Title::TYPE_OTHER);
            }
        }elseif($dataField->getTag() == '730'){
            $title->setType(Title::TYPE_UNIFORM_FURTHER);
        }

        if(is_null($title->getType()))
        {
            throw new \Exception('Could not detect title type (Field: ' . $dataField->getTag() . ', second indicator: ' . $dataField->getIndicator2() . ').');
        }

        $subFields = $dataField->getSubFields('a');
        if($subFields->count() > 1){
            throw new \Exception('More than one subfield $a found in ' . $dataField->getTag() . '.');
        }

        $a = null;
        if($subFields->count() > 0) {
            $a = $subFields->first()->getValue();
        }

        if($title->getType() == Title::TYPE_UNIFORM || $title->getType() == Title::TYPE_PREFERRED){
            $subFieldsG = $dataField->getSubFields('g');
            if($subFieldsG->count() > 0){
                $a .= " (" . $subFieldsG->first()->getValue() . ")";
            }

            $subFieldsP = $dataField->getSubFields('p');
            $arrP = [];
            foreach($subFieldsP as $subField)
            {
                $arrP[] = $subField->getValue();
            }

            if(count($arrP)){
                $a .= ". " . join(". ", $arrP) . ".";
            }

            $title->setValue($a);

        }elseif($title->getType() == Title::TYPE_PRESENT){
            $titleValue = $a;
            $furtherTitle = "";
            foreach($dataField->getSubFields('b') as $subField)
            {
                $furtherTitle .= ' : ' . $subField->getValue();
            }

            $furtherTitle .= " / ";

            $subValues = [];
            foreach($dataField->getSubFields('c') as $subField)
            {
                $subValues[] = $subField->getValue();
            }

            $furtherTitle .= join(" ; ", $subValues);
            if(!empty($subValues)){
                $furtherTitle .= " ; ";
            }

            foreach($dataField->getSubFields('i') as $subField)
            {
                $furtherTitle .= $subField->getValue() . '. ';
            }

            foreach($dataField->getSubFields('j') as $subField)
            {
                $furtherTitle .= $subField->getValue() . '. ';
            }

            if($furtherTitle !== " / ")
            {
                $titleValue = $titleValue . $furtherTitle;
            }

            $title->setValue(trim($titleValue));
        }elseif($title->getType() == Title::TYPE_UNIFORM_FURTHER) {
            $subFieldsG = $dataField->getSubFields('g');
            if($subFieldsG->count() > 0){
                $a .= ". " . $subFieldsG->first()->getValue();
            }

            $subFieldsN = $dataField->getSubFields('n');
            if($subFieldsN->count() > 0){
                $a .= ". " . $subFieldsN->first()->getValue();
            }

            $title->setValue(trim($a));
        }else{
            $subFieldCodes = ['b', 'n', 'p'];

            $titleValue = $a;

            $subValues = [];
            foreach($subFieldCodes as $code)
            {
                foreach($dataField->getSubFields($code) as $subField)
                {
                    $subValues[] = $subField->getValue();
                }
            }

            if(!empty($subValues))
            {
                $titleValue .= " : " . join(". ", $subValues) . ".";
            }

            $title->setValue(trim($titleValue));
        }

        if($title->getType() == Title::TYPE_OTHER){
            $introductoryText = null;
            $subFields = $dataField->getSubFields('i');
            if($subFields->count() > 1){
                throw new \Exception('More than one $i in ' . $dataField->getTag());
            }

            if($subFields->count() > 0){
                $introductoryText .= $subFields->first()->getValue() . '. ';
                $title->setIntroductoryText(trim($introductoryText));
            }
        }

        $title = $this->incunableService->findExistingTitle($title);

        $incunable->addTitle($title);

        if($title->getType() == Title::TYPE_UNIFORM || $title->getType() == Title::TYPE_UNIFORM_FURTHER)
        {
            $subFields = $dataField->getSubFields('1');
            if($subFields->count() > 1){
                throw new \Exception('More than one subfield 1 found in ' . $dataField->getTag() . '.');
            }

            if($subFields->count() > 0){
                $gnd = $subFields->first()->getValue();
                if(substr($gnd, 0, 8) == '(DE-588)')
                {
                    $gnd = trim(substr($gnd, 8));
                    $value = $this->gndService->getByKey($gnd, 'preferredName');

                    dump($incunable->getSystemNumber());
                    dump($gnd);
                    dump($this->gndService->get($gnd));

                    $work = new Work();
                    $work->setGnd($gnd);
                    $workTitle = new Title();
                    $workTitle->setType(Title::TYPE_UNIFORM);
                    $workTitle->setValue($value);

                    $workTitle = $this->incunableService->findExistingTitle($workTitle);

                    $work->addTitle($workTitle);

                    $work = $this->incunableService->findExistingWork($work);

                    $relation = new IncunableRelation();
                    $relation->setWork($work);

                    $relation->setIncunable($incunable);

                    $relation = $this->incunableService->findExistingRelation($relation);

                    $incunable->addRelation($relation);
                }
            }
        }

        return $title;
    }
}
