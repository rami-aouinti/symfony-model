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

namespace App\Platform\Application\Utils\Version;

use App\Media\Infrastructure\Container\File;
use App\Tests\Unit\Utils\Version\VersionTest;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 * @link VersionTest
 */
class Version
{
    final public const string VALUE_LICENSE = 'Copyright (c) 2022 Björn Hempel';

    final public const array VALUE_AUTHORS = [
        'Björn Hempel <bjoern@hempel.li>',
    ];

    final public const string PATH_VERSION = 'VERSION';

    final public const string INDEX_VERSION = 'version';

    final public const string INDEX_LICENSE = 'license';

    final public const string INDEX_AUTHORS = 'authors';

    public function __construct(
        protected string $rootDir
    ) {
    }

    /**
     * Returns the version of this application.
     */
    public function getVersion(): string
    {
        return (new File(sprintf('%s/%s', $this->rootDir, self::PATH_VERSION)))->getContentAsTextTrim();
    }

    /**
     * Returns the license of this application.
     */
    public function getLicense(): string
    {
        return self::VALUE_LICENSE;
    }

    /**
     * Returns the author of this application.
     *
     * @return array<int, string>
     */
    public function getAuthors(): array
    {
        return self::VALUE_AUTHORS;
    }

    /**
     * Returns all information.
     *
     * @return array{version: string, license: string, authors: array<int, string>}
     */
    public function getAll(): array
    {
        return [
            self::INDEX_VERSION => $this->getVersion(),
            self::INDEX_LICENSE => $this->getLicense(),
            self::INDEX_AUTHORS => $this->getAuthors(),
        ];
    }
}
