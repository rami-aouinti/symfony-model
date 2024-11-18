<?php

declare(strict_types=1);

namespace App\CoreBundle\Form\DataTransformer;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Webmozart\Assert\Assert;

/**
 * @template-implements DataTransformerInterface<object, int|string>
 */
final class ResourceToIdentifierTransformer implements DataTransformerInterface
{
    private ObjectRepository $repository;

    private string $identifier;

    public function __construct(ObjectRepository $repository, ?string $identifier = null)
    {
        $this->repository = $repository;
        $this->identifier = $identifier ?? 'id';
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function transform($value): mixed
    {
        if ($value === null) {
            return null;
        }

        /* @psalm-suppress ArgumentTypeCoercion */
        Assert::isInstanceOf($value, $this->repository->getClassName());

        return PropertyAccess::createPropertyAccessor()->getValue($value, $this->identifier);
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function reverseTransform($value): mixed
    {
        if ($value === null) {
            return null;
        }

        $resource = $this->repository->findOneBy([
            $this->identifier => $value,
        ]);
        if ($resource === null) {
            throw new TransformationFailedException(\sprintf('Object "%s" with identifier "%s"="%s" does not exist.', $this->repository->getClassName(), $this->identifier, $value));
        }

        return $resource;
    }
}
