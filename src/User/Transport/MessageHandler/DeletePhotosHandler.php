<?php

declare(strict_types=1);

namespace App\User\Transport\MessageHandler;

use App\Media\Application\Service\FileUploader;
use App\User\Transport\Message\DeletePhotos;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class DeletePhotosHandler
 *
 * @package App\User\Transport\MessageHandler
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsMessageHandler]
final readonly class DeletePhotosHandler
{
    public function __construct(private FileUploader $fileUploader)
    {
    }

    public function __invoke(DeletePhotos $deletePhotos): void
    {
        $photos = $deletePhotos->getProperty()->getPhotos();

        foreach ($photos as $photo) {
            $this->fileUploader->remove($photo->getPhoto());
        }
    }
}
