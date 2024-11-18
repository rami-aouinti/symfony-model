<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Entity\Listener;

use App\CoreBundle\Entity\Skill\SkillRelUser;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Settings\SettingsManager;
use App\Message\Domain\Entity\Message;
use Display;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SkillRelUserListener
{
    public function __construct(
        protected SettingsManager $settingsManager,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        protected Security $security
    ) {
    }

    public function postPersist(SkillRelUser $skillRelUser, PostPersistEventArgs $event): void
    {
        $user = $skillRelUser->getUser();
        $skill = $skillRelUser->getSkill();

        // Notification of badge assignation
        $url = $this->router->generate(
            'badge_issued_all',
            [
                'skillId' => $skill->getId(),
                'userId' => $user->getId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = \sprintf(
            $this->translator->trans('Hi, %s. You have achieved the skill "%s". To see the details go here: %s.'),
            $user->getFirstname(),
            $skill->getTitle(),
            Display::url($url, $url)
        );

        if ($this->security->getToken() !== null) {
            /** @var User $currentUser */
            $currentUser = $this->security->getUser();
            $message = (new Message())
                ->setTitle($this->translator->trans('You have achieved a new skill.'))
                ->setContent($message)
                ->addReceiverTo($user)
                ->setSender($currentUser)
            ;

            $event->getObjectManager()->persist($message);
            $event->getObjectManager()->flush();
        }
    }
}
