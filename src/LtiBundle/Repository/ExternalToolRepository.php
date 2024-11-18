<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\LtiBundle\Repository;

use App\CoreBundle\Repository\ResourceRepository;
use App\CoreBundle\Repository\ResourceWithLinkInterface;
use App\LtiBundle\Entity\ExternalTool;
use App\Platform\Domain\Entity\ResourceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\RouterInterface;

class ExternalToolRepository extends ResourceRepository implements ResourceWithLinkInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalTool::class);
    }

    public function getLink(ResourceInterface $resource, RouterInterface $router, array $extraParams = []): string
    {
        $params = [
            'id' => $resource->getResourceIdentifier(),
        ];

        if (!empty($extraParams)) {
            $params = array_merge($params, $extraParams);
        }

        return $router->generate('chamilo_lti_show', $params);
    }
}
