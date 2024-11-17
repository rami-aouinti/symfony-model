<?php

declare(strict_types=1);

namespace App\Property\Transport\Controller\Ajax;

use App\Place\Domain\Entity\City;
use App\Place\Infrastructure\Repository\DistrictRepository;
use App\Place\Infrastructure\Repository\MetroRepository;
use App\Place\Infrastructure\Repository\NeighborhoodRepository;
use App\Platform\Transport\Controller\Ajax\AjaxController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

/**
 * Class CityController
 *
 * @package App\Controller\Ajax
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class CityController extends AbstractController implements AjaxController
{
    #[Route(
        path: '/city/{id}.json',
        name: 'city_json',
        requirements: ['id' => Requirement::POSITIVE_INT],
        methods: ['GET']
    )]
    public function show(
        City $city,
        MetroRepository $metroRepository,
        DistrictRepository $districtRepository,
        NeighborhoodRepository $neighborhoodRepository,
    ): JsonResponse {
        return $this->json([
            'city' => $city->getName(),
            'districts' => $this->find($city, $districtRepository),
            'neighborhoods' => $this->find($city, $neighborhoodRepository),
            'metro_stations' => $this->find($city, $metroRepository),
        ]);
    }

    private function find(
        City $city,
        DistrictRepository|MetroRepository|NeighborhoodRepository $repository
    ): array {
        return array_map(fn ($entity) => [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
        ],
            $repository->findBy(['city' => $city]));
    }
}
