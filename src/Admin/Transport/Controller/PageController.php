<?php

declare(strict_types=1);

namespace App\Admin\Transport\Controller;

use App\Admin\Application\Service\PageService;
use App\Platform\Domain\Entity\Page;
use App\Platform\Infrastructure\Repository\PageRepository;
use App\Platform\Transport\Controller\BaseController;
use App\Platform\Transport\Form\Type\PageType;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @package App\Admin\Transport\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PageController extends BaseController
{
    #[Route(path: '/admin/page', name: 'admin_page', defaults: [
        'page' => 1,
    ], methods: ['GET'])]
    public function index(Request $request, PageRepository $repository): Response
    {
        // Get pages
        $pages = $repository->findLatest($request);

        return $this->render('admin/page/index.html.twig', [
            'site' => $this->site($request),
            'pages' => $pages,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route(path: '/admin/page/new', name: 'admin_page_new')]
    public function new(Request $request, PageService $pageService): Response
    {
        $page = new Page();
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pageService->create($page);

            return $this->redirectToRoute('page', [
                'slug' => $page->getSlug(),
            ]);
        }

        return $this->render('admin/page/new.html.twig', [
            'site' => $this->site($request),
            'page' => $page,
            'form' => $form,
        ]);
    }

    /**
     * Displays a form to edit an existing Page entity.
     */
    #[Route(
        path: '/admin/page/{id}/edit',
        name: 'admin_page_edit',
        requirements: [
            'id' => Requirement::POSITIVE_INT,
        ],
        methods: ['GET', 'POST']
    )]
    public function edit(Request $request, Page $page): Response
    {
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->getManager()->flush();
            $this->addFlash('success', 'message.updated');

            return $this->redirectToRoute('page', [
                'slug' => $page->getSlug(),
            ]);
        }

        return $this->render('admin/page/edit.html.twig', [
            'site' => $this->site($request),
            'form' => $form,
        ]);
    }

    /**
     * Deletes a Page entity.
     *
     * @throws InvalidArgumentException
     */
    #[Route(
        path: '/page/{id}/delete',
        name: 'admin_page_delete',
        requirements: [
            'id' => Requirement::POSITIVE_INT,
        ],
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Page $page, PageService $pageService): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->getPayload()->get('token'))) {
            return $this->redirectToRoute('admin_page');
        }

        $pageService->delete($page);

        return $this->redirectToRoute('admin_page');
    }
}
