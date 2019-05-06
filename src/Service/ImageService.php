<?php

namespace App\Service;


use App\Entity\Scan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageService
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(EntityManagerInterface $manager, KernelInterface $kernel)
    {
        $this->manager = $manager;
        $this->kernel = $kernel;
    }

    public function getRandomHeaderImagePath(): ?string
    {
        /* @var \App\Entity\Scan[] $scans */
        $scans = $this->manager->getRepository(Scan::class)->findAll();
        if(count($scans) == 0){
            return null;
        }

        $idx = mt_rand(0, count($scans) - 1);
        $scan = $scans[$idx];

        $path = $this->kernel->getProjectDir() . '/public' . $scan->getHeaderPath();
        if(!file_exists($path)) {
            $image = new \Imagick($this->kernel->getProjectDir() . '/public' . $scan->getPublicPath());
            $image->scaleImage(1000, 0);
            $image->cropImage(1000, 100, 0, round($image->getImageHeight() / 2) - 50);
            $image->setImageFormat('jpg');
            $image->setCompressionQuality(10);
            $image->writeImage($path);
        }

        return $scan->getHeaderPath();
    }
}