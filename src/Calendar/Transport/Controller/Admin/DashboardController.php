<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Calendar\Transport\Controller\Admin;

use App\Calendar\Domain\Entity\Calendar;
use App\Calendar\Domain\Entity\CalendarImage;
use App\Calendar\Domain\Entity\CalendarStyle;
use App\Calendar\Domain\Entity\Holiday;
use App\Calendar\Domain\Entity\HolidayGroup;
use App\Configuration\Application\Service\ConfigService;
use App\Event\Domain\Entity\Event;
use App\Media\Domain\Entity\Image;
use App\Platform\Application\Service\VersionService;
use App\User\Application\Service\SecurityService;
use App\User\Domain\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class DashboardController.
 * IS_AUTHENTICATED_FULLY
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-07)
 * @package App\Controller\Admin
 */
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected ConfigService $configService,
        protected VersionService $versionService,
        protected SecurityService $securityService
    ) {
    }

    /**
     * Index page of dashboard controller.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @throws Exception
     */
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('admin/page/insufficient_rights.html.twig', [
                /* title and other variables */
                'page_title' => $this->configService->getConfig(ConfigService::PARAMETER_NAME_BACKEND_TITLE_MAIN),
                'version' => $this->versionService->getVersion(),

                /* labels */
                'sign_out_label' => $this->translator->trans('admin.login.labelLogout'),

                /* content */
                'text' => 'admin.login.insufficientRights',
            ]);
        }

        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        if (!$adminUrlGenerator instanceof AdminUrlGenerator) {
            throw new Exception(sprintf('AdminUrlGenerator class expected (%s:%d).', __FILE__, __LINE__));
        }

        if ($this->securityService->isGrantedByAnAdmin()) {
            return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
        }

        return $this->redirect($adminUrlGenerator->setController(CalendarCrudController::class)->generateUrl());
    }

    /**
     * Configure dashboard.
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('PHPCalendar API');
    }

    /**
     * Configure menu items.
     *
     * @return mixed[]
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('admin.dashboard.label', 'fa fa-home');

        yield MenuItem::section('admin.section.calendar.label');
        yield MenuItem::linkToCrud('admin.section.calendar.label', 'fas fa-list', Calendar::class);
        yield MenuItem::linkToCrud('admin.section.calendarImage.label', 'fas fa-list', CalendarImage::class);
        yield MenuItem::linkToCrud('admin.section.image.label', 'fas fa-list', Image::class);
        yield MenuItem::linkToCrud('admin.section.event.label', 'fas fa-list', Event::class);

        yield MenuItem::section('admin.section.administration.label');
        if ($this->securityService->isGrantedByAnAdmin()) {
            yield MenuItem::linkToCrud('admin.section.calendarStyle.label', 'fas fa-list', CalendarStyle::class);
            yield MenuItem::linkToCrud('admin.section.holiday.label', 'fas fa-list', Holiday::class);
            yield MenuItem::linkToCrud('admin.section.holidayGroup.label', 'fas fa-list', HolidayGroup::class);
        }
        yield MenuItem::linkToCrud('admin.section.user.label', 'fas fa-list', User::class);
    }

    /**
     * Configures the user menu.
     *
     * @throws Exception
     */
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        /** @var User $user */
        $userConfig = $user->getConfig();

        $userRole = $this->translator->trans($userConfig['roleI18n']);

        return parent::configureUserMenu($user)
            ->setName(sprintf('%s - %s', $userConfig['fullName'], $userRole))
            ->displayUserName(true)
            ->setGravatarEmail(strval($user->getEmail()))
            ->addMenuItems([
                #MenuItem::linkToRoute('My Profile', 'fa fa-id-card', '...', ['...' => '...']),
                #MenuItem::linkToRoute('Settings', 'fa fa-user-cog', '...', ['...' => '...']),
                #MenuItem::section(),
                #MenuItem::linkToLogout('Logout', 'fa fa-sign-out'),
            ]);
    }
}
