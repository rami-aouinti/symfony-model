<?php

declare(strict_types=1);

namespace App\Platform\Transport\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;

/**
 * @package App\EventListener
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof TokenNotFoundException) {
            $customResponse = new JsonResponse([
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ], 419);
            $event->setResponse($customResponse);
        }
    }
}
