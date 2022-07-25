<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityFormType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CityController extends AbstractController
{
    #[Route('/city', name: 'app_city')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(EntityManagerInterface $em,Request $request, CityRepository $cityRepository): Response
    {
        $city = new City();
        $cityForm = $this->createForm(CityFormType::class,$city);
        $cityForm->handleRequest($request);

        if($cityForm->isSubmitted() && $cityForm->isValid()){
            $em->persist($city);
            $em->flush();
        }

        return $this->render('city/city.html.twig', [
            'citys' => $cityRepository->findAll(),
            'form'=> $cityForm->createView()
        ]);
    }
    #[Route('/city/delete/{id}', name: 'app_city_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(EntityManagerInterface $em, CityRepository $cityRepository, int $id)
    {
        $city = $cityRepository->find($id);
        $em->remove($city);
        $em->flush();
        return $this->redirectToRoute('app_city');


    }
}
