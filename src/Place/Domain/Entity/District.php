<?php

declare(strict_types=1);

namespace App\Place\Domain\Entity;

use App\Place\Domain\Entity\Traits\CityTrait;
use App\Place\Infrastructure\Repository\DistrictRepository;
use App\Platform\Domain\Entity\Traits\EntityIdTrait;
use App\Platform\Domain\Entity\Traits\EntityNameTrait;
use App\Property\Domain\Entity\Traits\PropertyTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @package App\Place\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: DistrictRepository::class)]
#[UniqueEntity('slug')]
class District
{
    use CityTrait;
    use EntityIdTrait;
    use EntityNameTrait;
    use PropertyTrait;

    final public const string MAPPED_BY = 'district';
    final public const string INVERSED_BY = 'districts';
    final public const string GETTER = 'getDistrict';
    final public const string SETTER = 'setDistrict';
}
