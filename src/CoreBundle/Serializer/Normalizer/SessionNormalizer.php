<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Serializer\Normalizer;

use App\CoreBundle\ServiceHelper\UserHelper;
use App\Session\Domain\Entity\Session;
use LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SessionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'SESSION_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly UserHelper $userHelper,
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        \assert($object instanceof Session);

        try {
            $object->getAccessVisibility();
        } catch (LogicException) {
            $object->setAccessVisibilityByUser(
                $this->userHelper->getCurrent()
            );
        }

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Session;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Session::class => false,
        ];
    }
}
