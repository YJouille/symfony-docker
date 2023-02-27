<?php

namespace App\EventListener;

use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Handler\UploadHandler;

class VichFileUpdateListener
{
    public function __construct(
        private readonly UploadHandler $uploadHandler
    )
    {
    }

    public function onVichUploaderPreUpload(Event $event)
    {
        $object = $event->getObject();
        $mapping = $event->getMapping();

        // $this->uploadHandler->remove($object, "avatarFile");
    }
}
