<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Repository\ResourceRepository;
use App\Quiz\Domain\Entity\CQuizQuestion;
use Doctrine\Persistence\ManagerRegistry;

final class CQuizQuestionRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CQuizQuestion::class);
    }

    public function getHotSpotImageUrl(CQuizQuestion $resource): string
    {
        $params = [
            'mode' => 'view',
            'filter' => 'hotspot_question',
        ];

        return $this->getResourceFileUrl($resource, $params);
    }
}
