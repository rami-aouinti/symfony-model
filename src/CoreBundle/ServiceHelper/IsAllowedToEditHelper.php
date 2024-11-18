<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\ServiceHelper;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Settings\SettingsManager;
use App\Session\Domain\Entity\Session;
use ExtraFieldValue;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class IsAllowedToEditHelper
{
    public function __construct(
        private SettingsManager $settingsManager,
        private Security $security,
        private RequestStack $requestStack,
        private CidReqHelper $cidReqHelper,
    ) {
    }

    public function check(
        bool $tutor = false,
        bool $coach = false,
        bool $sessionCoach = false,
        bool $checkStudentView = true,
        ?Course $course = null,
        ?Session $session = null,
    ): bool {
        /** @var User $user */
        $user = $this->security->getUser();

        $studentViewIsActive = $this->requestStack->getSession()->get('studentview') === 'studentview';

        $isSessionAdminAllowedToEdit = $this->settingsManager->getSetting('session.session_admins_edit_courses_content') === 'true';

        if ($user->isAdmin() || ($user->isSessionAdmin() && $isSessionAdminAllowedToEdit)) {
            if ($checkStudentView && $studentViewIsActive) {
                return false;
            }

            return true;
        }

        $session = $session ?: $this->cidReqHelper->getSessionEntity();
        $course = $course ?: $this->cidReqHelper->getCourseEntity();

        if ($session && $course && $this->settingsManager->getSetting('session.session_courses_read_only_mode') === 'true') {
            $lockExrafieldField = (new ExtraFieldValue('course'))
                ->get_values_by_handler_and_field_variable(
                    $course->getId(),
                    'session_courses_read_only_mode'
                )
            ;

            if (!empty($lockExrafieldField['value'])) {
                return false;
            }
        }

        $isCoachAllowedToEdit = $session?->hasCoach($user) && !$studentViewIsActive;
        $sessionVisibility = $session?->setAccessVisibilityByUser($user);
        $isCourseAdmin = $user->hasRole('ROLE_CURRENT_COURSE_TEACHER') || $user->hasRole('ROLE_CURRENT_COURSE_SESSION_TEACHER');

        if (!$isCourseAdmin && $tutor) {
            $isCourseAdmin = $user->isCourseTutor($course, $session);
        }

        if (!$isCourseAdmin && $coach) {
            if ($sessionVisibility === Session::READ_ONLY) {
                $isCoachAllowedToEdit = false;
            }

            if ($this->settingsManager->getSetting('session.allow_coach_to_edit_course_session') === 'true') {
                $isCourseAdmin = $isCoachAllowedToEdit;
            }
        }

        if (!$isCourseAdmin && $sessionCoach) {
            $isCourseAdmin = $isCoachAllowedToEdit;
        }

        if ($this->settingsManager->getSetting('course.student_view_enabled') !== 'true') {
            return $isCourseAdmin;
        }

        if ($session) {
            if ($sessionVisibility === Session::READ_ONLY) {
                $isCoachAllowedToEdit = false;
            }

            $isAllowed = $this->settingsManager->getSetting('session.allow_coach_to_edit_course_session') === 'true' && $isCoachAllowedToEdit;

            if ($checkStudentView) {
                $isAllowed = $isAllowed && !$studentViewIsActive;
            }
        } elseif ($checkStudentView) {
            $isAllowed = $isCourseAdmin && !$studentViewIsActive;
        } else {
            $isAllowed = $isCourseAdmin;
        }

        return $isAllowed;
    }
}
