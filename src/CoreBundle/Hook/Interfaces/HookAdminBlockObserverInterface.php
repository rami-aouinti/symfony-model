<?php

/* For licensing terms, see /license.txt */

/**
 * This file contains all Hook interfaces and their relation.
 * They are used for Hook classes.
 */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface HookAdminBlockObserverInterface.
 */
interface HookAdminBlockObserverInterface extends HookObserverInterface
{
    /**
     * @return int
     */
    public function hookAdminBlock(HookAdminBlockEventInterface $hook);
}
