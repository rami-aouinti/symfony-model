<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Repository\ResourceRepository;
use App\Forum\Domain\Entity\CForumCategory;
use App\Platform\Domain\Entity\ResourceInterface;
use App\Session\Domain\Entity\Session;
use Doctrine\Persistence\ManagerRegistry;

class CForumCategoryRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CForumCategory::class);
    }

    public function getForumCategoryByTitle(string $title, Course $course, ?Session $session = null): ?ResourceInterface
    {
        return $this->findCourseResourceByTitle(
            $title,
            $course->getResourceNode(),
            $course,
            $session
        );
    }
}
