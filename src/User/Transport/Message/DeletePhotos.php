<?php

declare(strict_types=1);

namespace App\User\Transport\Message;

use App\Property\Domain\Entity\Property;

/**
 * Class DeletePhotos
 *
 * @package App\User\Transport\Message
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class DeletePhotos
{
    public function __construct(private readonly Property $property)
    {
    }

    public function getProperty(): Property
    {
        return $this->property;
    }
}
