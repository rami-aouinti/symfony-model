<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

use function is_array;

/**
 * Object to identifier transformer.
 *
 * @author Julio Montoya
 *
 * @template-implements DataTransformerInterface<array, string>
 */
class ArrayToIdentifierTransformer implements DataTransformerInterface
{
    /**
     * @param $value
     *
     * @return string
     */
    public function transform($value): string
    {
        if (!is_array($value)) {
            return '';
        }

        return implode(',', $value);
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function reverseTransform($value): array
    {
        if (empty($value)) {
            return [];
        }

        return explode(',', $value);
    }
}
