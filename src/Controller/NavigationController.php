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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NavigationController extends AbstractController
{
    public function topNavigation(Request $request, ImageService $imageService, RequestStack $requestStack)
    {
        $query = $request->get('query');
        $headerImage = $imageService->getRandomHeaderImagePath();

        $route = $requestStack->getMasterRequest()->attributes->get('_route');

        return $this->render('navigation/top.html.twig', ['headerImage' => $headerImage, 'query' => $query, 'route' => $route]);
    }
}
