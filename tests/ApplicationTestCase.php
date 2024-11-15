<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseTestCase;

/**
 * Class ApplicationTestCase
 *
 * @package App\Tests
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
abstract class ApplicationTestCase extends BaseTestCase
{
    use CreatesApplication;
}
