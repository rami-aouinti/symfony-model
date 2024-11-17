<?php

declare(strict_types=1);

namespace App\Admin\Application\Service;

use App\Property\Application\Service\AbstractService;
use App\Property\Domain\Entity\DealType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class DealTypeService
 *
 * @package App\Admin\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class DealTypeService extends AbstractService
{
    public function __construct(
        CsrfTokenManagerInterface $tokenManager,
        RequestStack $requestStack,
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct($tokenManager, $requestStack);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(DealType $dealType): void
    {
        $this->save($dealType);
        $this->clearCache('deal_types_count');
        $this->addFlash('success', 'message.created');
    }

    public function update(DealType $dealType): void
    {
        $this->save($dealType);
        $this->addFlash('success', 'message.updated');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function remove(DealType $dealType): void
    {
        $this->em->remove($dealType);
        $this->em->flush();
        $this->clearCache('deal_types_count');
        $this->addFlash('success', 'message.deleted');
    }

    private function save(DealType $dealType): void
    {
        $this->em->persist($dealType);
        $this->em->flush();
    }
}
