<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\Status;
use App\Entity\User;
use App\Form\FilterType;
use App\Form\HangoutCancelType;
use App\Form\HangoutFormType;
use App\Repository\HangoutRepository;
use App\Repository\PlaceRepository;
use App\Repository\StatusRepository;
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
    public function index(HangoutRepository $hangoutRepository, Request $request,EntityManagerInterface $em, StatusRepository $statusRepository): Response
    {
        $user = $this->getUser();

        if($user == null)
        {
            $this->addFlash('fail','Vous devez être connecté pour voir les sorties!');
            return $this->redirectToRoute('app_login');
        }


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
            $this->updateStatusOfHangouts($hangouts,$statusRepository,$em);
        }

        return $this->render('hangout/hangoutList.html.twig', [
            'hangouts' => $hangouts,
            'filterForm' => $filterForm->createView(),
            'subscribe'=>$hangoutRepository,
        ]);
    }
    #[Route('/hangout/detail{id}', name: 'app_hangout_detail', methods: ['GET', 'POST'])]
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
        $currentUser = $this->getUser();

        $hangout->setOrganizer($currentUser);
        $campusOrganizerSite = $currentUser->getCampus();
        $hangout->setCampusOrganizerSite($campusOrganizerSite);
        $hangoutForm = $this->createForm(HangoutFormType::class, $hangout, ['defaultCampus' => $campusOrganizerSite]);
        $hangoutForm->handleRequest($request);

        if ($hangoutForm->isSubmitted() && $hangoutForm->isValid()) {
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
        ]);
    }

    #[Route('/hangout/edit{id}', name: 'app_hangout_edit', requirements: ['id' => '\d+'])]
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
    public function editStatus(EntityManagerInterface $em, HangoutRepository $hangoutRepository, StatusRepository $statusRepository, int $id, Request $request): Response
    {
        $hangout = $hangoutRepository->find($id);
        $currentUser = $this->getUser();
        if ($hangout->getStatus()->getLabel() === 'annulée' || $currentUser !== $hangout->getOrganizer()) {
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(HangoutCancelType::class, $hangout);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $form->get('reason')->getData() != '') {

            $hangout->setStatus($statusRepository->find(Status::STATUS_CANCELED));
            $hangout->setReason($form->get('reason')->getData());
            $em->flush();
            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('hangout/cancelHangout.html.twig', [
            'form' => $form, 'hangout' => $hangout
        ]);
    }

    public function updateStatusOfHangouts($allHangouts, StatusRepository $statusRepository, EntityManagerInterface $em){

        foreach ($allHangouts as $hg){
            $startDate = $hg->getStartTime();
            $now = (new \DateTime("now"));
            dump($now);
            $interval = $startDate->diff($hg->getDuration());
            dump($interval); // TODO modifier car interval actuel à 70 piges ... Pb conversion Timestamp
            $endDate = $startDate->add($interval);
            $oneMothMore = $startDate->modify('+1 month');

            dump($now);
           /* // SECTION TEST
            dump($startDate == $now);
            dump($now >= date('Y-m-d H:i', strtotime($startDate. ' + 1 day')));
                 dump($now >= date('Y-m-d H:i', strtotime($startDate. ' + 1 months')) );
                      dump($now >= $hg->getRegisterDateLimit()->format('Y-m-d H:i'));
            dump($hg->getName());
            // FIN SECTION TEST*/

            // To switch case
            // CAS 1 : now est >= date && < date + durée sortie STATUT EN COURS
            // CAS 2 : now >= date + durée sortie && < date + 1 mois  => STATUT PASSé
            // CAS 3 : now > date + 1 mois => STATUT ACHIVE

            // TODO Vérifier que les Status se mettent à jour de façon cohérente aux règles

            switch($hg->getStatus()->getId()){
                case ($startDate <= $now && $now < $endDate) :
                    if ($hg->getStatus()->getId() != Status::STATUS_IN_PROGRESS) {$hg->setStatus($statusRepository->find(Status::STATUS_IN_PROGRESS));}
                    break;
                case ($now > $endDate && $now < $endDate) :
                    if($hg->getStatus()->getId() != Status::STATUS_PAST)  {$hg->setStatus($statusRepository->find(Status::STATUS_PAST));}
                    break;
                //date('Y-m-d H:i', strtotime($startDate. ' + 1 day')) <= date('Y-m-d H:i', strtotime($startDate. ' + 1 month')) ) :
                case ($now > $oneMothMore):
                    if ($hg->getStatus()->getId() != Status::STATUS_ARCHIVED) {$hg->setStatus($statusRepository->find(Status::STATUS_ARCHIVED));}
                    break;
                default:
            }

            /*$today = date("Y-m-d H:i");
            if ($startDate == $today) {
                $hg->setStatus($statusRepository->find(Status::STATUS_IN_PROGRESS));
            }
            if($today >= date('Y-m-d H:i', strtotime($startDate. ' + 1 day')) &&
                date('Y-m-d H:i', strtotime($startDate. ' + 1 day')) <= date('Y-m-d H:i', strtotime($startDate. ' + 1 month')) ){
                $hg->setStatus($statusRepository->find(Status::STATUS_PAST));
            }
            if($today >= date('Y-m-d H:i', strtotime($startDate. ' + 1 months')) ){
                $hg->setStatus($statusRepository->find(Status::STATUS_ARCHIVED));

            }
            if($today >= $hg->getRegisterDateLimit()->format('Y-m-d H:i')){
                $hg->setStatus($statusRepository->find(Status::STATUS_CLOSED));
            }*/

            $em->persist($hg);
            $em->flush();
        }

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

        // e.g
        // [{"id":"3","name":"Treasure Island"},{"id":"4","name":"Presidio of San Francisco"}]
    }

/*    #[Route('/hangout/placeByCity', name: 'testToto', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function testToto(Request $request, EntityManagerInterface $em, StatusRepository $statusRepository): Response
    {
        $hangout = new Hangout();
        $currentUser = $this->getUser();

        $hangout->setOrganizer($currentUser);
        $campusOrganizerSite = $currentUser->getCampus();
        $hangout->setCampusOrganizerSite($campusOrganizerSite);
        $hangoutForm = $this->createForm(HangoutFullFormType::class, $hangout, ['defaultCampus' => $campusOrganizerSite]);
        $hangoutForm->handleRequest($request);

        if ($hangoutForm->isSubmitted() && $hangoutForm->isValid()) {
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

        return $this->render('hangout/editHangoutFullForm.html.twig', [
            'form' => $hangoutForm->createView(),
        ]);
    }*/

}
