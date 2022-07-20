<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CrashTestController extends AbstractController
{
    #[Route('/crash/test', name: 'app_crash_test')]
    public function index(EntityManagerInterface $entityManager,UserRepository $repository): Response
    {
        $user = new User();
        $userID = $this->getUser()->getID();
        $user = $repository->find($userID);
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->render('crash_test/index.html.twig', [
            'controller_name' => 'CrashTestController',
        ]);
    }
}
