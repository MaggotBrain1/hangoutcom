<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityFormType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CityController extends AbstractController
{
    #[Route('/city/edit/{id}/{ville}/{cp}', name: 'app_city_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(EntityManagerInterface $em, CityRepository $cityRepository, int $id,string $ville,string $cp)
    {
        $cpt = 0;
        $city = $cityRepository->find($id);
        if($city != '')
        {
            $cpt++;
            $city->setName($ville);
        }
        if(strlen($cp) == 5 && preg_match('^\d{5}^',$cp))
        {
            $cpt++;
            $city->setZipCode($cp);

        }
        if($cpt === 2)
        {
            $this->addFlash('sucess','Modifications enregistrées');
        }
        else{
            $this->addFlash('fail','Le code postal doit faire 5 chiffres et la ville doit être renseignée');
        }
        $em->flush();
        return $this->redirectToRoute('app_admin_manage_city');


    }
    #[Route('/city/delete/{id}', name: 'app_city_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(EntityManagerInterface $em, CityRepository $cityRepository, int $id)
    {
        $city = $cityRepository->find($id);
        $em->remove($city);
        $em->flush();
        return $this->redirectToRoute('app_admin_manage_city');


    }
}
