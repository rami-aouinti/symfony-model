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

namespace App\Platform\Transport\Controller;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class VersionController.
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-01-26)
 * @package App\Controller
 */
class VersionController
{
    final public const string PATH_VERSION = 'VERSION';

    final public const string API_ENDPOINT = '/api/v1/version';

    final public const string API_ENDPOINT_METHOD = Request::METHOD_GET;

    public function __construct(
        protected KernelInterface $appKernel
    ) {
    }

    /**
     * Returns the version as JSON.
     *
     * @throws Exception
     */
    #[Route(path: '/api/v1/version', name: 'version', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'appVersion' => $this->getAppVersion(),
            'phpVersion' => $this->getPhpVersion(),
            'symfonyVersion' => Kernel::VERSION,
        ]);
    }

    /**
     * Get app version.
     *
     * @throws Exception
     */
    protected function getAppVersion(): string
    {
        $pathVersion = sprintf('%s/%s', $this->appKernel->getProjectDir(), self::PATH_VERSION);

        if (!file_exists($pathVersion)) {
            throw new Exception(sprintf('File "%s" not found (%s:%d).', $pathVersion, __FILE__, __LINE__));
        }

        $version = file_get_contents($pathVersion);

        if ($version === false) {
            throw new Exception(sprintf('Unable to read file "%s" (%s:%d).', $pathVersion, __FILE__, __LINE__));
        }

        return trim($version);
    }

    /**
     * Returns the PHP version.
     */
    protected function getPhpVersion(): string
    {
        return PHP_VERSION;
    }
}
