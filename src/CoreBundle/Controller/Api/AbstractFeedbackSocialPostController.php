<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Controller\Api;

use App\Blog\Domain\Entity\SocialPost;
use App\Blog\Domain\Entity\SocialPostFeedback;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Settings\SettingsManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsController]
abstract class AbstractFeedbackSocialPostController extends AbstractController
{
    public function __construct(
        protected Security $security,
        protected EntityManager $entityManager,
        protected SettingsManager $settingsManager
    ) {
        if ($this->settingsManager->getSetting('social.allow_social_tool') !== 'true') {
            throw new AccessDeniedException();
        }
    }

    protected function getFeedbackForCurrentUser(SocialPost $socialPost): SocialPostFeedback
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $feedback = $this->entityManager
            ->getRepository(SocialPostFeedback::class)
            ->findOneBy(
                [
                    'user' => $user,
                    'socialPost' => $socialPost,
                ]
            )
        ;

        if ($feedback === null) {
            $feedback = (new SocialPostFeedback())->setUser($user);

            $socialPost->addFeedback($feedback);
        }

        return $feedback;
    }
}
