<?php

declare(strict_types=1);

namespace App\CoreBundle\Security\Authorization\Voter;

use App\CoreBundle\Entity\User\User;
use App\Track\Domain\Entity\TrackEExercise;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<'VIEW', TrackEExercise>
 */
class TrackEExerciseVoter extends Voter
{
    public const VIEW = 'VIEW';

    public function __construct(
        private Security $security
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        $allowed = [
            self::VIEW,
        ];

        return $subject instanceof TrackEExercise && \in_array($attribute, $allowed, true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var TrackEExercise $attempt */
        $attempt = $subject;

        $course = $attempt->getCourse();
        $session = $attempt->getSession();

        if ($attempt->getUser() === $user) {
            return true;
        }

        if ($session) {
            if ($session->hasUserAsGeneralCoach($user)) {
                return true;
            }

            if ($session->hasCourseCoachInCourse($user, $course)) {
                return true;
            }
        } else {
            if ($course->hasUserAsTeacher($user)) {
                return true;
            }
        }

        return false;
    }
}
