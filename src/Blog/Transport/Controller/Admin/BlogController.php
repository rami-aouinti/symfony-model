<?php

declare(strict_types=1);

namespace App\Blog\Transport\Controller\Admin;

use App\Blog\Application\Security\PostVoter;
use App\Blog\Domain\Entity\Post;
use App\Blog\Infrastructure\Repository\PostRepository;
use App\Blog\Transport\Form\PostType;
use App\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @package App\Blog\Transport\Controller\Admin
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[Route('/admin/post')]
#[IsGranted(User::ROLE_ADMIN)]
final class BlogController extends AbstractController
{
    /**
     * @param User $user
     */
    #[Route('/', name: 'admin_index', methods: ['GET'])]
    #[Route('/', name: 'admin_post_index', methods: ['GET'])]
    public function index(
        #[CurrentUser]
        User $user,
        PostRepository $posts,
    ): Response {
        $authorPosts = $posts->findBy([
            'author' => $user,
        ], [
            'publishedAt' => 'DESC',
        ]);

        return $this->render('admin/blog/index.html.twig', [
            'posts' => $authorPosts,
        ]);
    }

    /**
     * @param User $user
     */
    #[Route('/new', name: 'admin_post_new', methods: ['GET', 'POST'])]
    public function new(
        #[CurrentUser]
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $post = new Post();
        $post->setAuthor($user);

        $form = $this->createForm(PostType::class, $post)
            ->add('saveAndCreateNew', SubmitType::class)
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'post.created_successfully');

            /** @var SubmitButton $submit */
            $submit = $form->get('saveAndCreateNew');

            if ($submit->isClicked()) {
                return $this->redirectToRoute('admin_post_new', [], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('admin_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/blog/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * Finds and displays a Post entity.
     */
    #[Route('/{id:post}', name: 'admin_post_show', requirements: [
        'id' => Requirement::UUID,
    ], methods: ['GET'])]
    public function show(Post $post): Response
    {
        $this->denyAccessUnlessGranted(
            PostVoter::SHOW,
            $post,
            'Posts can only be shown to their authors.'
        );

        return $this->render('admin/blog/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * Displays a form to edit an existing Post entity.
     */
    #[Route(
        '/{id:post}/edit',
        name: 'admin_post_edit',
        requirements: [
            'id' => Requirement::UUID,
        ],
        methods: ['GET', 'POST']
    )
    ]
    #[IsGranted('edit', subject: 'post', message: 'Posts can only be edited by their authors.')]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'post.updated_successfully');

            return $this->redirectToRoute(
                'admin_post_edit',
                [
                    'id' => $post->getId(),
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('admin/blog/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * Deletes a Post entity.
     */
    #[Route(
        '/{id:post}/delete',
        name: 'admin_post_delete',
        requirements: [
            'id' => Requirement::UUID,
        ],
        methods: ['POST']
    )
    ]
    #[IsGranted('delete', subject: 'post')]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        /** @var string|null $token */
        $token = $request->getPayload()->get('token');

        if (!$this->isCsrfTokenValid('delete', $token)) {
            return $this->redirectToRoute('admin_post_index', [], Response::HTTP_SEE_OTHER);
        }

        $post->getTags()->clear();

        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'post.deleted_successfully');

        return $this->redirectToRoute('admin_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
