<?php

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface HookConditionalLoginEventInterface.
 */
interface HookConditionalLoginEventInterface extends HookEventInterface
{
    /**
     * Call Conditional Login hooks.
     */
    public function notifyConditionalLogin(): array;
}
