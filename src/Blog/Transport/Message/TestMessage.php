<?php

declare(strict_types=1);

namespace App\Blog\Transport\Message;

use App\Blog\Transport\Message\Interfaces\MessageHighInterface;

/**
 * TODO: This is message example, you can delete it.
 *
 * @package App\Message
 */
readonly class TestMessage implements MessageHighInterface
{
    public function __construct(
        private string $someId
    ) {
    }

    public function getSomeId(): string
    {
        return $this->someId;
    }
}
