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

namespace App\Platform\Application\Service;

use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-22)
 * @package App\Platform
 */
class VersionService
{
    final public const string PATH_VERSION_FILE = 'VERSION';

    final public const string PATH_REVISION_FILE = 'REVISION';

    public function __construct(
        protected KernelInterface $appKernel
    ) {
    }

    /**
     * Returns the version number from VERSION and REVISION file.
     *
     * @throws Exception
     */
    public function getVersion(): string
    {
        $versionFile = sprintf('%s/%s', $this->appKernel->getProjectDir(), self::PATH_VERSION_FILE);
        $revisionFile = sprintf('%s/%s', $this->appKernel->getProjectDir(), self::PATH_REVISION_FILE);

        if (!file_exists($versionFile)) {
            throw new Exception(sprintf('File was not found: %s (%s:%d)', $versionFile, __FILE__, __LINE__));
        }

        $versionNumber = file_get_contents($versionFile);
        $revisionNumber = null;

        if ($versionNumber === false) {
            throw new Exception(sprintf('Unable to get version file "%s" (%s:%d)', $versionFile, __FILE__, __LINE__));
        }

        $versionDate = filemtime($versionFile);

        if ($versionDate === false) {
            throw new Exception(sprintf('Unable to get date of file "%s" (%s:%d)', $versionFile, __FILE__, __LINE__));
        }

        if (file_exists($revisionFile)) {
            $revisionNumber = file_get_contents($revisionFile);

            if ($revisionNumber === false) {
                throw new Exception(sprintf('Unable to get version file "%s" (%s:%d)', $revisionFile, __FILE__, __LINE__));
            }
        }

        if ($revisionNumber !== null && $revisionNumber !== false) {
            return sprintf('v%s (%s, %s)', $versionNumber, $revisionNumber, date('Y-m-d H:m:s', $versionDate));
        }

        return sprintf('v%s (%s)', $versionNumber, date('Y-m-d H:m:s', $versionDate));
    }
}
