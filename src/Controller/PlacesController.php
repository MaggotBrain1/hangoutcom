<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlacesFormType;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlacesController extends AbstractController
{
    #[Route('/places', name: 'app_places')]
    public function index(EntityManagerInterface $em,Request $request,PlaceRepository $placeRepository): Response
    {
        $place = new Place();
        $placeForm = $this->createForm(PlacesFormType::class,$place);
        $placeForm->handleRequest($request);

        if($placeForm->isSubmitted() && $placeForm->isValid()){
            $em->persist($place);
            $em->flush();
        }

        return $this->render('places/index.html.twig', [
            'places' => $placeRepository->findAll(),
            'form'=> $placeForm->createView()
        ]);
    }

    #[Route('/places/delete/{id}', name: 'app_place_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(EntityManagerInterface $em, PlaceRepository $placeRepository, int $id)
    {
        $place = $placeRepository->find($id);
        $em->remove($place);
        $em->flush();
        return $this->redirectToRoute('app_campus');


    }
}
