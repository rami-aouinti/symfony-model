<?php

declare(strict_types=1);

namespace App\Place\Domain\Entity;

use App\Place\Domain\Entity\Traits\CityTrait;
use App\Place\Infrastructure\Repository\NeighborhoodRepository;
use App\Platform\Domain\Entity\Traits\EntityIdTrait;
use App\Platform\Domain\Entity\Traits\EntityNameTrait;
use App\Property\Domain\Entity\Traits\PropertyTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @package App\Place\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: NeighborhoodRepository::class)]
#[UniqueEntity('slug')]
class Neighborhood
{
    use CityTrait;
    use EntityIdTrait;
    use EntityNameTrait;
    use PropertyTrait;

    final public const string MAPPED_BY = 'neighborhood';
    final public const string INVERSED_BY = 'neighborhoods';
    final public const string GETTER = 'getNeighborhood';
    final public const string SETTER = 'setNeighborhood';
}