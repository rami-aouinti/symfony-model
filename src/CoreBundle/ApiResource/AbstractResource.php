<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\ApiResource;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @package App\CoreBundle\ApiResource
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
abstract class AbstractResource
{
    #[Groups([
        'ctool:read',
    ])]
    public ?string $illustrationUrl = null;

    #[Groups([
        'calendar_event:read',
    ])]
    public ?array $resourceLinkListFromEntity = null;
}
