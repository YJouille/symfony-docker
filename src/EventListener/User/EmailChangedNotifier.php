<?php

namespace App\EventListener\User;

use App\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class EmailChangedNotifier
{
    public function preUpdate(User $user, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField("email")) {
            //TODO: Call service to send confirmation password

            $user->setEmailVerified(false);
        }
    }
}
