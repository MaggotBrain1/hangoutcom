<?php

namespace App\Controller;

use App\Form\EditProfileType;
use App\Repository\UserRepository;
use Cassandra\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->redirectToRoute('app_login');
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');

    }

    #[Route(path: '/profile', name: 'app_profile')]
    public function profile(  UserRepository $userRepository,Request $request,EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher,SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if(!$user){
            throw $this->createNotFoundException("Oh no !!");
        }
        $form = $this->createForm(EditProfileType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {

            $user = $form->getData();
            if($form->get('plainPassword')->getData() != '' )
            {
                $hashedPassword = $passwordHasher->hashPassword($user,$form->get('plainPassword')->getData());
                $user->setPassword($hashedPassword);
            }
            $imageFile = $form->get('image')->getData();
            if($imageFile)
            {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(),PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFilename);
                $newFilename = $safeFileName.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                }
                catch (FileException $exception){

                }
                $user->setImage($newFilename);
            }
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->renderForm("user/profile.html.twig",
            ['user' => $user,'editUserForm'=>$form
            ]);
    }
    #[Route(path: '/user/{id}', name: 'app_user')]
public function profileUsers(int $id,UserRepository $userRepository)
    {
        $user = $userRepository->find($id);
        $thisUser = $this->getUser();
        if($user!= null)
        {
            if($user === $thisUser)
            {
                return $this->redirectToRoute('app_profile');
            }
        }
        else{
            throw $this->createNotFoundException("utilisateur introuvable");
        }
        return $this->render("otherUser/profile.html.twig",
        ['user'=>$user]);
    }
}
