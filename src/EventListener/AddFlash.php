<?php

namespace App\EventListener;

use App\Entity\Hangout;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Session\Session;

class AddFlash extends Session
{


    public function postUpdate(Hangout $hangout, LifecycleEventArgs $event )
    {
         $this->getFlashBag()->add('notice','La sorie " '.$hangout->getName().' " à bien été mise à jour');

    }
}




