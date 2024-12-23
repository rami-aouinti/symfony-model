<?php

declare(strict_types=1);

namespace App\Admin\Application\Service;

use App\Platform\Domain\Entity\Menu;
use App\Platform\Domain\Entity\Page;
use App\Property\Application\Service\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @package App\Admin\Application\Service
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PageService extends AbstractService
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
    public function create(Page $page): void
    {
        // Save page
        $this->save($page);
        $this->clearCache('pages_count');
        $this->addFlash('success', 'message.created');

        // Add a menu item
        if ($page->getShowInMenu() === true) {
            $menu = new Menu();
            $menu->setTitle($page->getTitle() ?? '');
            $menu->setLocale($page->getLocale() ?? '');
            $menu->setUrl('/info/' . ($page->getSlug() ?? ''));
            $this->save($menu);
        }
    }

    public function save(object $object): void
    {
        $this->em->persist($object);
        $this->em->flush();
    }

    public function remove(object $object): void
    {
        $this->em->remove($object);
        $this->em->flush();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(Page $page): void
    {
        // Delete page
        $this->remove($page);
        $this->clearCache('pages_count');
        $this->addFlash('success', 'message.deleted');

        // Delete a menu item
        $menu = $this->em->getRepository(Menu::class)->findOneBy([
            'url' => '/info/' . ($page->getSlug() ?? ''),
        ]);
        if ($menu !== null) {
            $this->remove($menu);
        }
    }
}
