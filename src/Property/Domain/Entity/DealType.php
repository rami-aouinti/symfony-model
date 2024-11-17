<?php

declare(strict_types=1);

namespace App\Property\Domain\Entity;

use App\Platform\Domain\Entity\Traits\EntityIdTrait;
use App\Platform\Domain\Entity\Traits\EntityNameTrait;
use App\Property\Domain\Entity\Traits\PropertyTrait;
use App\Property\Infrastructure\Repository\DealTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class DealType
 *
 * @package App\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Entity(repositoryClass: DealTypeRepository::class)]
#[UniqueEntity('slug')]
class DealType
{
    use EntityIdTrait;
    use EntityNameTrait;
    use PropertyTrait;

    final public const string MAPPED_BY = 'deal_type';
    final public const string GETTER = 'getDealType';
    final public const string SETTER = 'setDealType';
}
