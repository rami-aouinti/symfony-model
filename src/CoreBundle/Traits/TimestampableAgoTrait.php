<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Traits;

use Carbon\Carbon;
use Symfony\Component\Serializer\Annotation\Groups;

trait TimestampableAgoTrait
{
    #[Groups(['api'])]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    #[Groups(['api'])]
    public function getUpdatedAtAgo(): string
    {
        return Carbon::instance($this->getUpdatedAt())->diffForHumans();
    }
}
