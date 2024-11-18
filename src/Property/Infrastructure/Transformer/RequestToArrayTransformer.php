<?php

declare(strict_types=1);

namespace App\Property\Infrastructure\Transformer;

use Symfony\Component\HttpFoundation\Request;

/**
 * @package App\Property\Infrastructure\Transformer
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class RequestToArrayTransformer
{
    public function transform(Request $request): array
    {
        return [
            'city' => $request->query->getInt('city'),
            'deal_type' => $request->query->getInt('deal_type'),
            'category' => $request->query->getInt('category'),
            'bedrooms' => $request->query->getInt('bedrooms'),
            'guests' => $request->query->getInt('guests'),
            'feature' => $request->query->getInt('feature'),
            'sort_by' => $request->query->get('sort_by', 'priority_number'),
            'state' => $request->query->get('state', 'published'),
            'page' => $request->query->getInt('page', 1),
        ];
    }
}
