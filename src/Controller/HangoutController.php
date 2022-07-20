<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Form\HangoutFormType;
use App\Repository\HangoutRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HangoutController extends AbstractController
{
    #[Route('/hangouts', name: 'app_hangout_list', methods: ['GET'])]
    public function list(HangoutRepository $hangoutRepository): Response
    {
       $hangouts = $hangoutRepository->findAll();

        return $this->render('hangout/hangoutList.html.twig', [
            'hangouts' => $hangouts,
        ]);
    }

    #[Route('/hangout/create', name: 'app_hangout_create',methods: ['GET','POST'])]
    public function create(Request $request, EntityManagerInterface $em, StatusRepository $statusRepository): Response
    {
        $hangout = new Hangout();
        $currentUser = $this->getUser();

        $hangout->setOrganizer($currentUser);
        $campusOrganizerSite = $currentUser->getCampus();
        $hangout->setCampusOrganizerSite($campusOrganizerSite);
        $hangoutForm = $this->createForm(HangoutFormType::class, $hangout,['defaultCampus'=>$campusOrganizerSite]);
        $hangoutForm->handleRequest($request);

        if($hangoutForm->isSubmitted() && $hangoutForm->isSubmitted()){
            $hangout->setStartTime($hangoutForm["startTime"]->getData());
            $hangout->setRegisterDateLimit($hangoutForm["registerDateLimit"]->getData());
            $hangout->setStatus($statusRepository->find(1));
            $em->persist($hangout);
            $em->flush();
            $this->addFlash('success', 'Hangout successfully added .');
        }

        return $this->render('hangout/createHangout.html.twig', [
            'form' => $hangoutForm->createView(),
        ]);
    }

    #[Route('/hangout/publish{id}', name: 'app_hangout_publish',methods: ['GET','POST'])]
    public function publish(): Response
    {
        return $this->render('hangout/hangoutList.html.twig', [
            'controller_name' => 'HangoutController',
        ]);
    }

    #[Route('/hangout/edit{id}', name: 'app_hangout_edit')]
    public function edit(EntityManagerInterface $em, int $id,Request $request): Response
    {
        $hangout = $em->getRepository(Hangout::class)->find($id);
        $currentUser = $this->getUser();
        $campusOrganizerSite = $currentUser->getCampus();

        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id '.$id
            );
        }
        $hangoutForm = $this->createForm(HangoutFormType::class, $hangout,['defaultCampus'=>$campusOrganizerSite]);
        $hangoutForm->handleRequest($request);

        if($hangoutForm->isSubmitted() && $hangoutForm->isSubmitted()){
            $em->persist($hangout);
            $em->flush();
            $this->addFlash('success', 'Hangout successfully updated .');
        }

        return $this->render('hangout/createHangout.html.twig', [
            'form' => $hangoutForm->createView(),
        ]);
    }

    #[Route('/hangout/delete{id}', name: 'app_hangout_delete' ,methods: ['GET','DELETE'])]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $hangout = $em->getRepository(Hangout::class)->find($id);

        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id '.$id
            );
       }else{
            $em->remove($hangout);
            $em->flush();
        }

        return $this->render('hangout/hangoutList.html.twig', [
            'controller_name' => 'HangoutController',
        ]);
    }

}
