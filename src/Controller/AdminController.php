<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Hangout;
use App\Repository\CampusRepository;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/manage/city', name: 'app_admin_manage_city')]
    public function manageCity(CityRepository $cityRepository, EntityManagerInterface $entityManager): Response
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

        return $this->render('admin/manageCity.html.twig', [
            'cities' => $cities,

        ]);
    }

    #[Route('/admin/manage/campus', name: 'app_admin_manage_campus')]
    public function manageCampus(CampusRepository $campusRepository, EntityManagerInterface $entityManager): Response
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

        return $this->render('admin/manageCampus.html.twig', [
            'campus' => $campus,
        ]);
    }
}
