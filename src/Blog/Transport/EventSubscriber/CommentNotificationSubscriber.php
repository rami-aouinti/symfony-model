<?php

declare(strict_types=1);

namespace App\Blog\Transport\EventSubscriber;

use App\Blog\Domain\Entity\Post;
use App\Blog\Transport\Event\CommentCreatedEvent;
use App\Platform\Application\Service\MailerService;
use App\User\Domain\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * @package App\EventSubscriber
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class CommentNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private MailerService $mailerService,
        #[Autowire('%app.notifications.email_sender%')]
        private string $sender,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
        ];
    }

    /**
     * @throws Throwable
     */
    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();

        /** @var Post $post */
        $post = $comment->getPost();

        /** @var User $author */
        $author = $post->getAuthor();

        /** @var string $emailAddress */
        $emailAddress = $author->getEmail();

        $linkToPost = $this->urlGenerator->generate('blog_post', [
            'slug' => $post->getSlug(),
            '_fragment' => 'comment_' . $comment->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $subject = $this->translator->trans('notification.comment_created');

        $body = $this->translator->trans('notification.comment_created.description', [
            'title' => $post->getTitle(),
            'link' => $linkToPost,
        ]);

        $this->mailerService->sendMail($subject, $this->sender, $emailAddress, $body);
    }
}
