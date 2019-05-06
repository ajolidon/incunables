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
use App\Entity\Scan;
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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ScanImportService
{
    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @var string
     */
    protected $scansDirectory;

    /**
     * @var string
     */
    protected $publicDirectory;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container, KernelInterface $kernel)
    {
        $this->manager = $manager;
        $this->scansDirectory = $container->getParameter('scans_dir');
        $this->publicDirectory = $kernel->getProjectDir() . '/public/';
    }

    /**
     * @param Incunable $incunable
     * @return array with files which are not recognized
     * @throws \Exception
     */
    public function updateScans(Incunable $incunable): array
    {
        $notRecognizedFiles = [];
        $textFiles = [];

        $systemNumber = $incunable->getSystemNumber();
        $systemNumber = str_pad($systemNumber, 9, "0", STR_PAD_LEFT);
        $dir = $this->scansDirectory . $systemNumber . '/';
        if(file_exists($dir))
        {
            $result = [];
            $files = scandir($dir);
            foreach($files as $file){
                $re = '/^.+?\.([tT][iI][fF][fF]{0,1})$/m';
                preg_match_all($re, $file, $matches, PREG_SET_ORDER, 0);
                if(!empty($matches)){
                    $modified = new \DateTime();
                    $modified->setTimestamp(filemtime($dir . $file));
                    $textFile = str_replace($matches[0][1], 'txt', $file);

                    $description = null;

                    $textFiles[] = $textFile;

                    if(!file_exists($dir . $textFile)){
                        $re = '/^(.+?)_(.+?)\.[tT][iI][fF][fF]{0,1}$/m';
                        preg_match_all($re, $file, $matches, PREG_SET_ORDER, 0);
                        if(!empty($matches) && is_numeric($matches[0][2])){
                            $number = (int) $matches[0][2];
                            foreach($files as $txtFile){
                                $re = '/^(.+?)_(.+?)\.[tT][xX][tT]$/m';
                                preg_match_all($re, $txtFile, $txtMatches, PREG_SET_ORDER, 0);
                                if(!empty($txtMatches) && $matches[0][1] == $txtMatches[0][1] && is_numeric($txtMatches[0][2])){
                                    $txtNumber = (int) $txtMatches[0][2];
                                    if($number == $txtNumber) {
                                        $source = $dir . $txtFile;
                                        $destination = $dir . $txtMatches[0][1] . '_' . $matches[0][2] . '.txt';
                                        rename($source, $destination);
                                    }
                                }
                            }
                        }
                    }


                    if(file_exists($dir . $textFile)){
                        $description = trim(str_replace("\r\n", "\n", file_get_contents($dir . $textFile)));
                        $textFiles[] = $textFile;
                    }

                    $result[] = [
                        'path' => substr($dir, mb_strlen($this->scansDirectory)) . $file,
                        'modified' => $modified,
                        'description' => $description,
                    ];

                }elseif(substr($file, 0, 1) != "." && $file != "Thumbs.db"){
                    $notRecognizedFiles[] = $file;
                }
            }

            $paths = [];
            foreach($result as $entry){
                $paths[] = $entry['path'];
                $scan = $this->getScanByPath($incunable, $entry['path']);
                $needsUpdate = true;

                if(empty($scan)){
                    $scan = new Scan();
                }else{
                    if($scan->getModified()->format('Y-m-d H:i:s') == $entry['modified']->format('Y-m-d H:i:s')){
                        $needsUpdate = false;
                    }
                }

                $scan->setPath($entry['path']);
                $scan->setDescription($entry['description']);
                $scan->setModified($entry['modified']);

                $incunable->addScan($scan);

                if($needsUpdate){
                    $this->updateScan($scan);
                }
            }

            foreach($incunable->getScans() as $scan){
                if(!in_array($scan->getPath(), $paths)){
                    $incunable->removeScan($scan);
                    @unlink($this->publicDirectory . $scan->getPublicPath());
                }
            }

            $this->manager->persist($incunable);
            $this->manager->flush();
        }

        foreach($notRecognizedFiles as $key => $file){
            if(in_array($file, $textFiles)){
                unset($notRecognizedFiles[$key]);
            }

        }

        return array_values($notRecognizedFiles);
    }

    protected function updateScan(Scan $scan)
    {
        $image = new \Imagick($this->scansDirectory . $scan->getPath());
        $image->setImageFormat('jpg');
        $image->writeImage($this->publicDirectory . $scan->getPublicPath());

        $geometry = $image->getImageGeometry();
        $scan->setWidth($geometry['width']);
        $scan->setHeight($geometry['height']);
    }

    protected function getScanByPath(Incunable $incunable, string $path): ?Scan
    {
        foreach($incunable->getScans() as $scan)
        {
            if($scan->getPath() == $path){
                return $scan;
            }
        }

        return null;
    }
}