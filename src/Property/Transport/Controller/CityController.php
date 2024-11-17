<?php

declare(strict_types=1);

namespace App\Property\Transport\Controller;

use App\Place\Application\Service\CityService;
use App\Place\Domain\Entity\City;
use App\Platform\Transport\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class CityController
 *
 * @package App\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class CityController extends BaseController
{
    #[Route(path: '/city/{slug}', name: 'city', defaults: ['page' => 1], methods: ['GET'])]
    public function index(Request $request, City $city, CityService $service): Response
    {
        $searchParams = $service->getSearchParams($request, $city);
        $properties = $service->getProperties($searchParams);
        $siteOptions = $service->decorateOptions($this->site($request), $city);

        return $this->render('property/index.html.twig',
            [
                'site' => $siteOptions,
                'properties' => $properties,
                'searchParams' => $searchParams,
            ]
        );
    }
}
