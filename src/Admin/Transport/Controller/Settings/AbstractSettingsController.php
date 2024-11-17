<?php

declare(strict_types=1);

namespace App\Admin\Transport\Controller\Settings;

use App\Admin\Application\Service\SettingsService;
use App\Configuration\Infrastructure\Repository\SettingsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class AbstractSettingsController
 *
 * @package App\Admin\Transport\Controller\Settings
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
abstract class AbstractSettingsController extends AbstractController
{
    protected array $settings;

    public function __construct(
        protected SettingsRepository $repository,
        protected SettingsService $service
    ) {
        $this->settings = $this->repository->findAllAsArray();
    }
}
