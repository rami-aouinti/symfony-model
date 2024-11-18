<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Event;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use Symfony\Contracts\EventDispatcher\Event;

class CourseAccess extends Event
{
    protected User $user;
    protected Course $course;

    public function __construct(User $user, Course $course)
    {
        $this->user = $user;
        $this->course = $course;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }
}
