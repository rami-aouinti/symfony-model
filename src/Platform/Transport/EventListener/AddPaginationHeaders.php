<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Platform\Transport\EventListener;

use ApiPlatform\Doctrine\Orm\Paginator;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

use function sprintf;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.0 (2022-02-05)
 * @since 1.0.0 Add X-Total-Count to header (#48)
 * @package App\Doctrine
 */
class AddPaginationHeaders
{
    /**
     * Method onKernelResponse.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        /* Don't do anything if it's not the main request */
        if (!$event->isMainRequest()) {
            return;
        }

        /* Get request. */
        $request = $event->getRequest();

        /* Add X-Total-Count header. */
        $data = $request->attributes->get('data');
        if (($data) && $data instanceof Paginator) {
            $from = $data->count() ? ($data->getCurrentPage() - 1) * $data->getItemsPerPage() : 0;
            $to = $data->getCurrentPage() < $data->getLastPage() ? $data->getCurrentPage() * $data->getItemsPerPage() : $data->getTotalItems();

            $response = $event->getResponse();
            $response->headers->add([
                'Accept-Ranges' => 'items',
                'Range-Unit' => 'items',
                'Content-Range' => sprintf('%u-%u/%u', $from, $to, $data->getTotalItems()),
                'X-Total-Count' => $data->getTotalItems(),
            ]);
        }
    }
}
