<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository;

use App\Platform\Domain\Entity\ResourceInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Allows resources to connect with a custom URL.
 */
interface ResourceWithLinkInterface
{
    public function getLink(ResourceInterface $resource, RouterInterface $router, array $extraParams = []): string;
}
