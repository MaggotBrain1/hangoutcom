<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusFormType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CampusController extends AbstractController
{
    #[Route('/campus', name: 'app_campus')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(EntityManagerInterface $em,Request $request,CampusRepository $campusRepository): Response
    {
        $campus = new Campus();
        $campusForm = $this->createForm(CampusFormType::class,$campus);
        $campusForm->handleRequest($request);

        if($campusForm->isSubmitted() && $campusForm->isValid()){
            $em->persist($campus);
            $em->flush();
        }
        return $this->render('campus/index.html.twig', [
            'form' => $campusForm->createView(),
            'campus'=> $campusRepository->findAll()
        ]);
    }

    #[Route('/campus/delete/{id}', name: 'app_campus_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(EntityManagerInterface $em, CampusRepository $campusRepository, int $id)
    {
        $campus = $campusRepository->find($id);
        $em->remove($campus);
        $em->flush();
        return $this->redirectToRoute('app_campus');
    }
}
