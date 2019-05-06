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
use App\Helper\StringHelper;
use App\Service\ImageService;
use App\Service\IncunableImportService;
use App\Service\IncunableService;
use App\Service\SearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchController extends AbstractController
{
    /**
     * @Route("/search/", name="search")
     */
    public function search(Request $request, SearchService $searchService)
    {
        $query = trim($request->get('q'));
        if(mb_strlen($query) < 3){
            return $this->render('search/query_to_short.html.twig', ['query' => $query]);
        }

        return $this->render('search/index.html.twig', ['query' => $query, 'results' => $searchService->search($query)->getResults()]);
    }
}
