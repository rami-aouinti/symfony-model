<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Repository;

use App\CoreBundle\Entity\User\User;
use App\Platform\Domain\Entity\ResourceLink;
use App\Track\Domain\Entity\TrackEDownloads;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrackEDownloadsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrackEDownloads::class);
    }

    /**
     * Save record of a resource being downloaded in track_e_downloads.
     */
    public function saveDownload(User $user, ?ResourceLink $resourceLink, string $documentUrl): int
    {
        $download = (new TrackEDownloads())
            ->setDownDocPath($documentUrl)
            ->setDownUserId($user->getId())
            ->setDownDate(new DateTime())
            ->setResourceLink($resourceLink)
        ;

        $this->_em->persist($download);
        $this->_em->flush();

        return $download->getDownId();
    }
}
