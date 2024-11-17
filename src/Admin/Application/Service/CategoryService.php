<?php

declare(strict_types=1);

namespace App\Admin\Application\Service;


use App\Category\Domain\Entity\Category;
use App\Property\Application\Service\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class CategoryService
 *
 * @package App\Service\Admin
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class CategoryService extends AbstractService
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
    public function create(Category $category): void
    {
        $this->save($category);
        $this->clearCache('categories_count');
        $this->addFlash('success', 'message.created');
    }

    public function update(Category $category): void
    {
        $this->save($category);
        $this->addFlash('success', 'message.updated');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function remove(Category $category): void
    {
        $this->em->remove($category);
        $this->em->flush();
        $this->clearCache('categories_count');
        $this->addFlash('success', 'message.deleted');
    }

    private function save(Category $category): void
    {
        $this->em->persist($category);
        $this->em->flush();
    }
}
