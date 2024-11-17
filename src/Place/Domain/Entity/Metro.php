<?php

declare(strict_types=1);

namespace App\Place\Domain\Entity;

use App\Place\Domain\Entity\Traits\CityTrait;
use App\Place\Infrastructure\Repository\MetroRepository;
use App\Platform\Domain\Entity\Traits\EntityIdTrait;
use App\Platform\Domain\Entity\Traits\EntityNameTrait;
use App\Property\Domain\Entity\Traits\PropertyTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Metro
 *
 * @package App\Place\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: MetroRepository::class)]
#[UniqueEntity('slug')]
class Metro
{
    use CityTrait;
    use EntityIdTrait;
    use EntityNameTrait;
    use PropertyTrait;

    final public const string MAPPED_BY = 'metro_station';
    final public const string INVERSED_BY = 'metro_stations';
    final public const string GETTER = 'getMetroStation';
    final public const string SETTER = 'setMetroStation';
}
