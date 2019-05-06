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
use App\Repository\IncunableRepository;
use App\Service\IncunableImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/i")
 */
class IncunableController extends AbstractController
{
    /**
     * @Route("/{slug}/{id}/", name="incunable_show")
     */
    public function show(Incunable $incunable)
    {
        return $this->render('incunable/index.html.twig', ['incunable' => $incunable]);
    }
}
