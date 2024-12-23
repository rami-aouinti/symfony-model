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

namespace App\Place\Domain\Entity;

/**
 * Entity class Location
 *
 * Used only for forms. Not within the db.
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.1 (2022-05-25)
 * @since 1.0.0 First version.
 * @package App\Calendar\Domain\Entity
 */
class Location
{
    final public const string DEFAULT_LATITUDE = '47.900635';

    final public const string DEFAULT_LONGITUDE = '13.601868';

    protected string $locationFull;

    /**
     * Gets the full location ([latitude]° [longitude]°).
     */
    public function getLocationFull(): string
    {
        return $this->locationFull;
    }

    /**
     * Sets the full location ([latitude]° [longitude]°).
     */
    public function setLocationFull(string $locationFull): self
    {
        $this->locationFull = $locationFull;

        return $this;
    }
}
