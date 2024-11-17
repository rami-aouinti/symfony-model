<?php

declare(strict_types=1);

namespace App\Platform\Transport\EventSubscriber;

use App\Platform\Application\Middleware\ThrottleRequests;
use App\Platform\Application\Middleware\VerifyCsrfToken;
use App\Platform\Transport\Controller\Ajax\AjaxController;
use App\Platform\Transport\Twig\SourceCodeExtension;
use App\User\Transport\Controller\Auth\AuthController;
use Scheb\TwoFactorBundle\Controller\FormController;
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
        private VerifyCsrfToken $verifyCsrfToken,
        private ThrottleRequests $throttleRequests
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'registerCurrentController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        if (\is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof AjaxController) {
            $this->verifyCsrfToken->handle($event->getRequest());
        } elseif ($controller instanceof AuthController || $controller instanceof FormController) {
            $this->throttleRequests->handle($event->getRequest());
        }
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
