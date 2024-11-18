<?php

declare(strict_types=1);

namespace App\Property\Application\Service;

use App\Admin\Application\Service\PropertyService as Service;
use App\Platform\Application\Utils\Slugger;
use App\Property\Domain\Entity\Property;
use App\Property\Infrastructure\Repository\UserPropertyRepository;
use App\Property\Infrastructure\Transformer\PropertyTransformer;
use App\Property\Infrastructure\Transformer\RequestToArrayTransformer;
use App\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @package App\User\Application\Service\User
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PropertyService extends Service
{
    public function __construct(
        CsrfTokenManagerInterface $tokenManager,
        RequestStack $requestStack,
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,
        Slugger $slugger,
        private readonly PropertyTransformer $propertyTransformer,
        private readonly UserPropertyRepository $repository,
        private readonly RequestToArrayTransformer $transformer,
        private readonly TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($tokenManager, $requestStack, $em, $messageBus, $slugger);
    }

    public function getUserProperties(Request $request): PaginationInterface
    {
        $searchParams = $this->transformer->transform($request);
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $searchParams['user'] = $user->getId();

        return $this->repository->findByUser($searchParams);
    }

    public function contentToPlainText(Property $property, bool $isHtmlAllowed): Property
    {
        if (!$isHtmlAllowed) {
            $property = $this->propertyTransformer->contentToPlainText($property);
        }

        return $property;
    }

    public function contentToHtml(Property $property, bool $isHtml): Property
    {
        if (!$isHtml) {
            $property = $this->propertyTransformer->contentToHtml($property);
        }

        return $property;
    }

    public function sanitizeHtml(Property $property, bool $isHtmlAllowed): Property
    {
        if (!$isHtmlAllowed) {
            $property = $this->propertyTransformer->contentToPlainText($property);
            $property = $this->propertyTransformer->contentToHtml($property);
        }

        return $property;
    }
}
