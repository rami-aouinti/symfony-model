<?php

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\CoreBundle\Repository\MessageRepository;
use App\Message\Domain\Entity\Message;

/**
 * @template-implements ProviderInterface<Message>
 */
final class MessageByGroupStateProvider implements ProviderInterface
{
    private MessageRepository $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function supports(Operation $operation, array $uriVariables = [], array $context = []): bool
    {
        return $operation->getClass() === Message::class && $operation->getName() === 'get_messages_by_group';
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $groupId = $context['filters']['groupId'] ?? null;

        if ($groupId === null) {
            return [];
        }

        return $this->messageRepository->getMessagesByGroup((int)$groupId, true);
    }
}
