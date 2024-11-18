<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Traits;

use App\CoreBundle\Entity\AccessUrlRelCourse;
use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\Gradebook\GradebookCategory;
use App\CoreBundle\Entity\Gradebook\GradebookEvaluation;
use App\CoreBundle\Entity\Gradebook\GradebookLink;
use App\CourseBundle\Entity\CLp\CLpCategoryRelUserGroup;
use App\CourseBundle\Entity\CLp\CLpRelUser;
use App\CourseBundle\Entity\CLp\CLpRelUserGroup;
use App\CourseBundle\Entity\PeerAssessment\CPeerAssessment;
use App\Track\Domain\Entity\TrackEHotspot;

/**
 * Trait CourseTrait.
 */
trait CourseTrait
{
    /**
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * @param Course $course
     *
     * @return CLpRelUser|AccessUrlRelCourse|GradebookCategory|GradebookEvaluation|GradebookLink|CourseTrait|CLpCategoryRelUserGroup|CLpRelUserGroup|CPeerAssessment|TrackEHotspot
     */
    public function setCourse(Course $course): self
    {
        $this->course = $course;

        return $this;
    }
}
