<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/manage/city', name: 'app_admin_manage_city')]
    public function manageCity(): Response
    {
        return $this->render('admin/manageCity.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/manage/campus', name: 'app_admin_manage_campus')]
    public function manageCampus(): Response
    {
        return $this->render('admin/manageCampus.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
