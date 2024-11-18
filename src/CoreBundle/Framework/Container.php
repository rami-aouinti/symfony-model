<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Framework;

use App\CoreBundle\Component\Editor\CkEditor\CkEditor;
use App\CoreBundle\Component\Editor\Editor;
use App\CoreBundle\Repository\AssetRepository;
use App\CoreBundle\Repository\CareerRepository;
use App\CoreBundle\Repository\CourseCategoryRepository;
use App\CoreBundle\Repository\ExtraFieldOptionsRepository;
use App\CoreBundle\Repository\ExtraFieldRepository;
use App\CoreBundle\Repository\GradeBookCategoryRepository;
use App\CoreBundle\Repository\GradebookCertificateRepository;
use App\CoreBundle\Repository\LanguageRepository;
use App\CoreBundle\Repository\LegalRepository;
use App\CoreBundle\Repository\MessageRepository;
use App\CoreBundle\Repository\Node\AccessUrlRepository;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CoreBundle\Repository\Node\IllustrationRepository;
use App\CoreBundle\Repository\Node\MessageAttachmentRepository;
use App\CoreBundle\Repository\Node\PersonalFileRepository;
use App\CoreBundle\Repository\Node\SocialPostAttachmentRepository;
use App\CoreBundle\Repository\Node\TicketMessageAttachmentRepository;
use App\CoreBundle\Repository\Node\UsergroupRepository;
use App\CoreBundle\Repository\Node\UserRepository;
use App\CoreBundle\Repository\PromotionRepository;
use App\CoreBundle\Repository\ResourceNodeRepository;
use App\CoreBundle\Repository\SequenceRepository;
use App\CoreBundle\Repository\SequenceResourceRepository;
use App\CoreBundle\Repository\SessionRepository;
use App\CoreBundle\Repository\SkillRepository;
use App\CoreBundle\Repository\SocialPostRepository;
use App\CoreBundle\Repository\SysAnnouncementRepository;
use App\CoreBundle\Repository\TagRepository;
use App\CoreBundle\Repository\TrackEDownloadsRepository;
use App\CoreBundle\Repository\TrackEExerciseRepository;
use App\CoreBundle\Repository\TrackELoginRecordRepository;
use App\CoreBundle\Serializer\UserToJsonNormalizer;
use App\CoreBundle\ServiceHelper\ContainerHelper;
use App\CoreBundle\ServiceHelper\ThemeHelper;
use App\CoreBundle\Settings\SettingsManager;
use App\CoreBundle\Tool\ToolChain;
use App\CourseBundle\Repository\CAnnouncementAttachmentRepository;
use App\CourseBundle\Repository\CAnnouncementRepository;
use App\CourseBundle\Repository\CAttendanceRepository;
use App\CourseBundle\Repository\CCalendarEventAttachmentRepository;
use App\CourseBundle\Repository\CCalendarEventRepository;
use App\CourseBundle\Repository\CCourseDescriptionRepository;
use App\CourseBundle\Repository\CDocumentRepository;
use App\CourseBundle\Repository\CForumAttachmentRepository;
use App\CourseBundle\Repository\CForumCategoryRepository;
use App\CourseBundle\Repository\CForumPostRepository;
use App\CourseBundle\Repository\CForumRepository;
use App\CourseBundle\Repository\CForumThreadRepository;
use App\CourseBundle\Repository\CGlossaryRepository;
use App\CourseBundle\Repository\CGroupCategoryRepository;
use App\CourseBundle\Repository\CGroupRepository;
use App\CourseBundle\Repository\CLinkCategoryRepository;
use App\CourseBundle\Repository\CLinkRepository;
use App\CourseBundle\Repository\CLpCategoryRepository;
use App\CourseBundle\Repository\CLpItemRepository;
use App\CourseBundle\Repository\CLpRepository;
use App\CourseBundle\Repository\CNotebookRepository;
use App\CourseBundle\Repository\CQuizCategoryRepository;
use App\CourseBundle\Repository\CQuizQuestionCategoryRepository;
use App\CourseBundle\Repository\CQuizQuestionRepository;
use App\CourseBundle\Repository\CQuizRepository;
use App\CourseBundle\Repository\CShortcutRepository;
use App\CourseBundle\Repository\CStudentPublicationAssignmentRepository;
use App\CourseBundle\Repository\CStudentPublicationCommentRepository;
use App\CourseBundle\Repository\CStudentPublicationCorrectionRepository;
use App\CourseBundle\Repository\CStudentPublicationRepository;
use App\CourseBundle\Repository\CSurveyInvitationRepository;
use App\CourseBundle\Repository\CSurveyQuestionRepository;
use App\CourseBundle\Repository\CSurveyRepository;
use App\CourseBundle\Repository\CThematicAdvanceRepository;
use App\CourseBundle\Repository\CThematicPlanRepository;
use App\CourseBundle\Repository\CThematicRepository;
use App\CourseBundle\Repository\CToolIntroRepository;
use App\CourseBundle\Repository\CWikiRepository;
use App\CourseBundle\Settings\SettingsCourseManager;
use App\LtiBundle\Repository\ExternalToolRepository;
use Database;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface as HttpSessionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use UnitEnum;

/**
 * Symfony services for the legacy Chamilo code.
 */
class Container
{
    public static ?ContainerInterface $container = null;
    public static ?Request $request = null;
    // For legacy, to get the translator service is necessary get it by Container::$container->get('translator')
    public static ?Translator $translator = null;
    public static Environment $twig;
    public static ?Session $session = null;
    public static string $legacyTemplate = '@ChamiloCore/Layout/layout_one_col.html.twig';

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function getParameter(string $parameter): array|bool|float|int|string|UnitEnum|null
    {
        if (self::$container->hasParameter($parameter)) {
            return self::$container->getParameter($parameter);
        }

        return false;
    }

    public static function getLegacyHelper(): ContainerHelper
    {
        return self::$container->get(ContainerHelper::class);
    }

    public static function getEnvironment(): string
    {
        return self::getLegacyHelper()->getKernel()->getEnvironment();
    }

    public static function getLogDir(): string
    {
        return self::getLegacyHelper()->getKernel()->getLogDir();
    }

    public static function getCacheDir(): string
    {
        return self::getLegacyHelper()->getKernel()->getCacheDir() . '/';
    }

    public static function getProjectDir(): string
    {
        if (self::$container !== null) {
            return self::getLegacyHelper()->getKernel()->getProjectDir() . '/';
        }

        return str_replace('\\', '/', realpath(__DIR__ . '/../../../')) . '/';
    }

    public static function isInstalled(): bool
    {
        return self::getLegacyHelper()->getKernel()->isInstalled();
    }

    public static function getMessengerBus(): MessageBusInterface
    {
        return self::getLegacyHelper()->getMessengerBus();
    }

    public static function getTwig(): Environment
    {
        return self::$twig;
    }

    /**
     * @return Editor
     */
    public static function getHtmlEditor()
    {
        return self::$container->get(CkEditor::class);
    }

    /**
     * @return null|Request
     */
    public static function getRequest()
    {
        if (self::$container === null) {
            return null;
        }

        if (!empty(self::$request)) {
            return self::$request;
        }

        return self::$container->get('request_stack')->getCurrentRequest();
    }

    public static function setRequest(Request $request): void
    {
        self::$request = $request;
    }

    public static function getSession(): bool|HttpSessionInterface|Session|null
    {
        if (self::$session !== null) {
            return self::$session;
        }

        if (self::$container !== null) {
            return self::$container->get('request_stack')->getSession();
        }

        return false;
    }

    public static function setSession(Session $session): void
    {
        self::$session = $session;
    }

    public static function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        return self::getLegacyHelper()->getAuthorizationChecker();
    }

    public static function getTokenStorage(): TokenStorageInterface|TokenStorage
    {
        return self::getLegacyHelper()->getTokenStorage();
    }

    public static function getMailer(): Mailer
    {
        return self::$container->get(Mailer::class);
    }

    public static function getSettingsManager(): SettingsManager
    {
        return self::$container->get(SettingsManager::class);
    }

    public static function getCourseSettingsManager(): SettingsCourseManager
    {
        return self::$container->get(SettingsCourseManager::class);
    }

    /**
     * @return EntityManager
     */
    public static function getEntityManager()
    {
        return Database::getManager();
    }

    public static function getAssetRepository(): AssetRepository
    {
        return self::$container->get(AssetRepository::class);
    }

    public static function getResourceNodeRepository(): ResourceNodeRepository
    {
        return self::$container->get(ResourceNodeRepository::class);
    }

    public static function getAttendanceRepository(): CAttendanceRepository
    {
        return self::$container->get(CAttendanceRepository::class);
    }

    public static function getAnnouncementRepository(): CAnnouncementRepository
    {
        return self::$container->get(CAnnouncementRepository::class);
    }

    public static function getAccessUrlRepository(): AccessUrlRepository
    {
        return self::$container->get(AccessUrlRepository::class);
    }

    public static function getAnnouncementAttachmentRepository(): CAnnouncementAttachmentRepository
    {
        return self::$container->get(CAnnouncementAttachmentRepository::class);
    }

    public static function getTicketMessageAttachmentRepository(): TicketMessageAttachmentRepository
    {
        return self::$container->get(TicketMessageAttachmentRepository::class);
    }

    public static function getSocialPostAttachmentRepository(): SocialPostAttachmentRepository
    {
        return self::$container->get(SocialPostAttachmentRepository::class);
    }

    public static function getCourseRepository(): CourseRepository
    {
        return self::$container->get(CourseRepository::class);
    }

    public static function getCareerRepository(): CareerRepository
    {
        return self::$container->get(CareerRepository::class);
    }

    public static function getCourseCategoryRepository(): CourseCategoryRepository
    {
        return self::$container->get(CourseCategoryRepository::class);
    }

    public static function getCourseDescriptionRepository(): CCourseDescriptionRepository
    {
        return self::$container->get(CCourseDescriptionRepository::class);
    }

    public static function getCalendarEventRepository(): CCalendarEventRepository
    {
        return self::$container->get(CCalendarEventRepository::class);
    }

    public static function getCalendarEventAttachmentRepository(): CCalendarEventAttachmentRepository
    {
        return self::$container->get(CCalendarEventAttachmentRepository::class);
    }

    public static function getDocumentRepository(): CDocumentRepository
    {
        return self::$container->get(CDocumentRepository::class);
    }

    public static function getQuizCategoryRepository(): CQuizCategoryRepository
    {
        return self::$container->get(CQuizCategoryRepository::class);
    }

    public static function getExternalToolRepository(): ExternalToolRepository
    {
        return self::$container->get(ExternalToolRepository::class);
    }

    public static function getExtraFieldRepository(): ExtraFieldRepository
    {
        return self::$container->get(ExtraFieldRepository::class);
    }

    public static function getExtraFieldOptionsRepository(): ExtraFieldOptionsRepository
    {
        return self::$container->get(ExtraFieldOptionsRepository::class);
    }

    public static function getGlossaryRepository(): CGlossaryRepository
    {
        return self::$container->get(CGlossaryRepository::class);
    }

    public static function getGradeBookCategoryRepository(): GradeBookCategoryRepository
    {
        return self::$container->get(GradeBookCategoryRepository::class);
    }

    public static function getGradeBookCertificateRepository(): GradebookCertificateRepository
    {
        return self::$container->get(GradebookCertificateRepository::class);
    }

    public static function getGroupRepository(): CGroupRepository
    {
        return self::$container->get(CGroupRepository::class);
    }

    public static function getGroupCategoryRepository(): CGroupCategoryRepository
    {
        return self::$container->get(CGroupCategoryRepository::class);
    }

    public static function getForumRepository(): CForumRepository
    {
        return self::$container->get(CForumRepository::class);
    }

    public static function getForumCategoryRepository(): CForumCategoryRepository
    {
        return self::$container->get(CForumCategoryRepository::class);
    }

    public static function getForumPostRepository(): CForumPostRepository
    {
        return self::$container->get(CForumPostRepository::class);
    }

    public static function getForumAttachmentRepository(): CForumAttachmentRepository
    {
        return self::$container->get(CForumAttachmentRepository::class);
    }

    public static function getForumThreadRepository(): CForumThreadRepository
    {
        return self::$container->get(CForumThreadRepository::class);
    }

    public static function getIllustrationRepository(): IllustrationRepository
    {
        return self::$container->get(IllustrationRepository::class);
    }

    public static function getQuizRepository(): CQuizRepository
    {
        return self::$container->get(CQuizRepository::class);
    }

    public static function getQuestionRepository(): CQuizQuestionRepository
    {
        return self::$container->get(CQuizQuestionRepository::class);
    }

    public static function getQuestionCategoryRepository(): CQuizQuestionCategoryRepository
    {
        return self::$container->get(CQuizQuestionCategoryRepository::class);
    }

    public static function getLanguageRepository(): LanguageRepository
    {
        return self::$container->get(LanguageRepository::class);
    }

    public static function getLinkRepository(): CLinkRepository
    {
        return self::$container->get(CLinkRepository::class);
    }

    public static function getLinkCategoryRepository(): CLinkCategoryRepository
    {
        return self::$container->get(CLinkCategoryRepository::class);
    }

    public static function getLpRepository(): CLpRepository
    {
        return self::$container->get(CLpRepository::class);
    }

    public static function getLpItemRepository(): CLpItemRepository
    {
        return self::$container->get(CLpItemRepository::class);
    }

    public static function getLpCategoryRepository(): CLpCategoryRepository
    {
        return self::$container->get(CLpCategoryRepository::class);
    }

    public static function getMessageRepository(): MessageRepository
    {
        return self::$container->get(MessageRepository::class);
    }

    public static function getMessageAttachmentRepository(): MessageAttachmentRepository
    {
        return self::$container->get(MessageAttachmentRepository::class);
    }

    public static function getNotebookRepository(): CNotebookRepository
    {
        return self::$container->get(CNotebookRepository::class);
    }

    public static function getPersonalFileRepository(): PersonalFileRepository
    {
        return self::$container->get(PersonalFileRepository::class);
    }

    public static function getPromotionRepository(): PromotionRepository
    {
        return self::$container->get(PromotionRepository::class);
    }

    public static function getUserRepository(): UserRepository
    {
        return self::$container->get(UserRepository::class);
    }

    public static function getUsergroupRepository(): UsergroupRepository
    {
        return self::$container->get(UsergroupRepository::class);
    }

    public static function getUserToJsonNormalizer(): UserToJsonNormalizer
    {
        return self::$container->get(UserToJsonNormalizer::class);
    }

    public static function getShortcutRepository(): CShortcutRepository
    {
        return self::$container->get(CShortcutRepository::class);
    }

    public static function getStudentPublicationRepository(): CStudentPublicationRepository
    {
        return self::$container->get(CStudentPublicationRepository::class);
    }

    public static function getStudentPublicationAssignmentRepository(): CStudentPublicationAssignmentRepository
    {
        return self::$container->get(CStudentPublicationAssignmentRepository::class);
    }

    public static function getStudentPublicationCommentRepository(): CStudentPublicationCommentRepository
    {
        return self::$container->get(CStudentPublicationCommentRepository::class);
    }

    public static function getStudentPublicationCorrectionRepository(): CStudentPublicationCorrectionRepository
    {
        return self::$container->get(CStudentPublicationCorrectionRepository::class);
    }

    public static function getSequenceResourceRepository(): SequenceResourceRepository
    {
        return self::$container->get(SequenceResourceRepository::class);
    }

    public static function getSequenceRepository(): SequenceRepository
    {
        return self::$container->get(SequenceRepository::class);
    }

    public static function getSessionRepository(): SessionRepository
    {
        return self::$container->get(SessionRepository::class);
    }

    public static function getSkillRepository(): SkillRepository
    {
        return self::$container->get(SkillRepository::class);
    }

    public static function getSurveyRepository(): CSurveyRepository
    {
        return self::$container->get(CSurveyRepository::class);
    }

    public static function getSurveyInvitationRepository(): CSurveyInvitationRepository
    {
        return self::$container->get(CSurveyInvitationRepository::class);
    }

    public static function getSurveyQuestionRepository(): CSurveyQuestionRepository
    {
        return self::$container->get(CSurveyQuestionRepository::class);
    }

    public static function getSysAnnouncementRepository(): SysAnnouncementRepository
    {
        return self::$container->get(SysAnnouncementRepository::class);
    }

    public static function getTagRepository(): TagRepository
    {
        return self::$container->get(TagRepository::class);
    }

    public static function getThematicRepository(): CThematicRepository
    {
        return self::$container->get(CThematicRepository::class);
    }

    public static function getThematicPlanRepository(): CThematicPlanRepository
    {
        return self::$container->get(CThematicPlanRepository::class);
    }

    public static function getThematicAdvanceRepository(): CThematicAdvanceRepository
    {
        return self::$container->get(CThematicAdvanceRepository::class);
    }

    public static function getTrackEExerciseRepository(): TrackEExerciseRepository
    {
        return self::$container->get(TrackEExerciseRepository::class);
    }

    public static function getTrackEDownloadsRepository(): TrackEDownloadsRepository
    {
        return self::$container->get(TrackEDownloadsRepository::class);
    }

    public static function getWikiRepository(): CWikiRepository
    {
        return self::$container->get(CWikiRepository::class);
    }

    public static function getToolIntroRepository(): CToolIntroRepository
    {
        return self::$container->get(CToolIntroRepository::class);
    }

    public static function getLegalRepository(): LegalRepository
    {
        return self::$container->get(LegalRepository::class);
    }

    public static function getFormFactory(): FormFactory
    {
        return self::$container->get('form.factory');
    }

    public static function addFlash(string $message, string $type = 'success'): void
    {
        $type = match ($type) {
            'confirmation', 'confirm' => 'success',
            default => 'info',
        };

        $session = self::getSession();

        if ($session instanceof Session) {
            $session->getFlashBag()->add($type, $message);
        }
    }

    public static function getRouter(): Router
    {
        return self::$container->get('router');
    }

    public static function getToolChain(): ToolChain
    {
        return self::$container->get(ToolChain::class);
    }

    public static function setLegacyServices(ContainerInterface $container): void
    {
        $doctrine = $container->get('doctrine');
        Database::setConnection($doctrine->getConnection());

        /** @var EntityManager $em */
        $em = $doctrine->getManager();
        Database::setManager($em);
    }

    public static function getSocialPostRepository(): SocialPostRepository
    {
        return self::$container->get(SocialPostRepository::class);
    }

    public static function getTrackELoginRecordRepository(): TrackELoginRecordRepository
    {
        return self::$container->get(TrackELoginRecordRepository::class);
    }

    public static function getThemeHelper(): ThemeHelper
    {
        return self::$container->get(ThemeHelper::class);
    }
}
