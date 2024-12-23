<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller;

use App\CoreBundle\Component\Utils\GlideAsset;
use App\CoreBundle\Repository\AssetRepository;
use App\CoreBundle\Traits\ControllerTrait;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assets')]
class AssetController
{
    use ControllerTrait;

    #[Route(path: '/{category}/{path}', methods: ['GET'], requirements: [
        'path' => '.+',
    ], name: 'chamilo_core_asset_showfile')]
    public function showFile(
        string $category,
        string $path,
        AssetRepository $assetRepository,
        GlideAsset $glide,
        RequestStack $requestStack
    ): Response {
        $filePath = $category . '/' . $path;
        $exists = $assetRepository->getFileSystem()->fileExists($filePath);

        if ($exists) {
            $fileName = basename($filePath);
            $detector = new ExtensionMimeTypeDetector();
            $mimeType = (string)$detector->detectMimeTypeFromFile($filePath);
            // If image use glide, because why not.
            if (str_contains($mimeType, 'image')) {
                $server = $glide->getServer();
                $request = $requestStack->getCurrentRequest();
                $params = $request->query->all();

                // The filter overwrites the params from GET.
                /*if (!empty($filter)) {
                    $params = $glide->getFilters()[$filter] ?? [];
                }*/

                // The image was cropped manually by the user, so we force to render this version,
                // no matter other crop parameters.
                // $crop = $resourceFile->getCrop();
                /*if (!empty($crop)) {
                    $params['crop'] = $crop;
                }*/

                return $server->getImageResponse($filePath, $params);
            }

            $response = new StreamedResponse(
                function () use ($assetRepository, $filePath): void {
                    $outputStream = fopen('php://output', 'wb');
                    $stream = $assetRepository->getFileSystem()->readStream($filePath);

                    stream_copy_to_stream($stream, $outputStream);

                    fclose($outputStream);
                    fclose($fileStream);
                }
            );
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $fileName
            );
            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Content-Type', $mimeType ?: 'application/octet-stream');

            return $response;
        }

        throw new FileNotFoundException(\sprintf('File not found: %s', $path));
    }
}
