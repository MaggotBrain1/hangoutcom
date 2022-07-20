<?php

namespace App\Controller;

use App\Form\EditProfileType;
use App\Repository\UserRepository;
use Cassandra\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/profile/{id}', name: 'app_profile')]
    public function profile( int $id, UserRepository $userRepository,Request $request,EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if(!$user){
            throw $this->createNotFoundException("Oh no !!");
        }
        $form = $this->createForm(EditProfileType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            if( $form->get('confirmationPassword') === '' && $form->get('password') === $user->getPassword()) {

            }
            else if((($form->get('confirmationPassword') !== $form->get('password')) && $form->get('confirmationPassword') !== '') ){
                throw $this->createNotFoundException("Passwords must be equals");
            }

            $user = $form->getData();
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->renderForm("user/profile.html.twig",
            ['user' => $user,'editUserForm'=>$form
            ]);
    }
}
