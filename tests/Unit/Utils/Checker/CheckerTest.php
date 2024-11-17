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

namespace App\Tests\Unit\Utils\Checker;

use App\Exception\TypeInvalidException;
use App\Utils\Checker\Checker;
use PHPUnit\Framework\TestCase;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 * @link \App\Platform\Application\Utils\Checker\Checker
 */
final class CheckerTest extends TestCase
{
    /**
     * Test wrapper (Checker::checkX).
     *
     * @dataProvider dataProviderCheck
     *
     * @testdox $number) Test Checker::checkX
     * @param class-string<TypeInvalidException>|null $exceptionClass
     * @throws TypeInvalidException
     */
    public function testWrapperCheck(int $number, string $method, mixed $data, ?string $exceptionClass): void
    {
        /* Arrange */
        if ($exceptionClass !== null) {
            $this->expectException($exceptionClass);
        }

        /* Act */
        $checker = new Checker($data);

        /* Assert */
        $this->assertIsNumeric($number); // To avoid phpmd warning.
        $this->assertTrue(
            method_exists($checker, $method),
            sprintf('Class does not have method "%s".', $method)
        );
        $this->assertEquals($data, $checker->{$method}());
    }

    /**
     * Data provider (Checker::checkX).
     *
     * @return array<int, array{int, string, mixed, null|string}>
     * @link \App\Platform\Application\Utils\Checker\Checker::checkArray()
     * @link \App\Platform\Application\Utils\Checker\Checker::checkString()
     */
    public function dataProviderCheck(): array
    {
        $number = 0;

        return [
            /* array checks */
            [++$number, 'checkArray', [], null],
            [++$number, 'checkArray', [1, 2, 3], null],
            [++$number, 'checkArray', null, TypeInvalidException::class],

            /* string checks */
            [++$number, 'checkString', '', null],
            [++$number, 'checkString', null, TypeInvalidException::class],
        ];
    }
}
