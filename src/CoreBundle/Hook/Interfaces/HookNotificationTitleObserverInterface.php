<?php

/* For licensing terms, see /license.txt */

/**
 * This file contains Hook observer interface for notification title.
 */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface HookNotificationTitleObserverInterface.
 */
interface HookNotificationTitleObserverInterface extends HookObserverInterface
{
    /**
     * @return array
     */
    public function hookNotificationTitle(HookNotificationTitleEventInterface $hook);
}
