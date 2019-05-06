<?php

namespace App\Controller;

use App\Entity\Country;
use App\Entity\Incunable;
use App\Entity\Language;
use App\Entity\Location;
use App\Entity\RelationSubject;
use App\Entity\Title;
use App\Entity\Work;
use App\Service\IncunableImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/relation")
 */
class RelationController extends AbstractController
{
    /**
     * @Route("/", name="relation_home")
     */
    public function index(IncunableImportService $incunableService)
    {
        return $this->render('relation/index.html.twig',
            ['incunables' => $incunableService->findAllIncunables()]);
    }

    /**
     * @Route("/incunable/", name="relation_incunable_list")
     */
    public function incunableList(IncunableImportService $incunableService)
    {
        return $this->render('relation/incunable.list.html.twig',
            ['incunables' => $incunableService->findAllIncunables()]);
    }

    /**
     * @Route("/incunable/{id}/", name="relation_incunable")
     */
    public function incunable(Incunable $incunable)
    {
        return $this->render('relation/incunable.html.twig',
            ['incunable' => $incunable]);
    }

    /**
     * @Route("/work/", name="relation_work_list")
     */
    public function workList(IncunableImportService $incunableService)
    {
        return $this->render('relation/work.list.html.twig',
            ['works' => $incunableService->findAllWorks()]);
    }

    /**
     * @Route("/work/{id}/", name="relation_work")
     */
    public function work(Work $work, IncunableImportService $incunableService)
    {
        return $this->render('relation/work.html.twig', [
            'work' => $work,
        ]);
    }

    /**
     * @Route("/subject/", name="relation_subject_list")
     */
    public function subjectList(IncunableImportService $incunableService)
    {
        return $this->render('relation/subject.list.html.twig',
            ['subjects' => $incunableService->findAllSubjects()]);
    }

    /**
     * @Route("/subject/{id}/", name="relation_subject")
     */
    public function subject(RelationSubject $subject, IncunableImportService $incunableService)
    {
        //$relations = $incunableService->findRelationsBySubject($subject);

        return $this->render('relation/subject.html.twig', [
            'subject' => $subject,
            //'relations' => $relations
        ]);
    }

    /**
     * @Route("/title/", name="relation_title_list")
     */
    public function titleList(IncunableImportService $incunableService)
    {
        return $this->render('relation/title.list.html.twig',
            ['titles' => $incunableService->findAllTitles()]);
    }

    /**
     * @Route("/title/{id}/", name="relation_title")
     */
    public function title(Title $title)
    {
        return $this->render('relation/title.html.twig',
            ['title' => $title]);
    }

    /**
     * @Route("/language/", name="relation_language_list")
     */
    public function languageList(EntityManagerInterface $manager)
    {
        $languages = $manager->getRepository(Language::class)->findAll();

        return $this->render('relation/language.list.html.twig',
            ['languages' => $languages]);
    }

    /**
     * @Route("/language/{id}/", name="relation_language")
     */
    public function language(Language $language)
    {
        return $this->render('relation/language.html.twig',
            ['language' => $language]);
    }

    /**
     * @Route("/location/", name="relation_location_list")
     */
    public function locationList(EntityManagerInterface $manager)
    {
        $locations = $manager->getRepository(Location::class)->findAll();

        return $this->render('relation/location.list.html.twig',
            ['locations' => $locations]);
    }

    /**
     * @Route("/location/{id}/", name="relation_location")
     */
    public function location(Location $location)
    {
        return $this->render('relation/location.html.twig',
            ['location' => $location]);
    }

    /**
     * @Route("/country/", name="relation_country_list")
     */
    public function countryList(EntityManagerInterface $manager)
    {
        $countries = $manager->getRepository(Country::class)->findAll();

        return $this->render('relation/country.list.html.twig',
            ['countries' => $countries]);
    }

    /**
     * @Route("/country/{id}/", name="relation_country")
     */
    public function country(Country $country)
    {
        return $this->render('relation/country.html.twig',
            ['country' => $country]);
    }
}
