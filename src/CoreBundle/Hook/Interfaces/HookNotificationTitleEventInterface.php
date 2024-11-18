<?php

/* For licensing terms, see /license.txt */

/**
 * This file contains Hook event interface for notification title.
 */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface HookNotificationTitleEventInterface.
 */
interface HookNotificationTitleEventInterface extends HookEventInterface
{
    /**
     * @param int $type
     *
     * @return array
     */
    public function notifyNotificationTitle($type);
}
