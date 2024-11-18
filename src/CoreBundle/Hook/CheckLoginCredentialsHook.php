<?php

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Hook;

use App\CoreBundle\Hook\Interfaces\CheckLoginCredentialsHookEventInterface;
use App\CoreBundle\Hook\Interfaces\CheckLoginCredentialsHookObserverInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class CheckLoginCredentialsHook.
 */
class CheckLoginCredentialsHook extends HookEvent implements CheckLoginCredentialsHookEventInterface
{
    protected function __construct(EntityManager $entityManager)
    {
        parent::__construct('CheckLoginCredentialsHook', $entityManager);
    }

    /**
     * Call to all observers.
     */
    public function notifyLoginCredentials(): bool
    {
        /** @var CheckLoginCredentialsHookObserverInterface $observer */
        foreach ($this->observers as $observer) {
            $isChecked = $observer->checkLoginCredentials($this);

            if ($isChecked) {
                return true;
            }
        }

        return false;
    }
}
