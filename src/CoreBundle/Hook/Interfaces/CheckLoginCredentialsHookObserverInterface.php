<?php

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface CheckLoginCredentialsHookObserverInterface.
 */
interface CheckLoginCredentialsHookObserverInterface extends HookObserverInterface
{
    public function checkLoginCredentials(CheckLoginCredentialsHookEventInterface $event): bool;
}
