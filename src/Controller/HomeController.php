<?php

namespace App\Controller;

use App\Repository\HangoutRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(HangoutRepository $hangoutRepository): Response
    {
        $hangouts = $hangoutRepository->findAll();

        return $this->render('hangout/hangoutList.html.twig', [
            'hangouts' => $hangouts,
        ]);
    }
}
