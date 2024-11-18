<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\EventListener;

use App\CoreBundle\Entity\User\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Adds objects into the session like the old global.inc.
 */
class OnlineListener
{
    protected TokenStorageInterface $context;
    protected EntityManagerInterface $em;

    public function __construct(TokenStorageInterface $context, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->context = $context;
    }

    /**
     * Update the user "lastActivity" on each request.
     */
    public function __invoke(ControllerEvent $event): void
    {
        /*  Here we are checking that the current request is a "MASTER_REQUEST",
            and ignore any subrequest in the process (for example when doing a
            render() in a twig template)*/

        if ($event->getRequestType() !== HttpKernel::MAIN_REQUEST) {
            return;
        }

        // We are checking a token authentication is available before using the User
        if ($this->context->getToken() !== null) {
            $user = $this->context->getToken()->getUser();

            /* We are using a delay during which the user will be considered as
            still active, in order to avoid too much UPDATE in the database*/
            $delay = new DateTime();
            $delay->setTimestamp(strtotime('2 minutes ago'));
            // We are checking the User class in order to be certain we can call "getLastActivity".
            if ($user instanceof User && $user->getLastLogin() < $delay) {
                // User
                $user->setLastLogin(new DateTime());
                $this->em->persist($user);
                $this->em->flush();
            }
        }
    }
}
