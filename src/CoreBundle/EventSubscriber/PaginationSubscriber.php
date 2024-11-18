<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\EventSubscriber;

use Knp\Component\Pager\Event\ItemsEvent;
use Knp\Component\Pager\Event\PaginationEvent;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PaginationSubscriber
 *
 * @package App\CoreBundle\EventSubscriber
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class PaginationSubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event): void
    {
        if (\is_array($event->target)) {
            $event->items = $event->target;
            $event->count = \count($event->target);
            $event->stopPropagation();
        }
    }

    public function pagination(PaginationEvent $event): void
    {
        if (\is_array($event->target)) {
            $event->setPagination(new SlidingPagination());
        }

        $event->stopPropagation();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'knp_pager.items' => ['items', 1/* increased priority to override any internal */],
            'knp_pager.pagination' => ['pagination', 0],
        ];
    }
}
