<?php

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Hook;

use App\CoreBundle\Hook\Interfaces\HookNotificationContentEventInterface;
use Doctrine\ORM\EntityManager;

/**
 * Hook Event class for Content format of Notifications.
 */
class HookNotificationContent extends HookEvent implements HookNotificationContentEventInterface
{
    /**
     * Construct.
     */
    protected function __construct(EntityManager $entityManager)
    {
        parent::__construct('HookNotificationContent', $entityManager);
    }

    /**
     * @param int $type
     */
    public function notifyNotificationContent($type): array
    {
        // Check if exists data content
        /*if (isset($this->eventData['content'])) {
        }
        return [];*/
    }
}
