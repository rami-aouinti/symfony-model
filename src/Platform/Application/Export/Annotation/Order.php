<?php

declare(strict_types=1);

namespace App\Platform\Application\Export\Annotation;

/**
 * @package App\Platform\Application\Export\Annotation
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Order
{
    public function __construct(
        public array $order = []
    ) {
    }
}
