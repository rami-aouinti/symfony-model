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

namespace App\Media\Application\Service;

use App\Calendar\Domain\Entity\CalendarImage;
use App\Media\Domain\Entity\Image;
use App\Platform\Application\Utils\FileNameConverter;
use App\User\Application\Service\Entity\UserLoaderService;
use Exception;
use GdImage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-26)
 * @package App\Service
 */
class ImageService
{
    final public const string TEXT_NOT_GENERATED = 'admin.image.notGenerated';

    final public const string PATH_FONT = 'data/font/OpenSansCondensed-Bold.ttf';
    protected int $jpegQuality = 75;

    public function __construct(
        protected KernelInterface $appKernel,
        protected UserLoaderService $userLoaderService,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator
    ) {
    }

    /**
     * Sets JPEG quality.
     */
    public function setJpegQuality(int $jpegQuality): void
    {
        $this->jpegQuality = $jpegQuality;
    }

    /**
     * Resizes image.
     *
     * @throws Exception
     */
    public function resizeImage(string $pathSourceFull, string $pathTargetFull, int $widthResize): bool
    {
        /* Get information about image. */
        $imageInfo = $this->getImageInfo($pathSourceFull);

        /* Create image. */
        $imageSource = $this->createGdImageFromGivenPath($pathSourceFull);

        /* Get width and height */
        $width = intval($imageInfo[0]);
        $height = intval($imageInfo[1]);
        $imageType = intval($imageInfo[2]);
        $mimeType = strval($imageInfo['mime']);

        /* Calculate resized image. */
        $heightResize = intval(round($widthResize * $height / $width));

        /* Create resized image. */
        $imageResized = $this->createEmptyGdImage($widthResize, $heightResize);

        /* Copy resized image. */
        imagecopyresampled($imageResized, $imageSource, 0, 0, 0, 0, $widthResize, $heightResize, $width, $height);

        /* Create resized image. */
        $this->saveImage($imageResized, $pathTargetFull, $imageType, $mimeType);

        return true;
    }

    /**
     * Checks source image.
     *
     * @throws Exception
     */
    public function checkSourceImage(Image $image): void
    {
        $sourcePathFull = $image->getPathSource(FileNameConverter::MODE_OUTPUT_ABSOLUTE, false, $this->appKernel->getProjectDir());

        if (!file_exists($sourcePathFull)) {
            throw new Exception(sprintf('Unable to load source image "%s" (%s:%d)', $sourcePathFull, __FILE__, __LINE__));
        }
    }

    /**
     * Checks target image.
     *
     * @throws Exception
     */
    public function checkTargetImage(Image $image, ?CalendarImage $calendarImage = null): void
    {
        $targetPathFull = $image->getPathSource(FileNameConverter::MODE_OUTPUT_ABSOLUTE, false, $this->appKernel->getProjectDir(), false, null, $calendarImage);

        if (!file_exists($targetPathFull)) {
            throw new Exception(sprintf('Unable to load target image "%s" (%s:%d)', $targetPathFull, __FILE__, __LINE__));
        }
    }

    /**
     * Creates source image (unsupported method).
     *
     * @throws Exception
     * @noRector
     */
    public function createSourceImage(Image $image): void
    {
        throw new Exception(sprintf('Unsupported method (%s:%d).', __FILE__, __LINE__));
    }

    /**
     * Creates target image.
     *
     * @throws Exception
     */
    public function createTargetImage(Image $image, ?CalendarImage $calendarImage = null): void
    {
        $this->checkSourceImage($image);

        $targetPathFull = $image->getPathTarget(FileNameConverter::MODE_OUTPUT_ABSOLUTE, false, $this->appKernel->getProjectDir(), false, null, $calendarImage);

        if (!file_exists($targetPathFull)) {
            $targetPathTmp = $this->createTmpTargetImage($image, $calendarImage);

            $this->checkTargetImage($image, $calendarImage);

            $image->setPathTarget($targetPathTmp);
        }
    }

    /**
     * Checks source image with given width.
     *
     * @throws Exception
     */
    public function createSourceImageWidth(Image $image, int $width): void
    {
        $this->checkSourceImage($image);

        $sourcePathFullWidth = $image->getPathSource(FileNameConverter::MODE_OUTPUT_ABSOLUTE, false, $this->appKernel->getProjectDir(), false, $width);

        if (!file_exists($sourcePathFullWidth)) {
            $this->resizeImageWidth($image, $width);
        }
    }

    /**
     * Checks source image with given width.
     *
     * @throws Exception
     */
    public function createTargetImageWidth(Image $image, int $width, ?CalendarImage $calendarImage = null): void
    {
        $this->checkTargetImage($image, $calendarImage);

        $targetPathFullWidth = $image->getPathTarget(FileNameConverter::MODE_OUTPUT_ABSOLUTE, false, $this->appKernel->getProjectDir(), false, $width, $calendarImage);

        if (!file_exists($targetPathFullWidth)) {
            $this->resizeImageWidth($image, $width, Image::PATH_TYPE_TARGET, $calendarImage);
        }
    }

    /**
     * Checks target and source path.
     *
     * @throws Exception
     */
    public function checkPath(string $idHash): bool
    {
        $this->createSourceAndTargetFromIdHash($idHash);

        return true;
    }

    /**
     * Returns current request.
     *
     * @throws Exception
     */
    protected function getCurrentRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            throw new Exception(sprintf('Unable to get current request (%s:%d).', __FILE__, __LINE__));
        }

        return $request;
    }

    /**
     * Get entity fqcn from POST request or entity dto.
     *
     * @throws Exception
     */
    protected function isImagePosted(): bool
    {
        $request = $this->getCurrentRequest();

        if ($request->getMethod() !== Request::METHOD_POST) {
            return false;
        }

        if ($request->get('Image') === null) {
            return false;
        }

        return true;
    }

    /**
     * Creates the source and target directory from user id hash.
     *
     * @throws Exception
     */
    protected function createSourceAndTargetFromIdHash(string $idHash): void
    {
        $imageDirectory = sprintf('%s/%s/%s/%s', $this->appKernel->getProjectDir(), Image::PATH_DATA, Image::PATH_IMAGES, $idHash);

        $imageDirectorySource = sprintf('%s/%s', $imageDirectory, Image::PATH_TYPE_SOURCE);
        $imageDirectoryTarget = sprintf('%s/%s', $imageDirectory, Image::PATH_TYPE_TARGET);

        if (file_exists($imageDirectorySource) && !is_dir($imageDirectorySource)) {
            throw new Exception(sprintf('Path "%s" does exists but is not a directory (%s:%d).', $imageDirectorySource, __FILE__, __LINE__));
        }

        if (file_exists($imageDirectoryTarget) && !is_dir($imageDirectoryTarget)) {
            throw new Exception(sprintf('Path "%s" does exists but is not a directory (%s:%d).', $imageDirectorySource, __FILE__, __LINE__));
        }

        if (!file_exists($imageDirectorySource)) {
            mkdir($imageDirectorySource, 0775, true);
        }

        if (!file_exists($imageDirectoryTarget)) {
            mkdir($imageDirectoryTarget, 0775, true);
        }

        if (!file_exists($imageDirectorySource)) {
            throw new Exception(sprintf('Unable to create directory "%s" (%s:%d).', $imageDirectorySource, __FILE__, __LINE__));
        }

        if (!file_exists($imageDirectoryTarget)) {
            throw new Exception(sprintf('Unable to create directory "%s" (%s:%d).', $imageDirectoryTarget, __FILE__, __LINE__));
        }
    }

    /**
     * Gets image info.
     *
     * @return string[]|int[]
     * @throws Exception
     */
    protected function getImageInfo(string $path): array
    {
        /* Get information about image. */
        $imageInfo = getimagesize($path);

        if ($imageInfo === false) {
            throw new Exception(sprintf('Unable to get image information from "%s" (%s:%d).', $path, __FILE__, __LINE__));
        }

        return $imageInfo;
    }

    /**
     * Creates image from given path.
     *
     * @throws Exception
     */
    protected function createGdImageFromGivenPath(string $path): GdImage
    {
        $imageInfo = $this->getImageInfo($path);

        /* Create image. */
        $gdImage = match ($imageInfo[2]) {
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            default => throw new Exception(sprintf('Unsupported image type %d - %s (%s:%d)', $imageInfo[2], $imageInfo['mime'], __FILE__, __LINE__)),
        };

        if ($gdImage === false) {
            throw new Exception(sprintf('Unable to load image (%s:%d).', __FILE__, __LINE__));
        }

        return $gdImage;
    }

    /**
     * Creates an empty image from given width and height.
     *
     * @throws Exception
     */
    protected function createEmptyGdImage(int $width, int $height): GdImage
    {
        /* Create resized image. */
        $gdImage = imagecreatetruecolor($width, $height);

        if ($gdImage === false) {
            throw new Exception(sprintf('Unable to create image (%s:%d).', __FILE__, __LINE__));
        }

        return $gdImage;
    }

    /**
     * Creates the directory from given file.
     *
     * @throws Exception
     */
    protected function createDirectoryFromFile(string $path): bool
    {
        $directory = dirname($path);

        /* Check existing $directory */
        if (file_exists($directory)) {
            if (is_dir($directory)) {
                return true;
            }

            throw new Exception(sprintf('The given directory "%s" is not a directory (%s:%d)', $directory, __FILE__, __LINE__));
        }

        /* Create directory */
        mkdir($directory, 0775, true);

        /* Check directory */
        if (!file_exists($directory)) {
            throw new Exception(sprintf('Unable to create given directory "%s" (%s:%d).', $directory, __FILE__, __LINE__));
        }

        return true;
    }

    /**
     * Saves given gd image to path.
     *
     * @throws Exception
     */
    protected function saveImage(GdImage $image, string $path, int $imageType = IMAGETYPE_JPEG, string $mimeType = 'image/jpeg'): bool
    {
        /* Create directory if it does not exist */
        $this->createDirectoryFromFile($path);

        /* Create resized image. */
        $status = match ($imageType) {
            IMAGETYPE_GIF => imagegif($image, $path),
            IMAGETYPE_PNG => imagepng($image, $path),
            IMAGETYPE_JPEG => imagejpeg($image, $path, $this->jpegQuality),
            default => throw new Exception(sprintf('Unsupported image type %d - %s (%s:%d)', $imageType, $mimeType, __FILE__, __LINE__)),
        };

        /* Check image */
        if ($status === false || !file_exists($path)) {
            throw new Exception(sprintf('Unable to generate picture (%s:%d).', __FILE__, __LINE__));
        }

        return true;
    }

    /**
     * Resizes the given image with given width.
     *
     * @throws Exception
     */
    protected function resizeImageWidth(Image $image, int $widthResize, string $type = Image::PATH_TYPE_SOURCE, ?CalendarImage $calendarImage = null): string
    {
        $pathFull = $image->getPath($type, false, false, FileNameConverter::MODE_OUTPUT_ABSOLUTE, $this->appKernel->getProjectDir(), null, $calendarImage);
        $pathResizedFull = $image->getPath($type, false, false, FileNameConverter::MODE_OUTPUT_ABSOLUTE, $this->appKernel->getProjectDir(), $widthResize, $calendarImage);

        if ($pathFull === null) {
            throw new Exception(sprintf('Unexpected null value (%s:%d).', __FILE__, __LINE__));
        }

        if ($pathResizedFull === null) {
            throw new Exception(sprintf('Unexpected null value (%s:%d).', __FILE__, __LINE__));
        }

        /* Get information about image. */
        $imageInfo = $this->getImageInfo($pathFull);

        /* Create image. */
        $imageSource = $this->createGdImageFromGivenPath($pathFull);

        /* Get width and height */
        $width = intval($imageInfo[0]);
        $height = intval($imageInfo[1]);
        $imageType = intval($imageInfo[2]);
        $mimeType = strval($imageInfo['mime']);

        /* Calculate resized image. */
        $heightResize = intval(round($widthResize * $height / $width));

        /* Create resized image. */
        $imageResized = $this->createEmptyGdImage($widthResize, $heightResize);

        /* Copy resized image. */
        imagecopyresampled($imageResized, $imageSource, 0, 0, 0, 0, $widthResize, $heightResize, $width, $height);

        /* Create resized image. */
        $this->saveImage($imageResized, $pathResizedFull, $imageType, $mimeType);

        /* Return relative path */
        $path = $image->getPath($type, false, false, FileNameConverter::MODE_OUTPUT_FILE, $this->appKernel->getProjectDir(), $widthResize, $calendarImage);

        if ($path === null) {
            throw new Exception(sprintf('Unexpected null value (%s:%d).', __FILE__, __LINE__));
        }

        return $path;
    }

    /**
     * Creates a temporary target image.
     *
     * @throws Exception
     */
    protected function createTmpTargetImage(Image $image, ?CalendarImage $calendarImage = null): string
    {
        $sourcePathFull = $image->getPathSource(FileNameConverter::MODE_OUTPUT_ABSOLUTE, false, $this->appKernel->getProjectDir());
        $targetPathFullTmp = $image->getPathTarget(FileNameConverter::MODE_OUTPUT_ABSOLUTE, false, $this->appKernel->getProjectDir(), false, null, $calendarImage);

        /* Get information about image. */
        $imageInfo = $this->getImageInfo($sourcePathFull);

        /* Create image. */
        $sourceImage = $this->createGdImageFromGivenPath($sourcePathFull);

        /* Image properties. */
        $width = intval($imageInfo[0]);
        $height = intval($imageInfo[1]);
        $imageType = intval($imageInfo[2]);
        $mimeType = strval($imageInfo['mime']);
        $color = imagecolorallocate($sourceImage, 255, 255, 0);
        $font = sprintf('%s/%s', $this->appKernel->getProjectDir(), self::PATH_FONT);
        $text = $this->translator->trans(self::TEXT_NOT_GENERATED);
        $fontSize = intval($height / 20);
        $angle = intval(atan($height / $width) / M_PI * 2 * 90);

        /* Calculate box. */
        $box = imageftbbox($fontSize, $angle, $font, $text);

        if ($box === false) {
            throw new Exception(sprintf('Unable to get size (%s:%d).', __FILE__, __LINE__));
        }

        if ($color === false) {
            throw new Exception(sprintf('Unable build color (%s:%d).', __FILE__, __LINE__));
        }

        /* Get box. */
        [$left, $bottom, $right, , , $top] = $box;

        /* Calculate center. */
        $centerX = intval($width / 2);
        $centerY = intval($height / 2);

        /* Calculate offset. */
        $left_offset = intval(($right - $left) / 2);
        $top_offset = intval(($bottom - $top) / 2);

        /* Calculate position. */
        $x = $centerX - $left_offset;
        $y = $centerY + $top_offset;

        /* Add text. */
        imagettftext($sourceImage, $fontSize, $angle, $x, $y, $color, $font, $text);

        /* Create tmp image. */
        $this->saveImage($sourceImage, $targetPathFullTmp, $imageType, $mimeType);

        /* Return relative path */
        return $image->getPathTarget(FileNameConverter::MODE_OUTPUT_FILE, false, '', true);
    }
}
