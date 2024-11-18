<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller;

use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Framework\Container;
use App\CoreBundle\Repository\Node\IllustrationRepository;
use App\CoreBundle\Repository\Node\UserRepository;
use App\CoreBundle\Repository\SequenceRepository;
use App\CoreBundle\Repository\TagRepository;
use App\CourseBundle\Entity\CCourseDescription;
use App\Platform\Domain\Entity\ExtraField;
use App\Platform\Domain\Entity\Tag;
use App\Sequence\Domain\Entity\SequenceResource;
use App\Session\Domain\Entity\Session;
use App\Session\Domain\Entity\SessionRelCourse;
use BuyCoursesPlugin;
use CourseDescription;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Essence\Essence;
use ExtraFieldValue;
use SessionManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use UserManager;

/**
 * Class SessionController
 *
 * @package App\CoreBundle\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[Route('/sessions')]
class SessionController extends AbstractController
{
    /**
     * @Entity("session", expr="repository.find(sid)")
     */
    #[Route(path: '/{sid}/about', name: 'chamilo_core_session_about')]
    public function about(
        Request $request,
        Session $session,
        IllustrationRepository $illustrationRepo,
        UserRepository $userRepo,
        EntityManagerInterface $em
    ): Response {
        $requestSession = $request->getSession();
        $htmlHeadXtra[] = api_get_asset('readmore-js/readmore.js');

        $sessionId = $session->getId();
        $courses = [];
        $sessionCourses = $session->getCourses();

        $fieldsRepo = $em->getRepository(ExtraField::class);

        /** @var TagRepository $tagRepo */
        $tagRepo = $em->getRepository(Tag::class);

        /** @var SequenceRepository $sequenceResourceRepo */
        $sequenceResourceRepo = $em->getRepository(SequenceResource::class);

        /** @var ExtraField $tagField */
        $tagField = $fieldsRepo->findOneBy([
            'itemType' => ExtraField::COURSE_FIELD_TYPE,
            'variable' => 'tags',
        ]);

        $courseValues = new ExtraFieldValue('course');
        $userValues = new ExtraFieldValue('user');
        $sessionValues = new ExtraFieldValue('session');

        /** @var SessionRelCourse $sessionRelCourse */
        foreach ($sessionCourses as $sessionRelCourse) {
            $sessionCourse = $sessionRelCourse->getCourse();
            $courseTags = [];

            if ($tagField !== null) {
                $courseTags = $tagRepo->getTagsByItem($tagField, $sessionCourse->getId());
            }

            $courseCoaches = $userRepo->getCoachesForSessionCourse($session, $sessionCourse);
            $coachesData = [];

            /** @var User $courseCoach */
            foreach ($courseCoaches as $courseCoach) {
                $coachData = [
                    'complete_name' => UserManager::formatUserFullName($courseCoach),
                    'image' => $illustrationRepo->getIllustrationUrl($courseCoach),
                    'diploma' => $courseCoach->getDiplomas(),
                    'openarea' => $courseCoach->getOpenarea(),
                    'extra_fields' => $userValues->getAllValuesForAnItem(
                        $courseCoach->getId(),
                        null,
                        true
                    ),
                ];

                $coachesData[] = $coachData;
            }

            $cd = new CourseDescription();
            $cd->set_course_id($sessionCourse->getId());
            $cd->set_session_id($session->getId());
            $descriptionsData = $cd->get_description_data();

            $courseDescription = [];
            $courseObjectives = [];
            $courseTopics = [];
            $courseMethodology = [];
            $courseMaterial = [];
            $courseResources = [];
            $courseAssessment = [];
            $courseCustom = [];

            if (!empty($descriptionsData)) {
                foreach ($descriptionsData as $descriptionInfo) {
                    $type = $descriptionInfo->getDescriptionType();

                    switch ($type) {
                        case CCourseDescription::TYPE_DESCRIPTION:
                            $courseDescription[] = $descriptionInfo;

                            break;
                        case CCourseDescription::TYPE_OBJECTIVES:
                            $courseObjectives[] = $descriptionInfo;

                            break;
                        case CCourseDescription::TYPE_TOPICS:
                            $courseTopics[] = $descriptionInfo;

                            break;
                        case CCourseDescription::TYPE_METHODOLOGY:
                            $courseMethodology[] = $descriptionInfo;

                            break;
                        case CCourseDescription::TYPE_COURSE_MATERIAL:
                            $courseMaterial[] = $descriptionInfo;

                            break;
                        case CCourseDescription::TYPE_RESOURCES:
                            $courseResources[] = $descriptionInfo;

                            break;
                        case CCourseDescription::TYPE_ASSESSMENT:
                            $courseAssessment[] = $descriptionInfo;

                            break;
                        case CCourseDescription::TYPE_CUSTOM:
                            $courseCustom[] = $descriptionInfo;

                            break;
                    }
                }
            }

            $courses[] = [
                'course' => $sessionCourse,
                'description' => $courseDescription,
                'image' => Container::getIllustrationRepository()->getIllustrationUrl($sessionCourse),
                'tags' => $courseTags,
                'objectives' => $courseObjectives,
                'topics' => $courseTopics,
                'methodology' => $courseMethodology,
                'material' => $courseMaterial,
                'resources' => $courseResources,
                'assessment' => $courseAssessment,
                'custom' => array_reverse($courseCustom),
                'coaches' => $coachesData,
                'extra_fields' => $courseValues->getAllValuesForAnItem(
                    $sessionCourse->getId(),
                    null,
                    true
                ),
            ];
        }

        $sessionDates = SessionManager::parseSessionDates($session, true);

        $hasRequirements = false;

        /*$sessionRequirements = $sequenceResourceRepo->getRequirements(
         * $session->getId(),
         * SequenceResource::SESSION_TYPE
         * );
         * foreach ($sessionRequirements as $sequence) {
         * if (!empty($sequence['requirements'])) {
         * $hasRequirements = true;
         * break;
         * }
         * }*/
        $plugin = BuyCoursesPlugin::create();
        $checker = $plugin->isEnabled();
        $sessionIsPremium = null;
        if ($checker) {
            $sessionIsPremium = $plugin->getItemByProduct(
                $sessionId,
                BuyCoursesPlugin::PRODUCT_TYPE_SESSION
            );
            if ($sessionIsPremium !== []) {
                $requestSession->set('SessionIsPremium', true);
                $requestSession->set('sessionId', $sessionId);
            }
        }

        $redirectToSession = (Container::getSettingsManager()->getSetting('session.allow_redirect_to_session_after_inscription_about') === 'true');
        $redirectToSession = $redirectToSession ? '?s=' . $sessionId : false;

        $coursesInThisSession = SessionManager::get_course_list_by_session_id($sessionId);
        $coursesCount = \count($coursesInThisSession);
        $redirectToSession = $coursesCount === 1 && $redirectToSession
            ? ($redirectToSession . '&cr=' . array_values($coursesInThisSession)[0]['directory'])
            : $redirectToSession;

        $essence = new Essence();

        $params = [
            'session' => $session,
            'redirect_to_session' => $redirectToSession,
            'courses' => $courses,
            'essence' => $essence,
            'session_extra_fields' => $sessionValues->getAllValuesForAnItem($session->getId(), null, true),
            'has_requirements' => $hasRequirements,
            // 'sequences' => $sessionRequirements,
            'is_premium' => $sessionIsPremium,
            'show_tutor' => api_get_setting('show_session_coach') === 'true',
            'page_url' => api_get_path(WEB_PATH) . \sprintf('sessions/%s/about/', $session->getId()),
            'session_date' => $sessionDates,
            'is_subscribed' => SessionManager::isUserSubscribedAsStudent(
                $session->getId(),
                api_get_user_id()
            ),
            'user_session_time' => SessionManager::getDayLeftInSession(
                [
                    'id' => $session->getId(),
                    'duration' => $session->getDuration(),
                ],
                api_get_user_id()
            ),
            'base_url' => $request->getSchemeAndHttpHost(),
        ];

        return $this->render('@ChamiloCore/Session/about.html.twig', $params);
    }
}
