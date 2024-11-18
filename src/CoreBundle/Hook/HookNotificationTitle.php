<?php

/* For licensing terms, see /license.txt */
/**
 * This file contains the Hook Event class for Title of Notifications.
 */

namespace App\CoreBundle\Hook;

use App\CoreBundle\Hook\Interfaces\HookNotificationTitleEventInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class HookNotificationTitle.
 */
class HookNotificationTitle extends HookEvent implements HookNotificationTitleEventInterface
{
    /**
     * Construct.
     */
    protected function __construct(EntityManager $entityManager)
    {
        parent::__construct('HookNotificationTitle', $entityManager);
    }

    /**
     * @param int $type
     */
    public function notifyNotificationTitle($type): array
    {
        // Check if exists data title
        /*if (isset($this->eventData['title'])) {
            return $this->eventData;
        }*/

        return [];
    }
}
