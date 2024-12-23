<?php

declare(strict_types=1);

namespace App\Platform\Transport\Controller;

use App\Category\Domain\Entity\Category;
use App\Configuration\Infrastructure\Repository\SettingsRepository;
use App\Place\Domain\Entity\City;
use App\Platform\Transport\Controller\Traits\MenuTrait;
use App\Property\Domain\Entity\DealType;
use App\Property\Domain\Entity\Feature;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package App\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
abstract class BaseController extends AbstractController
{
    use MenuTrait;

    public function __construct(
        private readonly SettingsRepository $settingsRepository,
        protected ManagerRegistry $doctrine
    ) {
    }

    public function site(Request $request): array
    {
        $settings = $this->settingsRepository->findAllAsArray();

        $fields = $this->searchFields();

        $this->setDoctrine($this->doctrine);

        $menu = $this->menu($request);

        return array_merge($settings, $fields, $menu);
    }

    private function searchFields(): array
    {
        // Get city
        $cities = $this->doctrine
            ->getRepository(City::class)->findAll();

        // Get categories
        $categories = $this->doctrine
            ->getRepository(Category::class)->findAll();

        // Get deal types
        $dealTypes = $this->doctrine
            ->getRepository(DealType::class)->findAll();

        // Get features
        $features = $this->doctrine
            ->getRepository(Feature::class)->findAll();

        return [
            'cities' => $cities,
            'features' => $features,
            'categories' => $categories,
            'deal_types' => $dealTypes,
        ];
    }
}
