<?php

declare(strict_types=1);

namespace App\Platform\Application\Service;

use App\Platform\Application\Service\Interfaces\MessageServiceInterface;
use App\Platform\Transport\Message\TestMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @package App\Service
 */
readonly class MessageService implements MessageServiceInterface
{
    public function __construct(
        private MessageBusInterface $bus
    ) {
    }

    /**
     * TODO: This is example for creating test message, you can delete it.
     *
     * @throws ExceptionInterface
     */
    public function sendTestMessage(string $someId): self
    {
        $this->bus->dispatch(new Envelope(new TestMessage($someId)));

        return $this;
    }
}