<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\Place;
use App\Entity\Status;
use App\Entity\User;
use App\Form\FilterType;
use App\Form\HangoutCancelType;
use App\Form\HangoutFormType;
use App\Form\PlaceFormType;
use App\Repository\HangoutRepository;
use App\Repository\PlaceRepository;
use App\Repository\StatusRepository;
use App\services\MailService;
use App\services\UpdateStatusHangouts;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HangoutController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[IsGranted('ROLE_USER')]
    public function index(HangoutRepository $hangoutRepository, Request $request,EntityManagerInterface $em, StatusRepository $statusRepository,UpdateStatusHangouts $updateStatusHangouts,
    MailService $mailer): Response
    {
        //$mailer->send();
        $user = $this->getUser();
        if($user == null)
        {
            $this->addFlash('fail','Vous devez être connecté pour voir les sorties!');
            return $this->redirectToRoute('app_login');
        }
    /*    //TODO fonctionnel mais pas propre, trouver un moyen de config User Admin symfony (voir doc)
        $token = new UsernamePasswordToken($user,'none',$user->getRoles());
        if($accessDecisionManager->decide($token, (array)'ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin');
        }*/



        //à sa connection on affiche à l'user un message l'avertissant de l'annulation
        //d'une sortie à la quelle il était inscrit
        $hangoutCanceled = $hangoutRepository->findByStatusCanceled($user->getId());
        if ($hangoutCanceled && $user->getLastLogin()->format('Y-m-d H:i') == date('Y-m-d H:i')){
            foreach ($hangoutCanceled as $hangout){
                $this->addFlash('notice', 'La sortie '.$hangout->getName().' à été annulée');
            }
        }

        $hangout = new Hangout();
        $filterForm = $this->createForm(FilterType::class,$hangout);
        $filterForm->handleRequest($request);

        if($filterForm->isSubmitted() && $filterForm->isValid()){
            $campus = $filterForm["campusOrganizerSite"]->getData();
            $name = $filterForm["name"]->getData();
            $startDate = $filterForm["startDate"]->getData();
            $endDate= $filterForm["endDate"]->getData();
            $imOrginizer= $filterForm["isOrganizer"]->getData();
            $imIn= $filterForm["isSubscribe"]->getData();
            $imNotIn= $filterForm["isNotSuscribe"]->getData();
            $pastHangout= $filterForm["isHangoutPassed"]->getData();
            $hangouts = $hangoutRepository->findByFilter($campus,$name,$startDate,$endDate,$imOrginizer,$imIn,$imNotIn,$pastHangout,$user);
        }else{
            $hangouts = $hangoutRepository->findHangoutAvaible();
            $updateStatusHangouts->updateStatusOfHangouts($hangouts,$statusRepository,$em);
        }

        return $this->render('hangout/hangoutList.html.twig', [
            'hangouts' => $hangouts,
            'filterForm' => $filterForm->createView(),
        ]);
    }
    #[Route('/hangout/detail/{id}', name: 'app_hangout_detail', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function detail(HangoutRepository $hangoutRepository, int $id): Response
    {
        $hangout = $hangoutRepository->find($id);
        if ($hangout){
            $listUsersInHangout = $hangout->getHangouts();
        } else {
            $listUsersInHangout = ['Aucun participant inscrit'];
        }
        return $this->render('hangout/detailHangout.html.twig', [
            'hangout' => $hangout,
            'listUsersInHangout' => $listUsersInHangout
        ]);
    }

    #[Route('/hangout/create', name: 'app_hangout_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $em, StatusRepository $statusRepository): Response
    {
        $hangout = new Hangout();
        $place = new Place();

        $currentUser = $this->getUser();

        $hangout->setOrganizer($currentUser);

        $campusOrganizerSite = $currentUser->getCampus();

        $hangout->setCampusOrganizerSite($campusOrganizerSite);

        $hangoutForm = $this->createForm(HangoutFormType::class, $hangout, ['defaultCampus' => $campusOrganizerSite]);
        $placeForm   = $this->createForm(PlaceFormType::class, $place);

        $placeForm->handleRequest($request);
        $hangoutForm->handleRequest($request);




        if ($hangoutForm->isSubmitted() && $hangoutForm->isValid() ||$placeForm->isSubmitted() && $placeForm->isValid()) {
            $em->persist($place);
            $em->flush();
            $hangout->setStartTime($hangoutForm["startTime"]->getData());
            $hangout->setRegisterDateLimit($hangoutForm["registerDateLimit"]->getData());

            if ($request->request->get('submit') === 'published') {
                $hangout->setStatus($statusRepository->find(Status::STATUS_OPENED));
            } else {
                $hangout->setStatus($statusRepository->find(Status::STATUS_CREATED));
            }

            $em->persist($hangout);
            $em->flush();

            $hangoutId = $hangout->getId();
            $this->registerToHangout($hangoutId, $em);
            $this->addFlash('success', 'Hangout successfully added .');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('hangout/createHangout.html.twig', [
            'form' => $hangoutForm->createView(),
            'formPlace' => $placeForm->createView()
        ]);
    }

    #[Route('/hangout/edit/{id}', name: 'app_hangout_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function edit(EntityManagerInterface $em, int $id, Request $request, StatusRepository $statusRepository): Response
    {
        $hangout = $em->getRepository(Hangout::class)->find($id);
        $currentUser = $this->getUser();

        if ($hangout->getOrganizer()->getId() != $currentUser->getId()) {
            return $this->redirectToRoute('app_home');
        }
        $campusOrganizerSite = $currentUser->getCampus();

        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id ' . $id
            );
        }
        $hangoutForm = $this->createForm(HangoutFormType::class, $hangout, ['defaultCampus' => $campusOrganizerSite]);
        $hangoutForm->handleRequest($request);

        if ($hangoutForm->isSubmitted() && $hangoutForm->isValid()) {
            if ($request->request->get('submit') === 'published') {
                $hangout->setStatus($statusRepository->find(Status::STATUS_OPENED));
                $this->registerToHangout($hangout->getId(), $em);
            } elseif ($request->request->get('submit') === 'cancel') {
                $hangout->setStatus($statusRepository->find(Status::STATUS_CANCELED));
            }

            $em->persist($hangout);
            $em->flush();
            $this->addFlash('success', 'Hangout successfully updated .');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('hangout/editHangout.html.twig', [
            'form' => $hangoutForm->createView(),
            'thisIdHangout' => $id
        ]);
    }

    #[Route('/hangout/delete/{id}', name: 'app_hangout_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $hangout = $em->getRepository(Hangout::class)->find($id);
        $currentUser = $this->getUser();
        if ($hangout->getOrganizer()->getId() != $currentUser->getId()) {
            return $this->redirectToRoute('app_home');
        }

        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id ' . $id
            );
        } else {
            $em->remove($hangout);
            $em->flush();
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/hangout/register/{HangoutId}', name: 'app_hangout_register', requirements: ['HangoutId' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function registerToHangout(int $HangoutId, EntityManagerInterface $em): Response
    {
        $userId = $this->getUser()->getId();
        $hangout = $em->getRepository(Hangout::class)->find($HangoutId);
        $currentUser = $em->getRepository(User::class)->find($userId);
        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id ' . $HangoutId
            );
        } else if ($hangout->getHangouts()->count() < $hangout->getMaxOfRegistration() && $hangout->getRegisterDateLimit() > new \DateTime()) {
            if ($hangout->getStatus()->getId() == Status::STATUS_OPENED ){
                $relation = $hangout->addHangout($currentUser);
                $em->persist($relation);
                $em->flush();
            }else{
                if ($hangout->getOrganizer() == !$this->getUser()) {
                    $this->addFlash('fail', 'Désolé l\'inscription pour la sortie' . $hangout->getName() . ' est indisponible .');
                }
            }


        }
        return $this->redirectToRoute('app_home');
    }

    #[Route('/hangout/unsubscribe/{HangoutId}', name: 'app_hangout_withdraw', requirements: ['HangoutId' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function withdrawToHangout(EntityManagerInterface $em, int $HangoutId): Response
    {
        $hangout = $em->getRepository(Hangout::class)->find($HangoutId);
        $userId = $this->getUser()->getId();
        $currentUser = $em->getRepository(User::class)->find($userId);
        if (!$hangout) {
            throw $this->createNotFoundException(
                'No hangout found for id ' . $HangoutId
            );
        } else if ($hangout->getOrganizer() != $currentUser && $hangout->getRegisterDateLimit() > new \DateTime()) {
            $relation = $hangout->removeHangout($currentUser);
            $em->persist($relation);
            $em->flush();
        }
        return $this->redirectToRoute('app_home');
    }

    #[Route('/hangout/cancel/{id}', name: 'app_hangout_cancel', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function editStatus(EntityManagerInterface $em, HangoutRepository $hangoutRepository, StatusRepository $statusRepository, int $id, Request $request, MailService $mailer): Response
    {
        $hangout = $hangoutRepository->find($id);
        $currentUser = $this->getUser();
        if ($hangout->getStatus()->getId() === Status::STATUS_CANCELED && $currentUser === $hangout->getOrganizer()  ) {
            $this->addFlash('fail','La sortie est déjà annulée!');
            return $this->redirectToRoute('app_home');
        }
        else if($currentUser !== $hangout->getOrganizer()){
            $this->addFlash('fail','Vous n\'avez pas les droits pour accéder à cette page!');
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(HangoutCancelType::class, $hangout);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $form->get('reason')->getData() != '') {

            $hangout->setStatus($statusRepository->find(Status::STATUS_CANCELED));
            $hangout->setReason($form->get('reason')->getData());
            $em->flush();
            $this->addFlash('fail','La sortie à bien été annulée.');
          /*  $userInHangout = $hangout->getHangouts();
            foreach ($userInHangout as $user ){
            }*/
            $mailer->send();

            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('hangout/cancelHangout.html.twig', [
            'form' => $form, 'hangout' => $hangout
        ]);
    }

    public function listOfPlaceCityAction(Request $request, PlaceRepository $placeRepository,EntityManagerInterface $em )
    {

        // Search the neighborhoods that belongs to the city with the given id as GET parameter "cityid"
        $places = $placeRepository->createQueryBuilder("p")
            ->where("p.city = :cityid")
            ->setParameter("cityid", $request->query->get("cityid"))
            ->getQuery()
            ->getResult();

        // Serialize into an array the data that we need, in this case only name and id
        // Note: you can use a serializer as well, for explanation purposes, we'll do it manually
        $responseArray = array();
        foreach($places as $place){
            $responseArray[] = array(
                "id" => $place->getId(),
                "name" => $place->getName()
            );
        }

        // Return array with structure of the neighborhoods of the providen city id
        return new JsonResponse($responseArray);

    }


    /**
     * Returns a JSON string with the neighborhoods of the City with the providen id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/PlacesByCity', name: 'app_places_by_city')]
    public function PlacesByCity(Request $request,PlaceRepository $placeRepository)
    {
        // Get Entity manager and repository


        // Search the neighborhoods that belongs to the city with the given id as GET parameter "cityid"
        $places = $placeRepository->createQueryBuilder("q")
            ->where("q.city = :cityid")
            ->setParameter("cityid", $request->query->get("cityid"))
            ->getQuery()
            ->getResult();

        // Serialize into an array the data that we need, in this case only name and id
        // Note: you can use a serializer as well, for explanation purposes, we'll do it manually
        $responseArray = array();
        foreach($places as $place){
            $responseArray[] = array(
                "id" => $place->getId(),
                "name" => $place->getName()
            );
        }

        // Return array with structure of the neighborhoods of the providen city id
        return new JsonResponse($responseArray);

    }



}
