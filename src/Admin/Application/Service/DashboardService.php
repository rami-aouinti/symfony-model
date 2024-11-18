<?php

declare(strict_types=1);

namespace App\Admin\Application\Service;

use App\Category\Domain\Entity\Category;
use App\Place\Domain\Entity\City;
use App\Platform\Domain\Entity\Page;
use App\Property\Application\Service\Cache\GetCache;
use App\Property\Domain\Entity\DealType;
use App\Property\Domain\Entity\Property;
use App\User\Domain\Entity\User;
use Psr\Cache\InvalidArgumentException;

/**
 * @package App\Service\Admin
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class DashboardService
{
    use GetCache;

    /**
     * @throws InvalidArgumentException
     */
    public function countProperties(): int
    {
        return $this->getCount('properties_count', Property::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function countCities(): int
    {
        return $this->getCount('cities_count', City::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function countDealTypes(): int
    {
        return $this->getCount('deal_types_count', DealType::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function countCategories(): int
    {
        return $this->getCount('categories_count', Category::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function countPages(): int
    {
        return $this->getCount('pages_count', Page::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function countUsers(): int
    {
        return $this->getCount('users_count', User::class);
    }
}
