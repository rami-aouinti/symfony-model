<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Event;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\Session\Domain\Entity\Session;
use Symfony\Contracts\EventDispatcher\Event;

class SessionAccess extends Event
{
    protected User $user;
    protected Course $course;
    protected Session $session;

    public function __construct(User $user, Course $course, Session $session)
    {
        $this->user = $user;
        $this->course = $course;
        $this->session = $session;
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

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }
}
