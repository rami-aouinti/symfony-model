<?php

declare(strict_types=1);

namespace App\Platform\Application\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function count;
use function is_string;

/**
 * @package App\Platform\Application\Validator\Constraints
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class ColorChoicesValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ColorChoices) {
            throw new UnexpectedTypeException($constraint, ColorChoices::class);
        }

        if (!is_string($value) || trim($value) === '') {
            return;
        }

        $colors = explode(',', $value);

        foreach ($colors as $color) {
            $color = explode('|', $color);
            $name = $color[0];
            $code = $color[0];
            if (count($color) > 1) {
                $code = $color[1];
            }

            if (empty($name)) {
                $name = $code;
            }

            if (preg_match('/^#[0-9a-fA-F]{6}$/i', $code) !== 1) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $this->formatValue($code))
                    ->setCode(ColorChoices::COLOR_CHOICES_ERROR)
                    ->addViolation();

                return;
            }

            if ($name === $code) {
                return;
            }

            $name = str_replace(['-', ' '], '', $name);
            $length = mb_strlen($name);

            if ($length > $constraint->maxLength || !preg_match('/^[a-zA-Z0-9]+$/', $name)) {
                $this->context->buildViolation($constraint->invalidNameMessage)
                    ->setParameter('{{ name }}', $this->formatValue($name))
                    ->setParameter('{{ color }}', $this->formatValue($code))
                    ->setParameter('{{ max }}', $this->formatValue($constraint->maxLength))
                    ->setParameter('{{ count }}', $this->formatValue($length))
                    ->setCode(ColorChoices::COLOR_CHOICES_NAME_ERROR)
                    ->addViolation();
            }
        }
    }
}
