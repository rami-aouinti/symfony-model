<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Traits;

use App\Access\Domain\Entity\AccessUrl;
use App\CoreBundle\Component\Utils\Glide;
use App\CoreBundle\Repository\Node\IllustrationRepository;
use App\CoreBundle\Repository\Node\MessageAttachmentRepository;
use App\CoreBundle\Repository\ResourceFactory;
use App\CoreBundle\Repository\ResourceNodeRepository;
use App\CoreBundle\Settings\SettingsManager;
use App\CourseBundle\Repository\CAnnouncementAttachmentRepository;
use App\CourseBundle\Repository\CAnnouncementRepository;
use App\CourseBundle\Repository\CAttendanceRepository;
use App\CourseBundle\Repository\CCalendarEventAttachmentRepository;
use App\CourseBundle\Repository\CDocumentRepository;
use App\CourseBundle\Repository\CForumAttachmentRepository;
use App\CourseBundle\Repository\CForumRepository;
use App\CourseBundle\Repository\CLpCategoryRepository;
use App\CourseBundle\Repository\CLpRepository;
use App\CourseBundle\Repository\CQuizQuestionCategoryRepository;
use App\CourseBundle\Repository\CQuizQuestionRepository;
use App\CourseBundle\Repository\CStudentPublicationCommentRepository;
use App\CourseBundle\Repository\CStudentPublicationCorrectionRepository;
use App\CourseBundle\Repository\CStudentPublicationRepository;
use App\CourseBundle\Repository\CToolRepository;
use App\LtiBundle\Repository\ExternalToolRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sylius\Bundle\SettingsBundle\Form\Factory\SettingsFormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 *
 */
trait ControllerTrait
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    public static function getSubscribedServices(): array
    {
        $services = AbstractController::getSubscribedServices();
        $services['translator'] = TranslatorInterface::class;
        $services['glide'] = Glide::class;
        // $services['chamilo_settings.form_factory.settings'] = SettingsFormFactory::class;

        $services[] = SettingsManager::class;
        $services[] = MessageAttachmentRepository::class;
        $services[] = ResourceFactory::class;
        $services[] = ResourceNodeRepository::class;
        $services[] = SettingsFormFactory::class;

        /*
            The following classes are needed in order to load the resources files when using the /r/ path
            For example: http://my.chamilomaster.net/r/agenda/event_attachments/96/download?cid=1&sid=0&gid=0
            Then the repository CCalendarEventAttachmentRepository need to be added here,
            because it was set in the tools.yml like this:
            chamilo_core.tool.agenda:
                (...)
                event_attachments:
                    repository: App\CourseBundle\Repository\CCalendarEventAttachmentRepository
        */
        $services[] = CAnnouncementRepository::class;
        $services[] = CAnnouncementAttachmentRepository::class;
        $services[] = CAttendanceRepository::class;
        $services[] = CCalendarEventAttachmentRepository::class;
        $services[] = CDocumentRepository::class;
        $services[] = CForumRepository::class;
        $services[] = CForumAttachmentRepository::class;
        $services[] = CLpRepository::class;
        $services[] = CLpCategoryRepository::class;
        $services[] = CToolRepository::class;
        $services[] = CQuizQuestionRepository::class;
        $services[] = CQuizQuestionCategoryRepository::class;
        $services[] = CStudentPublicationRepository::class;
        $services[] = CStudentPublicationCommentRepository::class;
        $services[] = CStudentPublicationCorrectionRepository::class;
        $services[] = ExternalToolRepository::class;
        $services[] = IllustrationRepository::class;

        return $services;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getRequest(): ?Request
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    public function abort(string $message = ''): void
    {
        throw new NotFoundHttpException($message);
    }

    /**
     * Translator shortcut.
     *
     * @param string $variable
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return string
     */
    public function trans(string $variable): string
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get('translator');

        return $translator->trans($variable);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getGlide(): Glide
    {
        return $this->container->get('glide');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAccessUrl(): ?AccessUrl
    {
        $urlId = $this->getRequest()->getSession()->get('access_url_id');

        return $this->container->get('doctrine')->getRepository(AccessUrl::class)->find($urlId);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getSettingsManager(): SettingsManager
    {
        return $this->container->get(SettingsManager::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return mixed|object|null
     */
    protected function getSettingsFormFactory(): mixed
    {
        return $this->container->get(SettingsFormFactory::class);
    }
}
