<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CourseBundle\Entity\Attendance\CAttendance;
use App\CourseBundle\Repository\CAttendanceRepository;
use Doctrine\DBAL\Schema\Schema;

final class Version20201216110722 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_attendance';
    }

    public function up(Schema $schema): void
    {
        $attendanceRepo = $this->container->get(CAttendanceRepository::class);
        // $attendanceRepo = $container->get(CAttendanceCalendar::class);
        $courseRepo = $this->container->get(CourseRepository::class);

        $admin = $this->getAdmin();

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();
            $course = $courseRepo->find($courseId);

            // c_thematic.
            $sql = "SELECT * FROM c_attendance WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];

                /** @var CAttendance $resource */
                $resource = $attendanceRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }

                $result = $this->fixItemProperty(
                    'attendance',
                    $attendanceRepo,
                    $course,
                    $admin,
                    $resource,
                    $course
                );

                if ($result === false) {
                    continue;
                }

                $this->entityManager->persist($resource);
                $this->entityManager->flush();
            }
        }
    }
}
