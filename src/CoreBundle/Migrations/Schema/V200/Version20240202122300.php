<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CourseBundle\Entity\CLink;
use App\CourseBundle\Repository\CLinkRepository;
use App\CourseBundle\Repository\CShortcutRepository;
use Doctrine\DBAL\Schema\Schema;
use Exception;

class Version20240202122300 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Create shortcuts for c_link entries with on_homepage = 1';
    }

    public function up(Schema $schema): void
    {
        $admin = $this->getAdmin();

        $linkRepo = $this->container->get(CLinkRepository::class);
        $shortcutRepo = $this->container->get(CShortcutRepository::class);

        $sql = 'SELECT * FROM c_link WHERE on_homepage = 1';
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();

        while ($row = $result->fetchAssociative()) {
            $linkId = $row['iid'];

            /** @var CLink $link */
            $link = $linkRepo->find($linkId);

            if (!$link) {
                error_log("Link with ID {$linkId} not found");

                continue;
            }

            $course = $link->getFirstResourceLink()->getCourse();
            $session = $link->getFirstResourceLink()->getSession();

            $shortcut = $shortcutRepo->getShortcutFromResource($link);
            if ($shortcut === null) {
                try {
                    $shortcutRepo->addShortCut($link, $admin, $course, $session);
                    error_log("Shortcut created for link ID {$linkId}");
                } catch (Exception $e) {
                    error_log("Failed to create shortcut for link ID {$linkId}: " . $e->getMessage());
                }
            } else {
                error_log("Shortcut already exists for link ID {$linkId}");
            }
        }

        $this->entityManager->flush();
    }

    public function down(Schema $schema): void
    {
    }
}
