<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity\Listener;

use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Repository\Node\UserRepository;
use App\Platform\Domain\Entity\ResourceNode;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class UserListener
 *
 * @package App\CoreBundle\Entity\Listener
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
readonly class UserListener
{
    public function __construct(
        private UserRepository $userRepository,
        private Security $security,
        private TranslatorInterface $translator
    ) {
    }

    /**
     * This code is executed when a new user is created.
     */
    public function prePersist(User $user, PrePersistEventArgs $args): void
    {
        $this->userRepository->updateCanonicalFields($user);
        $this->userRepository->updatePassword($user);

        if ($user->isSkipResourceNode()) {
            return;
        }

        if (!$user->hasResourceNode()) {
            // Check if creator is set with $resource->setCreator()
            $creator = $user->getResourceNodeCreator();
            if ($creator === null) {
                /** @var User|null $defaultCreator */
                $defaultCreator = $this->security->getUser();
                if ($defaultCreator !== null) {
                    $creator = $defaultCreator;
                } else {
                    if (!empty($user->getCreatorId())) {
                        $creator = $this->userRepository->find($user->getCreatorId());
                    }
                }
            }

            if ($creator === null) {
                throw new UserNotFoundException('User creator not found, use $resource->setCreator();');
            }

            $em = $args->getObjectManager();
            $resourceNode = (new ResourceNode())
                ->setTitle($user->getUsername())
                ->setCreator($creator)
                ->setResourceType($this->userRepository->getResourceType())
            ;
            $em->persist($resourceNode);
            $user->setResourceNode($resourceNode);
        }
    }

    /**
     * This code is executed when a user is updated.
     */
    public function preUpdate(User $user, PreUpdateEventArgs $args): void
    {
        $this->userRepository->updatePassword($user);
        $this->userRepository->updateCanonicalFields($user);
    }

    public function preRemove(User $user, PreRemoveEventArgs $eventArgs): void
    {
        $this->deleteContentAndAttachmentsFromMessages($user, $eventArgs);
    }

    private function deleteContentAndAttachmentsFromMessages(User $user, PreRemoveEventArgs $eventArgs): void
    {
        $ob = $eventArgs->getObjectManager();

        $now = api_get_utc_datetime();

        $messages = $user->getSentMessages();
        $newContent = sprintf(
            $this->translator->trans('This message was deleted when the user was removed from the platform on %s'),
            api_get_local_time($now)
        );

        foreach ($messages as $message) {
            $message->setContent($newContent);

            $attachments = $message->getAttachments();

            foreach ($attachments as $attachment) {
                $ob->remove($attachment);
            }

            $message->setSender(null);
        }
    }
}
