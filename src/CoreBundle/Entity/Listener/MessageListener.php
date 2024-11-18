<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Entity\Listener;

use App\Message\Domain\Entity\Message;
use App\Message\Domain\Entity\MessageRelUser;
use Doctrine\ORM\Event\PostLoadEventArgs;

class MessageListener
{
    public function postLoad(Message $message, PostLoadEventArgs $args): void
    {
        $om = $args->getObjectManager();
        $messageRelUserRepo = $om->getRepository(MessageRelUser::class);

        $softDeleteable = $om->getFilters()->enable('softdeleteable');

        $softDeleteable->disableForEntity(MessageRelUser::class);

        $message->setReceiversFromArray(
            $messageRelUserRepo->findBy([
                'message' => $message,
            ])
        );

        $softDeleteable->enableForEntity(Message::class);
    }
}
