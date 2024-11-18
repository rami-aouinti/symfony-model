<?php

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Hook;

use App\CoreBundle\Hook\Interfaces\HookUpdateUserEventInterface;
use App\CoreBundle\Hook\Interfaces\HookUpdateUserObserverInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class HookUpdateUser.
 */
class HookUpdateUser extends HookEvent implements HookUpdateUserEventInterface
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct('HookUpdateUser', $entityManager);
    }

    /**
     * Update all the observers.
     *
     * @param int $type
     *
     * @return int
     */
    public function notifyUpdateUser($type)
    {
        $this->eventData['type'] = $type;

        /** @var HookUpdateUserObserverInterface $observer */
        foreach ($this->observers as $observer) {
            $observer->hookUpdateUser($this);
        }

        return 1;
    }
}
