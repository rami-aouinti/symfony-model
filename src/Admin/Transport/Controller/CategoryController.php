<?php

declare(strict_types=1);

namespace App\Admin\Transport\Controller;

use App\Admin\Application\Service\CategoryService;
use App\Category\Domain\Entity\Category;
use App\Category\Infrastructure\Repository\CategoryRepository;
use App\Category\Transport\Form\Type\CategoryType;
use App\Platform\Transport\Controller\BaseController;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @package App\Admin\Transport\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class CategoryController extends BaseController
{
    #[Route(path: '/admin/category', name: 'admin_category')]
    public function index(Request $request, CategoryRepository $repository): Response
    {
        $categories = $repository->findAll();

        return $this->render('admin/category/index.html.twig', [
            'site' => $this->site($request),
            'categories' => $categories,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route(path: '/admin/category/new', name: 'admin_category_new')]
    public function new(Request $request, CategoryService $service): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category)
            ->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->create($category);

            /** @var ClickableInterface $button */
            $button = $form->get('saveAndCreateNew');
            if ($button->isClicked()) {
                return $this->redirectToRoute('admin_category_new');
            }

            return $this->redirectToRoute('admin_category');
        }

        return $this->render('admin/category/new.html.twig', [
            'site' => $this->site($request),
            'category' => $category,
            'form' => $form,
        ]);
    }

    /**
     * Displays a form to edit an existing Category entity.
     */
    #[Route(
        path: '/admin/category/{id}/edit',
        name: 'admin_category_edit',
        requirements: [
            'id' => Requirement::UUID,
        ],
        methods: ['GET', 'POST']
    )]
    public function edit(Request $request, Category $category, CategoryService $service): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $service->update($category);

            return $this->redirectToRoute('admin_category');
        }

        return $this->render('admin/category/edit.html.twig', [
            'site' => $this->site($request),
            'form' => $form,
        ]);
    }

    /**
     * Deletes a Category entity.
     *
     * @throws InvalidArgumentException
     */
    #[Route(
        path: '/category/{id}/delete',
        name: 'admin_category_delete',
        requirements: [
            'id' => Requirement::UUID,
        ],
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Category $category, CategoryService $service): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->getPayload()->get('token'))) {
            return $this->redirectToRoute('admin_category');
        }

        $service->remove($category);

        return $this->redirectToRoute('admin_category');
    }
}
