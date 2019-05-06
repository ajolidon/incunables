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
 * @Route("/c")
 */
class CountryController extends AbstractController
{
    /**
     * @Route("/{slug}/{id}/", name="country_show")
     */
    public function show(Country $country)
    {
        return $this->render('country/index.html.twig', ['country' => $country]);
    }
}
