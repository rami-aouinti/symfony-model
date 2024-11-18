<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\ApiResource;

use App\CoreBundle\Tool\AbstractTool;
use App\Platform\Domain\Entity\ResourceNode;
use Symfony\Component\Serializer\Annotation\Groups;

class CourseTool extends AbstractResource
{
    #[Groups(['ctool:read'])]
    public ?int $iid = null;

    #[Groups(['ctool:read'])]
    public string $title;

    #[Groups(['ctool:read'])]
    public ?bool $visibility = null;

    #[Groups(['ctool:read'])]
    public AbstractTool $tool;

    #[Groups(['ctool:read'])]
    public ?ResourceNode $resourceNode = null;

    #[Groups(['ctool:read'])]
    public string $url;
}
