<?php

declare(strict_types=1);

namespace App\Platform\Application\Service\Interfaces;

use App\Platform\Transport\Message\Interfaces\MessageHighInterface;
use App\Platform\Transport\Message\Interfaces\MessageLowInterface;
use Throwable;

/**
 * @package App\Service\Interfaces
 */
interface MessageServiceInterface
{
    /**
     * @throws Throwable
     */
    public function sendMessage(MessageHighInterface|MessageLowInterface $message): self;
}
