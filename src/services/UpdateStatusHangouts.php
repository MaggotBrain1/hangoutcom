<?php

namespace App\services;

use App\Entity\Status;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateStatusHangouts
{
    public function updateStatusOfHangouts($allHangouts, StatusRepository $statusRepository, EntityManagerInterface $em){
        foreach ($allHangouts as $hg){
            $hangoutClone = clone($hg);
            $startDate = $hg->getStartTime();
            $now = (new \DateTime("now")); // renvoi la date/h/m/s du jour
            $durationH = $hg->getDuration()->format('H');// on récupere les minutes de la durée
            $durationM = $hg->getDuration()->format('i');//on récupere les heures de la durée
            $endDate = clone($startDate);// on clone $startDate pour que les modification ne l'affecte pas directement.
            $endDate->modify("+{$durationM} minutes");// on ajoute les minutes à la date de début
            $endDate->modify("+{$durationH} hour");// on ajoute les heures à la date de départ
            // $endDate === la date et heures de la sortie additionnée au temps de durée de la sorite
            $oneMothMore = clone($startDate);
            $oneMothMore->modify('+1 month');

            switch($hg->getStatus()->getId()){
                case ($startDate <= $now && $now < $endDate) :
                    if ($hg->getStatus()->getId() != Status::STATUS_IN_PROGRESS){
                        $hg->setStatus($statusRepository->find(Status::STATUS_IN_PROGRESS));
                    }
                    break;
                case ($now > $endDate && $now < $oneMothMore ) :
                    if($hg->getStatus()->getId() != Status::STATUS_PAST){
                        $hg->setStatus($statusRepository->find(Status::STATUS_PAST));
                    }
                    break;
                case ($now > $oneMothMore):
                    if ($hg->getStatus()->getId() != Status::STATUS_ARCHIVED) {
                        $hg->setStatus($statusRepository->find(Status::STATUS_ARCHIVED));
                    }
                    break;
            }

            if ($hangoutClone->getStatus()->getId() != $hg->getStatus()->getId()){
                $em->persist($hg);
                $em->flush();
            }
        }
    }

}