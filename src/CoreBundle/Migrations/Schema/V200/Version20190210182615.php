<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use Doctrine\DBAL\Schema\Schema;

class Version20190210182615 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Session changes';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('session');
        if ($table->hasColumn('position') === false) {
            $this->addSql('ALTER TABLE session ADD COLUMN position INT DEFAULT 0 NOT NULL');
        } else {
            $this->addSql('ALTER TABLE session CHANGE position position INT DEFAULT 0 NOT NULL');
        }

        $this->addSql('UPDATE session SET promotion_id = NULL WHERE promotion_id = 0 OR promotion_id NOT IN (SELECT id FROM promotion)');
        if (!$table->hasForeignKey('FK_D044D5D4139DF194')) {
            $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE SET NULL');
            $this->addSql('CREATE INDEX IDX_D044D5D4139DF194 ON session (promotion_id);');
        }

        if (!$table->hasColumn('status')) {
            $this->addSql('ALTER TABLE session ADD COLUMN status INT NOT NULL');
        } else {
            $this->addSql('ALTER TABLE session CHANGE status status INT NOT NULL');
        }

        if (!$table->hasForeignKey('FK_D044D5D4EF87E278')) {
            $this->addSql('UPDATE session SET session_admin_id = NULL WHERE session_admin_id NOT IN (SELECT id FROM user)');
            $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4EF87E278 FOREIGN KEY(session_admin_id) REFERENCES user(id);');
        }

        $this->addSql('UPDATE session_category SET date_start = NULL WHERE CAST(date_start AS CHAR(11)) = "0000-00-00"');
        $this->addSql('UPDATE session_category SET date_end = NULL WHERE CAST(date_end AS CHAR(11)) = "0000-00-00"');

        $table = $schema->getTable('session_rel_course_rel_user');

        if (!$table->hasColumn('progress')) {
            $this->addSql('ALTER TABLE session_rel_course_rel_user ADD progress INT NOT NULL');
        }

        if ($table->hasForeignKey('FK_720167E91D79BD3')) {
            $this->addSql('ALTER TABLE session_rel_course_rel_user DROP FOREIGN KEY FK_720167E91D79BD3');
            $this->addSql('ALTER TABLE session_rel_course_rel_user ADD CONSTRAINT FK_720167E91D79BD3 FOREIGN KEY (c_id) REFERENCES course (id) ON DELETE CASCADE');
        } else {
            $this->addSql('ALTER TABLE session_rel_course_rel_user ADD CONSTRAINT FK_720167E91D79BD3 FOREIGN KEY (c_id) REFERENCES course (id) ON DELETE CASCADE');
        }

        // Remove duplicates.
        $sql = 'SELECT max(id) id, session_id, c_id, user_id, status, count(*) as count
                FROM session_rel_course_rel_user
                GROUP BY session_id, c_id, user_id, status
                HAVING count > 1';
        $result = $this->connection->executeQuery($sql);
        $items = $result->fetchAllAssociative();

        foreach ($items as $item) {
            $userId = $item['user_id'];
            $sessionId = $item['session_id'];
            $courseId = $item['c_id'];
            $status = $item['status'];

            $sql = "SELECT id
                    FROM session_rel_course_rel_user
                    WHERE user_id = {$userId} AND session_id = {$sessionId} AND c_id = {$courseId} AND status = {$status}";
            $result = $this->connection->executeQuery($sql);
            $subItems = $result->fetchAllAssociative();
            $counter = 0;
            foreach ($subItems as $subItem) {
                $id = $subItem['id'];
                if ($counter === 0) {
                    $counter++;

                    continue;
                }
                $sql = "DELETE FROM session_rel_course_rel_user WHERE id = {$id}";
                $this->connection->executeQuery($sql);
                $counter++;
            }
        }

        if (!$table->hasIndex('course_session_unique')) {
            $this->addSql(' CREATE UNIQUE INDEX course_session_unique ON session_rel_course_rel_user (session_id, c_id, user_id, status);');
        }

        $table = $schema->getTable('session_rel_course');
        if (!$table->hasIndex('course_session_unique')) {
            $this->addSql('CREATE UNIQUE INDEX course_session_unique ON session_rel_course (session_id, c_id)');
        }

        $table = $schema->getTable('session_rel_user');
        if (!$table->hasIndex('session_user_unique')) {
            $this->addSql('CREATE UNIQUE INDEX session_user_unique ON session_rel_user (session_id, user_id, relation_type);');
        }

        // Move id_coach to session_rel_user
        $result = $this->connection->executeQuery('SELECT id, session_admin_id, id_coach FROM session');
        $items = $result->fetchAllAssociative();

        foreach ($items as $item) {
            $coachId = (int)$item['id_coach'];
            $adminId = (int)$item['session_admin_id'];
            $sessionId = (int)$item['id'];

            if (!empty($coachId)) {
                $sqlUser = "SELECT * FROM user
                            WHERE id = {$coachId}";
                $resultUser = $this->connection->executeQuery($sqlUser);
                $existsUser = $resultUser->fetchAllAssociative();
                if (!empty($existsUser)) {
                    $sql = "SELECT * FROM session_rel_user
                            WHERE user_id = {$coachId} AND session_id = {$sessionId} AND relation_type = 3 ";
                    $result = $this->connection->executeQuery($sql);
                    $exists = $result->fetchAllAssociative();
                    if (empty($exists)) {
                        $sql = "INSERT INTO session_rel_user (relation_type, duration, registered_at, user_id, session_id)
                                VALUES (3, 0, NOW(), {$coachId}, {$sessionId})";
                        $this->connection->executeQuery($sql);
                    }
                }
            }

            if (!empty($adminId)) {
                $sqlUser = "SELECT * FROM user
                            WHERE id = {$adminId}";
                $resultUser = $this->connection->executeQuery($sqlUser);
                $existsUser = $resultUser->fetchAllAssociative();
                if (!empty($existsUser)) {
                    $sql = "SELECT * FROM session_rel_user
                            WHERE user_id = {$adminId} AND session_id = {$sessionId} AND relation_type = 4 ";
                    $result = $this->connection->executeQuery($sql);
                    $exists = $result->fetchAllAssociative();
                    if (empty($exists)) {
                        $sql = "INSERT INTO session_rel_user (relation_type, duration, registered_at, user_id, session_id)
                                VALUES (4, 0, NOW(), {$adminId}, {$sessionId})";
                        $this->connection->executeQuery($sql);
                    }
                }
            }
        }

        $sql = 'SELECT user_id, session_id, status
                FROM session_rel_course_rel_user scu
                WHERE user_id NOT IN (SELECT user_id FROM session_rel_user WHERE session_id = scu.session_id)';
        $result = $this->connection->executeQuery($sql);
        $items = $result->fetchAllAssociative();

        foreach ($items as $item) {
            $userId = (int)$item['user_id'];
            $sessionId = (int)$item['session_id'];
            $status = (int)$item['status'];
            if (!empty($userId)) {
                $sql = "SELECT * FROM session_rel_user
                        WHERE user_id = {$userId} AND session_id = {$sessionId} AND relation_type = {$status}";
                $result = $this->connection->executeQuery($sql);
                $exists = $result->fetchAllAssociative();
                if (empty($exists)) {
                    $sql = "INSERT INTO session_rel_user (relation_type, duration, registered_at, user_id, session_id)
                            VALUES ({$status}, 0, NOW(), {$userId}, {$sessionId})";
                    $this->connection->executeQuery($sql);
                }
            }
        }

        $table = $schema->getTable('session');
        if ($table->hasForeignKey('FK_D044D5D4D1DC2CFC')) {
            $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4D1DC2CFC');
        }

        // $this->addSql('ALTER TABLE session DROP COLUMN id_coach');
        if ($table->hasForeignKey('FK_D044D5D4EF87E278')) {
            $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4EF87E278');
        }

        if ($table->hasIndex('idx_id_coach')) {
            $this->addSql('DROP INDEX idx_id_coach ON session');
        }

        if ($table->hasForeignKey('idx_id_session_admin_id')) {
            $this->addSql('DROP INDEX idx_id_session_admin_id ON session');
        }
        // $this->addSql('ALTER TABLE session DROP COLUMN session_admin_id');
    }

    public function down(Schema $schema): void
    {
    }
}
