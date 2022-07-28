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
    #[Route('/campus/edit/{id}/{nom}', name: 'app_campus_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(EntityManagerInterface $em, CampusRepository $campusRepository, int $id,string $nom)
    {
        $campus = $campusRepository->find($id);
        if($nom != '')
        {
            $campus->setName($nom);
            $this->addFlash('sucess','Modification Enregistrée');
        }
        else {
            $this->addFlash('fail','Le nom du campus ne peut pas être vide');
        }
        $em->flush();
        return $this->redirectToRoute('app_admin_manage_campus');
    }

    #[Route('/campus/delete/{id}', name: 'app_campus_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(EntityManagerInterface $em, CampusRepository $campusRepository, int $id)
    {
        $campus = $campusRepository->find($id);
        $em->remove($campus);
        $em->flush();
        return $this->redirectToRoute('app_admin_manage_campus');
    }
}
