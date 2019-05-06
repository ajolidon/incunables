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
use App\Service\IncunableImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/l")
 */
class LocationController extends AbstractController
{
    /**
     * @Route("/{slug}/{id}/", name="location_show")
     */
    public function show(Location $location)
    {
        return $this->render('location/index.html.twig', ['location' => $location]);
    }
}
