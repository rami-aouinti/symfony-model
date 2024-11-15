<?php

declare(strict_types=1);

namespace App\Blog\Transport\Controller;

use App\Blog\Domain\Entity\Comment;
use App\Blog\Domain\Entity\Post;
use App\Blog\Infrastructure\Repository\PostRepository;
use App\Blog\Transport\Event\CommentCreatedEvent;
use App\Blog\Transport\Form\CommentType;
use App\Tag\Infrastructure\Repository\TagRepository;
use App\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @package App\Controller
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[Route('/blog')]
final class BlogController extends AbstractController
{
    /**
     * NOTE: For standard formats, Symfony will also automatically choose the best
     * Content-Type header for the response.
     *
     * See https://symfony.com/doc/current/routing.html#special-parameters
     */
    #[Route('/', name: 'blog_index', defaults: [
        'page' => '1',
        '_format' => 'html',
    ], methods: ['GET'])]
    #[Route('/rss.xml', name: 'blog_rss', defaults: [
        'page' => '1',
        '_format' => 'xml',
    ], methods: ['GET'])]
    #[Route(
        '/page/{page}',
        name: 'blog_index_paginated',
        requirements: [
            'page' => Requirement::POSITIVE_INT,
        ],
        defaults: [
            '_format' => 'html',
        ],
        methods: ['GET']
    )
    ]
    #[Cache(smaxage: 10)]
    public function index(
        Request $request,
        int $page,
        string $_format,
        PostRepository $posts,
        TagRepository $tags
    ): Response {
        $tag = null;

        if ($request->query->has('tag')) {
            $tag = $tags->findOneBy([
                'name' => $request->query->get('tag'),
            ]);
        }

        $latestPosts = $posts->findLatest($page, $tag);

        return $this->render('blog/index.' . $_format . '.twig', [
            'paginator' => $latestPosts,
            'tagName' => $tag?->getName(),
        ]);
    }

    #[Route(
        '/posts/{slug:post}',
        name: 'blog_post',
        requirements: [
            'slug' => Requirement::ASCII_SLUG,
        ],
        methods: ['GET']
    )
    ]
    public function postShow(Post $post): Response
    {
        return $this->render('blog/post_show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @param User                     $user
     * @param Post                     $post
     */
    #[Route(
        '/comment/{postSlug}/new',
        name: 'comment_new',
        requirements: [
            'postSlug' => Requirement::ASCII_SLUG,
        ],
        methods: ['POST']
    )
    ]
    #[IsGranted('IS_AUTHENTICATED')]
    public function commentNew(
        #[CurrentUser]
        User $user,
        Request $request,
        #[MapEntity(mapping: [
            'postSlug' => 'slug',
        ])]
        Post $post,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
    ): Response {
        $comment = new Comment();
        $comment->setAuthor($user);
        $post->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            $eventDispatcher->dispatch(new CommentCreatedEvent($comment));

            return $this->redirectToRoute(
                'blog_post',
                [
                    'slug' => $post->getSlug(),
                ],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('blog/comment_form_error.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    public function commentForm(Post $post): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('blog/_comment_form.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'blog_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        return $this->render('blog/search.html.twig', [
            'query' => (string)$request->query->get('q', ''),
        ]);
    }
}
