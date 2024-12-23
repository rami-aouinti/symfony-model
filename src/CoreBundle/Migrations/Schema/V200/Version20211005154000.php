<?php

declare(strict_types=1);

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Ticket\TicketMessageAttachment;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\TicketMessageAttachmentRepository;
use App\CoreBundle\Repository\Node\UserRepository;
use App\Kernel;
use Doctrine\DBAL\Schema\Schema;

class Version20211005154000 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate ticket attachment files';
    }

    public function up(Schema $schema): void
    {
        /** @var Kernel $kernel */
        $kernel = $this->container->get('kernel');
        $rootPath = $kernel->getProjectDir();

        $attachmentRepo = $this->container->get(TicketMessageAttachmentRepository::class);
        $userRepo = $this->container->get(UserRepository::class);

        $sql = 'SELECT * FROM ticket_message_attachments ORDER BY id';

        $result = $this->connection->executeQuery($sql);
        $items = $result->fetchAllAssociative();

        foreach ($items as $item) {
            /** @var TicketMessageAttachment $messageAttachment */
            $messageAttachment = $attachmentRepo->find($item['id']);

            if ($messageAttachment->hasResourceNode()) {
                continue;
            }

            $ticket = $messageAttachment->getTicket();
            $user = $userRepo->find($item['sys_insert_user_id']);

            if ($user === null) {
                continue;
            }

            $attachmentRepo->addResourceNode($messageAttachment, $user, $user);

            if ($ticket->getAssignedLastUser() !== null) {
                $messageAttachment->addUserLink($ticket->getAssignedLastUser());
            }

            $attachmentRepo->create($messageAttachment);

            $filePath = $this->getUpdateRootPath() . '/app/upload/ticket_attachment/' . $item['path'];
            error_log('MIGRATIONS :: $filePath -- ' . $filePath . ' ...');
            $this->addLegacyFileToResource($filePath, $attachmentRepo, $messageAttachment, $item['id']);

            $this->entityManager->persist($messageAttachment);
            $this->entityManager->flush();
        }
    }
}
