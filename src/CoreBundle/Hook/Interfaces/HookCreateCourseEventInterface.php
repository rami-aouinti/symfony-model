<?php

/* For licensing terms, see /license.txt */

/**
 * This file contains all Hook interfaces and their relation.
 * They are used for Hook classes.
 */

namespace App\CoreBundle\Hook\Interfaces;

/**
 * Interface HookCreateUserEventInterface.
 */
interface HookCreateCourseEventInterface extends HookEventInterface
{
    /**
     * Update all the observers.
     *
     * @param int $type
     *
     * @return int
     */
    public function notifyCreateCourse($type);
}
