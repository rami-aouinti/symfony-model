<?php

declare(strict_types=1);

namespace App\User\Application\Validator;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @package App\User\Application\Validator
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class PhotoRequirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new NotBlank([
                'message' => 'Please select a file to upload',
            ]),
            new File([
                'maxSize' => '12M',
                'mimeTypes' => [
                    'image/*',
                ],
            ]),
        ];
    }
}
