<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @package App\Tests
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
abstract class UnitTestCase extends BaseTestCase
{
    use CreatesApplication;
}
