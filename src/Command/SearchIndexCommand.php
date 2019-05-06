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

class SearchIndexCommand extends Command
{
    protected static $defaultName = 'inc:search:index';

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var IncunableImportService
     */
    protected $incunableService;

    protected $searchService;

    public function __construct(IncunableImportService $incunableService, SearchService $searchService)
    {
        parent::__construct(self::$defaultName);

        $this->incunableService = $incunableService;
        $this->searchService = $searchService;
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->note('Update search index...');

        $incunables = $this->incunableService->findAllIncunables();
        $progressBar = new ProgressBar($output, count($incunables));

        $progressBar->start();

        foreach($incunables as $incunable){
            $this->searchService->updateSearchIndex($incunable);
            $progressBar->advance();
        }

        $progressBar->finish();
    }
}
