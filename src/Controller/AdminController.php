<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\City;
use App\Form\CampusFormType;
use App\Form\CityType;
use App\Repository\CampusRepository;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/manage/city', name: 'app_admin_manage_city')]
    public function manageCity(CityRepository $cityRepository, EntityManagerInterface $entityManager, Request $request): Response
    {

        $user = $this->getUser();
        if(!$user)
        {
            $this->addFlash('fail', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        if(!$this->isGranted("ROLE_ADMIN"))
        {
            $this->addFlash('fail', 'Vous devez être admin pour consulter cette page');
            return $this->redirectToRoute('app_login');
        }
        $cities = $entityManager->getRepository(City::class)->findAll();

        $city = new City();


        $formCityType = $this->createForm(CityType::class, $city);
        $formCityType->handleRequest($request);
        if($formCityType->isSubmitted() && $formCityType->isValid()){
            // TODO : TRAITEMENT DU FORMULAIRE
        }

        return $this->render('admin/manageCity.html.twig', [
            'form_city'=> $formCityType->createView(),
            'cities' => $cities,

        ]);
    }

    #[Route('/admin/manage/campus', name: 'app_admin_manage_campus')]
    public function manageCampus(CampusRepository $campusRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        if(!$user)
        {
            $this->addFlash('fail', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        if(!$this->isGranted("ROLE_ADMIN"))
        {
            $this->addFlash('fail', 'Vous devez être admin pour consulter cette page');
            return $this->redirectToRoute('app_login');
        }
        $campus = $entityManager->getRepository(Campus::class)->findAll();

        $newCampus = new Campus();

        $formCampusType = $this->createForm(CampusFormType::class, $newCampus);
        $formCampusType->handleRequest($request);

        if($formCampusType->isSubmitted() && $formCampusType->isValid()){
            // TODO : TRAITEMENT DU FORMULAIRE
        }

        return $this->render('admin/manageCampus.html.twig', [
            'form_campus' => $formCampusType->createView(),
            'campus' => $campus,

        ]);
    }
}
