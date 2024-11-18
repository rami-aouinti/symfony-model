<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Tool;

use App\CourseBundle\Entity\Student\CStudentPublication;
use App\CourseBundle\Entity\Student\CStudentPublicationAssignment;
use App\CourseBundle\Entity\Student\CStudentPublicationComment;
use App\CourseBundle\Entity\Student\CStudentPublicationCorrection;

class Assignment extends AbstractTool implements ToolInterface
{
    public function getTitle(): string
    {
        return 'student_publication';
    }

    public function getTitleToShow(): string
    {
        return 'Assignments';
    }

    public function getLink(): string
    {
        return '/resources/assignment/:nodeId';
    }

    public function getIcon(): string
    {
        return 'mdi-inbox-full';
    }

    public function getCategory(): string
    {
        return 'interaction';
    }

    public function getResourceTypes(): ?array
    {
        return [
            'student_publications' => CStudentPublication::class,
            'student_publications_assignments' => CStudentPublicationAssignment::class,
            'student_publications_comments' => CStudentPublicationComment::class,
            'student_publications_corrections' => CStudentPublicationCorrection::class,
        ];
    }
}
