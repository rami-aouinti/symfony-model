<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PartialPaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Settings\SettingsManager;
use App\CoreBundle\Tool\ToolChain;
use App\CoreBundle\Traits\CourseFromRequestTrait;
use App\CourseBundle\Entity\CTool;
use App\Platform\Domain\Entity\ResourceLink;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @template-implements ProviderInterface<CTool>
 */
final class CToolStateProvider implements ProviderInterface
{
    use CourseFromRequestTrait;

    public function __construct(
        private readonly CollectionProvider $provider,
        protected EntityManagerInterface $entityManager,
        private readonly SettingsManager $settingsManager,
        private readonly Security $security,
        private readonly ToolChain $toolChain,
        protected RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        /** @var PartialPaginatorInterface $result */
        $result = $this->provider->provide($operation, $uriVariables, $context);

        $request = $this->requestStack->getMainRequest();

        $studentView = $request ? $request->getSession()->get('studentview') : 'studentview';

        /** @var User|null $user */
        $user = $this->security->getUser();

        $isAllowToEdit = $user && ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_CURRENT_COURSE_TEACHER'));
        $isAllowToEditBack = $user && ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_CURRENT_COURSE_TEACHER'));
        $isAllowToSessionEdit = $user && ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_CURRENT_COURSE_TEACHER') || $user->hasRole('ROLE_CURRENT_COURSE_SESSION_TEACHER'));

        $allowVisibilityInSession = $this->settingsManager->getSetting('course.allow_edit_tool_visibility_in_session');
        $session = $this->getSession();

        $results = [];

        /** @var CTool $cTool */
        foreach ($result as $cTool) {
            $toolModel = $this->toolChain->getToolFromName(
                $cTool->getTool()->getTitle()
            );

            if (!$isAllowToEdit && $toolModel->getCategory() === 'admin') {
                continue;
            }

            $resourceLinks = $cTool->getResourceNode()->getResourceLinks();

            if ($session && $allowVisibilityInSession) {
                $sessionLink = $resourceLinks->findFirst(
                    fn (int $key, ResourceLink $resourceLink): bool => $resourceLink->getSession()?->getId() === $session->getId()
                );

                if ($sessionLink) {
                    // Set the session link as unique to include in repsonse
                    $resourceLinks->clear();
                    $resourceLinks->add($sessionLink);

                    $isAllowToEdit = $isAllowToSessionEdit;
                } else {
                    $isAllowToEdit = $isAllowToEditBack;
                }
            }

            if (!$isAllowToEdit || $studentView === 'studentview') {
                $notPublishedLink = $resourceLinks->first()->getVisibility() !== ResourceLink::VISIBILITY_PUBLISHED;

                if ($notPublishedLink) {
                    continue;
                }
            }

            $results[] = $cTool;
        }

        return $results;
    }
}
