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

namespace App\Platform\Application\Utils;

use Exception;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-05-06)
 * @package App\Utils
 */
class Timer
{
    /**
     * @var float[]
     */
    protected static array $start = [];

    /**
     * @var bool[]
     */
    protected static array $stop = [];

    /**
     * @var float[]
     */
    protected static array $time = [];

    /**
     * Starts a new timer.
     */
    public static function new(): int
    {
        self::$start[] = microtime(true);

        $id = array_key_last(self::$start);

        self::$time[$id] = 0;
        self::$stop[$id] = false;

        return $id;
    }

    /**
     * Alias for self::new.
     */
    public static function start(): int
    {
        return self::new();
    }

    /**
     * Stop a given timer.
     *
     * @throws Exception
     */
    public static function stop(int $id): float
    {
        if (!array_key_exists($id, self::$start)) {
            throw new Exception(sprintf('Unable to find id "%d" in timer (%s:%d).', $id, __FILE__, __LINE__));
        }

        $time = self::time($id);

        if (array_key_exists($id, self::$time)) {
            self::$time[$id] += $time;
        } else {
            self::$time[$id] = $time;
        }

        self::$stop[$id] = true;

        return self::$time[$id];
    }

    /**
     * Resumes a given timer.
     *
     * @throws Exception
     */
    public static function resume(int $id): float
    {
        if (!array_key_exists($id, self::$start)) {
            throw new Exception(sprintf('Unable to find id "%d" in timer (%s:%d).', $id, __FILE__, __LINE__));
        }

        if (self::$stop[$id] === false) {
            throw new Exception(sprintf('Use %s::stop() before using %s::resume() on timer "%d" (%s:%d).', self::class, self::class, $id, __FILE__, __LINE__));
        }

        self::$start[$id] = microtime(true);
        self::$stop[$id] = true;

        return self::$time[$id];
    }

    /**
     * Returns the time since the first start.
     *
     * @throws Exception
     */
    public static function time(int $id): float
    {
        if (!array_key_exists($id, self::$start)) {
            throw new Exception(sprintf('Unable to find id "%d" in timer (%s:%d).', $id, __FILE__, __LINE__));
        }

        return microtime(true) - self::$start[$id] + self::$time[$id];
    }
}
