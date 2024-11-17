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

namespace App\User\Application\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\User\Domain\Entity\User;
use Exception;

/**
 * Entity class UserProvider
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-21)
 * @since 0.1.0 (2022-11-21) First version.
 * @implements ProviderInterface<User>
 */
final class UserProvider implements ProviderInterface
{
    private const string METHOD_GET_COLLECTION = 'GET_COLLECTION';

    /**
     * Provide User entity or User collection.
     *
     * @param array<string, mixed> $uriVariables
     * @param array<int|string, mixed> $context
     * @return object|array<int, object>|null
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return match (true) {
            $operation instanceof GetCollection => $this->provideGetCollection($uriVariables, $context),
            default => throw new Exception(sprintf('Unsupported case (%s:%d)', __FILE__, __LINE__))
        };
    }

    /**
     * Provide User collection.
     *
     * @param array<string, mixed> $uriVariables
     * @param array<int|string, mixed> $context
     * @return array<int, object>
     */
    private function provideGetCollection(array $uriVariables = [], array $context = []): array
    {
        return [];
    }
}
