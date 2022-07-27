<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\City;
use App\Form\CampusFilteredFormType;
use App\Form\CampusFormType;
use App\Form\CityFilteredFormType;
use App\Form\CityFormType;
use App\Form\CityType;
use App\Repository\CampusRepository;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_USER')]
    public function HomeAdmin(): Response
    {

        return $this->render('admin/homeAdmin.html.twig', [

        ]);
    }

    #[Route('/admin/manage/city', name: 'app_admin_manage_city')]
    #[IsGranted('ROLE_USER')]
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
        //initialisation de filteredCities pour pas d'erreurs
        $filteredCities = '';
        $city = new City();
        $cityBis = new City();
        $formFilteredCityType = $this->createForm(CityFilteredFormType::class,$cityBis);
        $formFilteredCityType->handleRequest($request);
        $formCityType = $this->createForm(CityFormType::class, $city);
        $formCityType->handleRequest($request);
        if($formCityType->isSubmitted() && $formCityType->isValid()){
            $city = $formCityType->getData();
            $entityManager->persist($city);
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_manage_city');
        }
        if($formFilteredCityType->isSubmitted() && $formFilteredCityType->isValid())
        {
            $name = $formFilteredCityType->get('name')->getData();
            $filteredCities = $cityRepository->getFilteredCity($name);
        }
        return $this->render('admin/manageCity.html.twig', [
            'form_city'=> $formCityType->createView(),
            'form_filtered'=> $formFilteredCityType->createView(),
            'filteredCities'=>$filteredCities,
            'cities' => $cities,

        ]);
    }

    #[Route('/admin/manage/campus', name: 'app_admin_manage_campus')]
    #[IsGranted('ROLE_USER')]
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
        $newCampusBis = new Campus();
        $filteredCampus = '';
        $formCampusType = $this->createForm(CampusFormType::class, $newCampus);
        $formCampusType->handleRequest($request);
        $formFilterCampus = $this->createForm(CampusFilteredFormType::class,$newCampusBis);

        $formFilterCampus->handleRequest($request);

        if($formCampusType->isSubmitted() && $formCampusType->isValid()){
            $campus = $formCampusType->getData();
            $entityManager->persist($campus);
            $entityManager->flush();
            return $this->redirectToRoute('app_admin_manage_campus');
        }
        if($formFilterCampus->isSubmitted() && $formFilterCampus->isValid()){
            $filteredCampus = $campusRepository->getFilteredCampusByName($formFilterCampus->get('name')->getData());
        }

        return $this->render('admin/manageCampus.html.twig', [
            'form_campus' => $formCampusType->createView(),
            'form_filtered_campus'=> $formFilterCampus->createView(),
            'campus' => $campus,
            'filteredCampus'=>$filteredCampus

        ]);
    }
}
