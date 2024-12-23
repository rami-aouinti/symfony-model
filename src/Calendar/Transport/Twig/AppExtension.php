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

namespace App\Calendar\Transport\Twig;

use App\Calendar\Application\Service\UrlService;
use App\Media\Application\Service\ImageService;
use App\Media\Domain\Entity\Image;
use App\Platform\Application\Utils\FileNameConverter;
use App\Platform\Application\Utils\NamingConventionsConverter;
use App\Platform\Transport\Controller\Base\BaseController;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use DateTime;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * AppExtension class
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0.1 (2022-03-05)
 * @package App\Calendar\Domain\Entity
 */
class AppExtension extends AbstractExtension
{
    public function __construct(
        protected KernelInterface $kernel,
        protected UrlGeneratorInterface $generator,
        protected ImageService $imageService,
        protected TranslatorInterface $translator
    ) {
    }

    /**
     * Returns the TwigFilter[].
     *
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('preg_replace', $this->pregReplace(...)),
            new TwigFilter('camel_case', $this->camelCase(...)),
            new TwigFilter('path_orig', $this->getPathOrig(...)),
            new TwigFilter('path_preview', $this->getPathPreview(...)),
            new TwigFilter('image_dimensions', $this->getImageDimensions(...)),
            new TwigFilter('add_hash', $this->addHash(...)),
            new TwigFilter('check_path', $this->checkPath(...)),
            new TwigFilter('url_absolute', $this->urlAbsolute(...)),
            new TwigFilter('month_translation', $this->getMonthTranslationKey(...)),
            new TwigFilter('qr_code', $this->getQrCode(...)),
            new TwigFilter('date_event', $this->getDateEvent(...)),
        ];
    }

    /**
     * Returns the TwigFunction[].
     *
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('path_encoded', $this->pathEncoded(...)),
        ];
    }

    /**
     * TwigFilter: Twig filter preg_replace.
     *
     * @throws Exception
     */
    public function pregReplace(string $subject, string $pattern, string $replacement): string
    {
        $replaced = preg_replace($pattern, $replacement, $subject);

        if (!is_string($replaced)) {
            throw new Exception(sprintf('Unable to replace string (%s:%d).', __FILE__, __LINE__));
        }

        return $replaced;
    }

    /**
     * TwigFilter: Convert given string to camel case.
     *
     * @throws Exception
     */
    public function camelCase(string $string): string
    {
        $converter = new NamingConventionsConverter(str_replace('-', '_', $string));

        return $converter->getCamelCase();
    }

    /**
     * TwigFilter: Returns the orig path of given path.
     *
     * @throws Exception
     */
    public function getPathOrig(string $path, string $outputMode = FileNameConverter::MODE_OUTPUT_RELATIVE): string
    {
        $fileNameConverter = new FileNameConverter($path, $this->kernel->getProjectDir(), false, $outputMode);

        $type = $fileNameConverter->getType($path);

        $pathOrig = $fileNameConverter->getFilename($type);

        $pathOrigFull = $fileNameConverter->getFilename($type, null, false, null, FileNameConverter::MODE_OUTPUT_ABSOLUTE);

        /* The target image does not exists -> use the source image. */
        if (!file_exists($pathOrigFull) && $type === Image::PATH_TYPE_TARGET) {
            $pathOrig = FileNameConverter::replacePathType($pathOrig, Image::PATH_TYPE_SOURCE, null, true);
        }

        return $this->checkPath($pathOrig);
    }

    /**
     * TwigFilter: Returns the preview path of given path.
     *
     * @throws Exception
     */
    public function getPathPreview(string $path, int $width = 400, string $outputMode = FileNameConverter::MODE_OUTPUT_RELATIVE): string
    {
        $fileNameConverter = new FileNameConverter($path, $this->kernel->getProjectDir(), false, $outputMode);

        $type = $fileNameConverter->getType($path);

        $pathFull = $fileNameConverter->getFilename($type, null, false, null, FileNameConverter::MODE_OUTPUT_ABSOLUTE);

        /* The target image does not exists -> use the source image. */
        if (!file_exists($pathFull) && $type === Image::PATH_TYPE_TARGET) {
            $pathFull = FileNameConverter::replacePathType($pathFull, Image::PATH_TYPE_SOURCE, null, true);
        }

        $pathPreview = $fileNameConverter->getFilename($type, $width);
        $pathPreviewFull = $fileNameConverter->getFilename($type, $width, false, null, FileNameConverter::MODE_OUTPUT_ABSOLUTE);

        /* Resize image if image does not exist. */
        if (!file_exists($pathPreviewFull)) {
            $this->imageService->resizeImage($pathFull, $pathPreviewFull, $width);
        }

        return $this->checkPath($pathPreview);
    }

    /**
     * TwigFilter: Returns the image dimensions.
     *
     * @throws Exception
     */
    public function getImageDimensions(string $path): string
    {
        $pathFull = sprintf('%s/public/%s', $this->kernel->getProjectDir(), $path);

        $size = getimagesize($pathFull);

        if ($size === false) {
            throw new Exception(sprintf('Unable to get image size (%s:%d).', __FILE__, __LINE__));
        }

        return str_replace(['width=', 'height='], ['data-width=', 'data-height='], (string)$size[3]);
    }

    /**
     * TwigFilter: Adds hash to the end of image path.
     *
     * @throws Exception
     */
    public function addHash(string $path): string
    {
        $fullPath = $this->getFullPath($path);

        if (!file_exists($fullPath)) {
            throw new Exception(sprintf('Image "%s" was not found (%s:%d).', $path, __FILE__, __LINE__));
        }

        $md5 = md5_file($fullPath);

        if ($md5 === false) {
            throw new Exception(sprintf('Unable to calculate md5 hash from file "%s" (%s:%d).', $path, __FILE__, __LINE__));
        }

        return sprintf('%s?%s', $path, $md5);
    }

    /**
     * TwigFilter: Checks the given path and add .tmp if the file does not exists.
     *
     * @throws Exception
     */
    public function checkPath(string $path): string
    {
        $fullPath = $this->getFullPath($path);

        if (file_exists($fullPath)) {
            return $path;
        }

        throw new Exception(sprintf('Unable to find image "%s" (%s:%d).', $fullPath, __FILE__, __LINE__));
    }

    /**
     * TwigFilter: Add url extensions.
     */
    public function urlAbsolute(string $path): string
    {
        if (preg_match('~^http[s]?://~', $path)) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return sprintf('/%s', $path);
    }

    /**
     * TwigFunction: Returns encoded path.
     *
     * @param array<string, int|string> $parameters
     * @throws Exception
     */
    public function pathEncoded(string $name, array $parameters = [], bool $relative = false): string
    {
        $configName = sprintf('CONFIG_%s', strtoupper($name));

        $constantName = sprintf('%s::%s', BaseController::class, $configName);

        $config = constant($constantName);

        if ($config === null) {
            throw new Exception(sprintf('Constant name "%s" is not defined (%s:%d).', $constantName, __FILE__, __LINE__));
        }

        if (!is_array($config)) {
            throw new Exception(sprintf('Array data type expected (%s:%d).', __FILE__, __LINE__));
        }

        $encoded = UrlService::encode($config, $parameters);

        $nameEncoded = sprintf('%s_%s', $name, BaseController::KEY_NAME_ENCODED);

        $parametersEncoded = [
            BaseController::KEY_NAME_ENCODED => $encoded,
        ];

        return $this->generator->generate($nameEncoded, $parametersEncoded, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * Get month translation key.
     *
     * @throws Exception
     */
    public function getMonthTranslationKey(int $month): string
    {
        $name = match ($month) {
            0 => 'title',
            1 => 'january',
            2 => 'february',
            3 => 'march',
            4 => 'april',
            5 => 'may',
            6 => 'june',
            7 => 'july',
            8 => 'august',
            9 => 'september',
            10 => 'october',
            11 => 'november',
            12 => 'december',
            default => throw new Exception(sprintf('Unknown month (%s:%d).', __FILE__, __LINE__)),
        };

        return sprintf('admin.calendarImage.fields.month.entries.%s', $name);
    }

    /**
     * Get QrCode value.
     *
     * @throws Exception
     */
    public function getQrCode(string $url, int $qrCodeVersion = QRCode::VERSION_AUTO): string
    {
        /* Set options for qrCode */
        $options = [
            'eccLevel' => QRCode::ECC_H,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG, // Assurez-vous de produire une image PNG brute
            'version' => $qrCodeVersion,
            'addQuietzone' => true,
            'scale' => 10, // Facteur d'échelle pour la taille du QR code
            'markupDark' => '#000000', // Couleur des parties sombres
            'markupLight' => '#FFFFFF', // Couleur des parties claires (blanc)
        ];

        /* Generate QR code image data */
        $qrCodeBlob = (new QRCode(new QROptions($options)))->render($url);

        // Valider que $qrCodeBlob contient une image valide
        if (!$qrCodeBlob || !is_string($qrCodeBlob)) {
            throw new Exception(sprintf('Failed to generate QR code for URL: %s', $url));
        }

        // Convertir les données en base64
        return sprintf('data:image/png;base64,%s', base64_encode($qrCodeBlob));
    }

    /**
     * Gets formatted event date.
     *
     * @throws Exception
     */
    public function getDateEvent(DateTime|string $date): string
    {
        if (!$date instanceof DateTime) {
            $date = DateTime::createFromFormat('Y-m-d', $date);

            if (!$date instanceof DateTime) {
                throw new Exception(sprintf('Unable to parse given date (%s:%d).', __FILE__, __LINE__));
            }
        }

        $day = $date->format('j');
        $month = intval($date->format('n'));
        $year = intval($date->format('Y'));

        $monthString = $this->translator->trans($this->getMonthTranslationKey($month));

        return match ($year) {
            2100 => sprintf('%s. %s (%s)', $day, $monthString, $this->translator->trans('words.yearUnknown')),
            1970 => sprintf('%s. %s', $day, $monthString),
            default => sprintf('%s. %s %s', $day, $monthString, $year),
        };
    }

    /**
     * Returns the full path.
     */
    protected function getFullPath(string $path): string
    {
        return sprintf('%s/%s', $this->kernel->getProjectDir(), $path);
    }

    /**
     * Add tmp part to given file.
     *
     * @throws Exception
     */
    protected function addTmp(string $path): string
    {
        $path = preg_replace('~(\.[0-9]+)?(\.[a-z]+)$~i', '.tmp$1$2', $path);

        if (!is_string($path)) {
            throw new Exception(sprintf('Unable to replace string (%s:%d).', __FILE__, __LINE__));
        }

        return $path;
    }
}
