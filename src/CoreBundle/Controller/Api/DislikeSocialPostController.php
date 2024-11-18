<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Controller\Api;

use App\Blog\Domain\Entity\SocialPost;
use App\CoreBundle\Settings\SettingsManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DislikeSocialPostController extends AbstractFeedbackSocialPostController
{
    public function __construct(Security $security, EntityManager $entityManager, SettingsManager $settingsManager)
    {
        parent::__construct($security, $entityManager, $settingsManager);

        if ($this->settingsManager->getSetting('social.disable_dislike_option', true) !== 'false') {
            throw new AccessDeniedException();
        }
    }

    public function __invoke(SocialPost $socialPost): SocialPost
    {
        $feedback = $this->getFeedbackForCurrentUser($socialPost);
        $feedback
            ->setLiked(false)
            ->setDisliked(
                !$feedback->isDisliked()
            )
        ;

        $this->entityManager->persist($feedback);
        $this->entityManager->flush();

        return $socialPost;
    }
}
