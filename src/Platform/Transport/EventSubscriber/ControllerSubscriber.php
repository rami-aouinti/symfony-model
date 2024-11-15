<?php

declare(strict_types=1);

namespace App\Platform\Transport\EventSubscriber;

use App\Platform\Transport\Twig\SourceCodeExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @package App\Blog\Transport\EventSubscriber
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class ControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SourceCodeExtension $twigExtension,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'registerCurrentController',
        ];
    }

    public function registerCurrentController(ControllerEvent $event): void
    {
        // this check is needed because in Symfony a request can perform any
        // number of sub-requests. See
        // https://symfony.com/doc/current/components/http_kernel.html#sub-requests
        if ($event->isMainRequest()) {
            $this->twigExtension->setController($event->getController());
        }
    }
}
