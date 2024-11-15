<?php

declare(strict_types=1);

namespace App\Platform\Application\Service;

use App\Platform\Application\Service\Interfaces\MessageServiceInterface;
use App\Platform\Transport\Message\Interfaces\MessageHighInterface;
use App\Platform\Transport\Message\Interfaces\MessageLowInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @package App\Platform\Application\Service
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
readonly class MessageService implements MessageServiceInterface
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws ExceptionInterface
     */
    public function sendMessage(MessageHighInterface|MessageLowInterface $message): self
    {
        $this->bus->dispatch(new Envelope($message));

        return $this;
    }
}
