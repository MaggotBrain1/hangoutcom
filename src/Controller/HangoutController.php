<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\Status;
use App\Entity\User;
use App\Form\HangoutCancelType;
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
    #[Route('/hangout/detail{id}', name: 'app_hangout_detail',methods: ['GET','POST'])]
    public function detail(HangoutRepository $hangoutRepository, int $id): Response
    {
        $hangout = $hangoutRepository->find($id);
        //TODO getHangouts retourne une collection qui contient la liste des user inscrit à la sortie
        if($hangout){
            $listUsersInHangout = $hangout->getHangouts();
        }else{
            $listUsersInHangout = ['Aucun participant inscrit'];
        }
        return $this->render('hangout/detailHangout.html.twig', [
            'hangout' =>$hangout,
            'listUsersInHangout' =>$listUsersInHangout
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

        if($hangoutForm->isSubmitted() && $hangoutForm->isValid()){
            $hangout->setStartTime($hangoutForm["startTime"]->getData());
            $hangout->setRegisterDateLimit($hangoutForm["registerDateLimit"]->getData());

            if ($request->request->get('submit') === 'published'){
                $hangout->setStatus($statusRepository->find(Status::STATUS_OPENED));
            }else{
                $hangout->setStatus($statusRepository->find(Status::STATUS_CREATED));
            }

            $em->persist($hangout);
            $em->flush();
            $hangoutId = $hangout->getId();
            $this->registerToHangout($hangoutId,$em);
            $this->addFlash('success', 'Hangout successfully added .');
        }

        return $this->render('hangout/createHangout.html.twig', [
            'form' => $hangoutForm->createView(),
        ]);
    }

    #[Route('/hangout/edit{id}', name: 'app_hangout_edit',  requirements: ['id' => '\d+'])]
    public function edit(EntityManagerInterface $em, int $id,Request $request,StatusRepository $statusRepository): Response
    {

        $hangout = $em->getRepository(Hangout::class)->find($id);
        $currentUser = $this->getUser();
        if($hangout->getOrganizer()->getId() != $currentUser->getId())
        {
            return $this->redirectToRoute('app_home');
        }
        $campusOrganizerSite = $currentUser->getCampus();

        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id '.$id
            );
        }
        $hangoutForm = $this->createForm(HangoutFormType::class, $hangout,['defaultCampus'=>$campusOrganizerSite]);
        $hangoutForm->handleRequest($request);

        if($hangoutForm->isSubmitted() && $hangoutForm->isValid()){
            if ($request->request->get('submit') === 'published'){
                $hangout->setStatus($statusRepository->find(Status::STATUS_OPENED));
            }elseif($request->request->get('submit') === 'cancel'){
                $hangout->setStatus($statusRepository->find(Status::STATUS_CANCELED));
            }
            $em->persist($hangout);
            $em->flush();
            $this->addFlash('success', 'Hangout successfully updated .');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('hangout/editHangout.html.twig', [
            'form' => $hangoutForm->createView(),
            'thisIdHangout'=> $id
        ]);
    }

    #[Route('/hangout/delete/{id}', name: 'app_hangout_delete', requirements: ['id' => '\d+'])]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $hangout = $em->getRepository(Hangout::class)->find($id);
        $currentUser = $this->getUser();
        if($hangout->getOrganizer()->getId() != $currentUser->getId()){
            return $this->redirectToRoute('app_home');
        }

        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id '.$id
            );
       }else{
            $em->remove($hangout);
            $em->flush();
        }

       /* return $this->renderForm('hangout/hangoutList.html.twig', [
            'controller_name' => 'HangoutController',
        ]);*/
        return $this->redirectToRoute('app_home');
    }
    #[Route('/hangout/register/{HangoutId}', name: 'app_hangout_register', requirements: ['HangoutId' => '\d+'] )]
    public function registerToHangout( int $HangoutId,EntityManagerInterface $em): Response
    {
        $userId = $this->getUser()->getId();
        $hangout = $em->getRepository(Hangout::class)->find($HangoutId);
        $currentUser = $em->getRepository(User::class)->find($userId);
        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id '.$HangoutId
            );
        }else if($hangout->getHangouts()->count() < $hangout->getMaxOfRegistration() && $hangout->getRegisterDateLimit() > new \DateTime()){
        $relation = $hangout->addHangout($currentUser);
        $em->persist($relation);
        $em->flush();

        }
        return $this->redirectToRoute('app_home');
    }
    #[Route('/hangout/unsubscribe/{HangoutId}', name: 'app_hangout_withdraw', requirements: ['HangoutId' => '\d+'])]
    public function withdrawToHangout(EntityManagerInterface $em, int $HangoutId): Response
    {
        $hangout = $em->getRepository(Hangout::class)->find($HangoutId);
        $userId = $this->getUser()->getId();
        $currentUser = $em->getRepository(User::class)->find($userId);
        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id '.$HangoutId
            );
        }else if($hangout->getOrganizer() != $currentUser && $hangout->getRegisterDateLimit() > New \DateTime()){
            $relation = $hangout->removeHangout($currentUser);
            $em ->persist($relation);
            $em->flush();
        }
        return $this->redirectToRoute('app_home');
    }

    #[Route('/hangout/cancel/{id}', name: 'app_hangout_cancel', requirements: ['id' => '\d+'])]
    public function editStatus(EntityManagerInterface $em, HangoutRepository $hangoutRepository,StatusRepository $statusRepository, int $id,Request $request): Response
    {
        $hangout = $hangoutRepository->find($id);
        $currentUser = $this->getUser();
        if($hangout->getStatus()->getLabel() === 'annulée' || $currentUser !== $hangout->getOrganizer())
        {
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(HangoutCancelType::class,$hangout);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid() && $form->get('reason')->getData() != '')
        {

            $hangout->setStatus($statusRepository->find(Status::STATUS_CANCELED));
            $hangout->setReason($form->get('reason')->getData());
            $em->flush();
            return $this->redirectToRoute('app_home');
        }


        return $this->renderForm('hangout/cancelHangout.html.twig',[
        'form'=>$form,'hangout'=>$hangout
        ]);
    }


}
