<?php

/* For licensing terms, see /license.txt */

/**
 * This file contains all Hook interfaces and their relation.
 * They are used for Hook classes.
 */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface HookAdminBlockEventInterface.
 */
interface HookAdminBlockEventInterface extends HookEventInterface
{
    /**
     * @return int
     */
    public function notifyAdminBlock(int $type);
}
