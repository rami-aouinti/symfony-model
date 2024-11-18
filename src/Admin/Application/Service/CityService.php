<?php

declare(strict_types=1);

namespace App\Admin\Application\Service;

use App\Place\Domain\Entity\City;
use App\Property\Application\Service\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @package App\Admin\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class CityService extends AbstractService
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
    public function create(City $city): void
    {
        $this->save($city);
        $this->clearCache('cities_count');
        $this->addFlash('success', 'message.created');
    }

    public function update(City $city): void
    {
        $this->save($city);
        $this->addFlash('success', 'message.updated');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function remove(City $city): void
    {
        $this->em->remove($city);
        $this->em->flush();
        $this->clearCache('cities_count');
        $this->addFlash('success', 'message.deleted');
    }

    private function save(City $city): void
    {
        $this->em->persist($city);
        $this->em->flush();
    }
}
