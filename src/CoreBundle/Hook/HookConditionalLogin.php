<?php

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Hook;

use App\CoreBundle\Hook\Interfaces\HookConditionalLoginEventInterface;
use App\CoreBundle\Hook\Interfaces\HookConditionalLoginObserverInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class HookConditionalLogin.
 *
 * Hook to implement Conditional Login.
 */
class HookConditionalLogin extends HookEvent implements HookConditionalLoginEventInterface
{
    protected function __construct(EntityManager $entityManager)
    {
        parent::__construct('HookConditionalLogin', $entityManager);
    }

    /**
     * Notify to all hook observers.
     */
    public function notifyConditionalLogin(): array
    {
        $conditions = [];

        /** @var HookConditionalLoginObserverInterface $observer */
        foreach ($this->observers as $observer) {
            $conditions[] = $observer->hookConditionalLogin($this);
        }

        return $conditions;
    }
}
