<?php

declare(strict_types=1);

namespace App\Admin\Application\Service;

use App\Property\Application\Service\AbstractService;
use App\User\Domain\Entity\User;
use App\User\Infrastructure\Transformer\UserTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class UserService
 *
 * @package App\Admin\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class UserService extends AbstractService
{
    public function __construct(
        CsrfTokenManagerInterface $tokenManager,
        RequestStack $requestStack,
        private readonly EntityManagerInterface $em,
        private readonly UserTransformer $transformer
    ) {
        parent::__construct($tokenManager, $requestStack);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(User $user): void
    {
        $user = $this->transformer->transform($user);
        $this->save($user);
        $this->clearCache('users_count');
        $this->addFlash('success', 'message.created');
    }

    public function update(User $user): void
    {
        $user = $this->transformer->transform($user);
        $this->save($user);
        $this->addFlash('success', 'message.updated');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function remove(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
        $this->clearCache('users_count');
        $this->addFlash('success', 'message.deleted');
    }

    private function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }
}
