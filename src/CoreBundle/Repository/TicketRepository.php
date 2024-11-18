<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Repository;

use App\CoreBundle\Entity\Ticket\Ticket;
use Doctrine\Persistence\ManagerRegistry;

class TicketRepository extends ResourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }
}
