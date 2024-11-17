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

namespace App\Platform\Application\Utils;

use App\Media\Domain\Entity\Image;
use Exception;
use JetBrains\PhpStorm\Pure;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-28)
 * @package App\Utils
 */
class FileNameConverter
{
    final public const MODE_OUTPUT_FILE = 'MODE_OUTPUT_FILE';

    final public const MODE_OUTPUT_RELATIVE = 'MODE_OUTPUT_RELATIVE';

    final public const MODE_OUTPUT_ABSOLUTE = 'MODE_OUTPUT_ABSOLUTE';

    final public const PATH_IMAGES = Image::PATH_IMAGES;

    final public const PATH_DATA = Image::PATH_DATA;
    protected string $filename;

    /**
     * @throws Exception
     */
    public function __construct(
        string $filename,
        protected string $rootPath = '',
        protected bool $test = false,
        protected string $outputMode = self::MODE_OUTPUT_FILE
    ) {
        $this->filename = $this->filterFilename($filename);
    }

    /**
     * Replaces the path by type.
     *
     * @throws Exception
     */
    public static function replacePathType(string $filename, string $type, ?string $additionalPath = null, bool $removeAdditionalPath = false): string
    {
        $search = sprintf(
            '~([a-z0-9]{40,40}/)(%s|%s|%s|%s)(/)%s~',
            Image::PATH_TYPE_SOURCE,
            Image::PATH_TYPE_TARGET,
            Image::PATH_TYPE_COMPARE,
            Image::PATH_TYPE_EXPECTED,
            $removeAdditionalPath ? '([0-9]+/)' : ''
        );

        $path = $type;

        if ($additionalPath !== null) {
            $path = sprintf('%s/%s', $path, $additionalPath);
        }

        $replace = sprintf('$1%s$3', $path);

        $filename = preg_replace($search, $replace, $filename);

        if ($filename === null) {
            throw new Exception(sprintf('Unable to replace path (%s:%d).', __FILE__, __LINE__));
        }

        return $filename;
    }

    /**
     * Returns the (raw) filename.
     *
     * @throws Exception
     */
    public function getFilename(string $type = Image::PATH_TYPE_SOURCE, ?int $width = null, bool $tmp = false, ?bool $test = null, ?string $outputMode = null, string $additionalPath = null): string
    {
        $test ??= $this->test;
        $outputMode ??= $this->outputMode;

        $filename = match ($type) {
            Image::PATH_TYPE_TARGET, Image::PATH_TYPE_EXPECTED, Image::PATH_TYPE_COMPARE => self::replacePathType($this->filename, $type, $additionalPath),
            default => $this->filename,
        };

        if ($width !== null) {
            $filename = self::addFilenameWidth($filename, $width);
        }

        if ($tmp) {
            $filename = self::addFilenameTmp($filename);
        }

        if ($test) {
            $filename = self::addFilenameTest($filename);
        }

        return match ($outputMode) {
            self::MODE_OUTPUT_FILE => $filename,
            self::MODE_OUTPUT_RELATIVE => self::addPathRelative($filename, $test),
            self::MODE_OUTPUT_ABSOLUTE => self::addPathAbsolute($filename, $this->rootPath, $test),
            default => throw new Exception(sprintf('Unsupported output mode (%s:%d).', __FILE__, __LINE__)),
        };
    }

    /**
     * Returns source filename
     *
     * @throws Exception
     */
    public function getFilenameSource(?int $width = null, bool $tmp = false, ?bool $test = null, ?string $outputMode = null, string $additionalPath = null): string
    {
        return $this->getFilename(Image::PATH_TYPE_SOURCE, $width, $tmp, $test, $outputMode, $additionalPath);
    }

    /**
     * Returns target filename.
     *
     * @throws Exception
     */
    public function getFilenameTarget(?int $width = null, bool $tmp = false, ?bool $test = null, ?string $outputMode = null, string $additionalPath = null): string
    {
        return $this->getFilename(Image::PATH_TYPE_TARGET, $width, $tmp, $test, $outputMode, $additionalPath);
    }

    /**
     * Returns target filename.
     *
     * @throws Exception
     */
    public function getFilenameExpected(?int $width = null, bool $tmp = false, ?bool $test = null, ?string $outputMode = null, string $additionalPath = null): string
    {
        return $this->getFilename(Image::PATH_TYPE_EXPECTED, $width, $tmp, $test, $outputMode, $additionalPath);
    }

    /**
     * Returns target filename.
     *
     * @throws Exception
     */
    public function getFilenameCompare(?int $width = null, bool $tmp = false, ?bool $test = null, ?string $outputMode = null, string $additionalPath = null): string
    {
        return $this->getFilename(Image::PATH_TYPE_COMPARE, $width, $tmp, $test, $outputMode, $additionalPath);
    }

    /**
     * Returns with filename.
     *
     * @throws Exception
     */
    public function getFilenameWidth(int $width, string $type = Image::PATH_TYPE_SOURCE, bool $tmp = false, ?bool $test = null, ?string $outputMode = null, string $additionalPath = null): string
    {
        return $this->getFilename($type, $width, $tmp, $test, $outputMode, $additionalPath);
    }

    /**
     * Returns tmp filename.
     *
     * @throws Exception
     */
    public function getFilenameTmp(?bool $test = null, ?string $outputMode = null, string $additionalPath = null): string
    {
        return $this->getFilename(Image::PATH_TYPE_SOURCE, null, true, $test, $outputMode, $additionalPath);
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Gets the root path.
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * Sets the root path.
     */
    public function setRootPath(string $rootPath): self
    {
        $this->rootPath = $rootPath;

        return $this;
    }

    /**
     * Gets test mode.
     */
    public function isTest(): bool
    {
        return $this->test;
    }

    /**
     * Sets test mode.
     */
    public function setTest(bool $test): self
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Gets output mode.
     */
    public function getOutputMode(): string
    {
        return $this->outputMode;
    }

    /**
     * Sets output mode.
     */
    public function setOutputMode(string $outputMode): self
    {
        $this->outputMode = $outputMode;

        return $this;
    }

    /**
     * Gets type from path.
     *
     * @throws Exception
     */
    public function getType(string $path): string
    {
        return match (true) {
            str_contains($path, sprintf('/%s/', Image::PATH_TYPE_SOURCE)) => Image::PATH_TYPE_SOURCE,
            str_contains($path, sprintf('/%s/', Image::PATH_TYPE_TARGET)) => Image::PATH_TYPE_TARGET,
            str_contains($path, sprintf('/%s/', Image::PATH_TYPE_EXPECTED)) => Image::PATH_TYPE_EXPECTED,
            str_contains($path, sprintf('/%s/', Image::PATH_TYPE_COMPARE)) => Image::PATH_TYPE_COMPARE,
            default => throw new Exception(sprintf('Unable to detect type from path "%s" (%s:%d).', $path, __FILE__, __LINE__)),
        };
    }

    /**
     * Filters the given filename.
     *
     * @throws Exception
     */
    protected function filterFilename(string $filename): string
    {
        $filename = preg_replace('~^/~', '', $filename);

        if (!is_string($filename)) {
            throw new Exception(sprintf('Unable to replace given string (%s:%d).', __FILE__, __LINE__));
        }

        return $filename;
    }

    /**
     * Adds the width part to filename
     *
     * @throws Exception
     */
    protected static function addFilenameWidth(string $filename, int $width): string
    {
        $filename = preg_replace('~\.([a-z]+)$~i', sprintf('.%d.$1', $width), $filename);

        if ($filename === null) {
            throw new Exception(sprintf('Unable to replace path (%s:%d).', __FILE__, __LINE__));
        }

        return $filename;
    }

    /**
     * Adds the tmp part to filename.
     *
     * @throws Exception
     */
    protected static function addFilenameTmp(string $filename): string
    {
        $filename = preg_replace('~\.([a-z]+)$~i', '.tmp.$1', $filename);

        if ($filename === null) {
            throw new Exception(sprintf('Unable to replace path (%s:%d).', __FILE__, __LINE__));
        }

        return $filename;
    }

    /**
     * Adds the test part to filename (still unsupported).
     *
     * @throws Exception
     */
    protected static function addFilenameTest(string $filename): string
    {
        return $filename;
    }

    /**
     * Adds relative path to filename.
     */
    protected static function addPathRelative(string $filename, bool $test = false): string
    {
        $pathRelative = sprintf($test ? '%s/tests/%s' : '%s/%s', self::PATH_DATA, self::PATH_IMAGES);

        return sprintf('%s/%s', $pathRelative, $filename);
    }

    /**
     * Adds absolute path to filename.
     */
    #[Pure]
    protected static function addPathAbsolute(string $filename, string $rootPath = '', bool $test = false): string
    {
        return sprintf('%s/%s', $rootPath, self::addPathRelative($filename, $test));
    }
}
