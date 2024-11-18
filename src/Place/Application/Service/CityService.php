<?php

declare(strict_types=1);

namespace App\Place\Application\Service;

use App\Place\Domain\Entity\City;
use App\Property\Infrastructure\Repository\FilterRepository;
use App\Property\Infrastructure\Transformer\RequestToArrayTransformer;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package App\Place\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class CityService
{
    public function __construct(
        private RequestToArrayTransformer $transformer,
        private FilterRepository $repository
    ) {
    }

    public function getSearchParams(Request $request, City $city): array
    {
        $searchParams = $this->transformer->transform($request);
        $searchParams['city'] = $city->getId();

        return $searchParams;
    }

    public function getProperties(array $searchParams): PaginationInterface
    {
        return $this->repository->findByFilter($searchParams);
    }

    public function decorateOptions(array $siteOptions, City $city): array
    {
        $siteOptions['title'] = $city->getTitle() ?? $siteOptions['title'];
        $siteOptions['meta_title'] = $city->getMetaTitle() ?? $city->getName();
        $siteOptions['meta_description'] = $city->getMetaDescription() ?? $siteOptions['meta_description'];

        return $siteOptions;
    }
}
