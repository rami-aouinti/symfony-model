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

namespace App\Platform\Application\Utils\Converter;

use App\Platform\Transport\Exception\FunctionReplaceException;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
readonly class CamelCaseToSeparatedConverter implements NameConverterInterface
{
    /**
     * @param array<int, string>|null $attributes
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        private ?array $attributes = null,
        private bool $lowerCamelCase = true
    ) {
    }

    /**
     * @throws FunctionReplaceException
     */
    public function normalize(string $propertyName): string
    {
        if ($this->attributes === null || in_array($propertyName, $this->attributes)) {
            return (new NamingConventions($propertyName))->getSeparated();
        }

        return $propertyName;
    }

    /**
     * @throws FunctionReplaceException
     */
    public function denormalize(string $propertyName): string
    {
        $namingConverter = new NamingConventions($propertyName);

        $camelCasedName = $this->lowerCamelCase ? $namingConverter->getCamelCase() : $namingConverter->getPascalCase();

        if ($this->attributes === null || in_array($camelCasedName, $this->attributes)) {
            return $camelCasedName;
        }

        return $propertyName;
    }
}
