<?php

/* For licensing terms, see /license.txt */

/**
 * This file contains all Hook interfaces and their relation.
 * They are used for Hook classes.
 */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface CreateUserHookInterface.
 */
interface HookCreateCourseObserverInterface extends HookObserverInterface
{
    /**
     * @return int
     */
    public function hookCreateCourse(HookCreateCourseEventInterface $hook);
}
