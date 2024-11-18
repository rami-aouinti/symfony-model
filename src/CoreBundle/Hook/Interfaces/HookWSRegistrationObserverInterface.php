<?php

/* For licensing terms, see /license.txt */

/**
 * This file contains all Hook interfaces and their relation.
 * They are used for Hook classes.
 */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface HookWSRegistrationObserverInterface.
 */
interface HookWSRegistrationObserverInterface extends HookObserverInterface
{
    /**
     * @return int
     */
    public function hookWSRegistration(HookWSRegistrationEventInterface $hook);
}
