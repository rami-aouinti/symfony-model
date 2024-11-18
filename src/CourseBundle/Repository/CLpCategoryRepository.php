<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Repository;

use App\CoreBundle\Repository\ResourceRepository;
use App\CoreBundle\Repository\ResourceWithLinkInterface;
use App\CourseBundle\Entity\CLp\CLpCategory;
use App\Platform\Domain\Entity\ResourceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\RouterInterface;

final class CLpCategoryRepository extends ResourceRepository implements ResourceWithLinkInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CLpCategory::class);
    }

    public function getLink(ResourceInterface $resource, RouterInterface $router, array $extraParams = []): string
    {
        $params = [
            'id' => $resource->getResourceIdentifier(),
            'name' => 'lp/lp_controller.php',
            'action' => 'view_category',
        ];
        if (!empty($extraParams)) {
            $params = array_merge($params, $extraParams);
        }

        return $router->generate('legacy_main', $params);
    }
}
