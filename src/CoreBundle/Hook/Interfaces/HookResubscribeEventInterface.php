<?php

/* For licensing terms, see /license.txt */

/**
 * This file contains all Hook interfaces and their relation.
 * They are used for Hook classes.
 */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface HookResubscribeEventInterface.
 */
interface HookResubscribeEventInterface extends HookEventInterface
{
    /**
     * Update all the observers.
     *
     * @param int $type
     *
     * @return int
     */
    public function notifyResubscribe($type);
}
