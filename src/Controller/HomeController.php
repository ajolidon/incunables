<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Incunable;
use App\Entity\Language;
use App\Entity\Location;
use App\Entity\RelationSubject;
use App\Entity\Scan;
use App\Entity\Title;
use App\Entity\Work;
use App\Service\ImageService;
use App\Service\IncunableImportService;
use App\Service\IncunableService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(IncunableService $incunableService)
    {
        $incunables = $incunableService->getNewestIncunables();

        return $this->render('home/index.html.twig', ['incunables' => $incunables]);
    }
}
