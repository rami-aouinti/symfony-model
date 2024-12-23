<?php

declare(strict_types=1);

namespace App\CoreBundle\Migrations\Schema\V200;

use App\Access\Domain\Entity\AccessUrl;
use App\Access\Domain\Entity\AccessUrlRelUserGroup;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Entity\User\Usergroup;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\AccessUrlRepository;
use App\CoreBundle\Repository\Node\IllustrationRepository;
use App\CoreBundle\Repository\Node\UsergroupRepository;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class Version20210205082253 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate User/Usergroups images';
    }

    public function up(Schema $schema): void
    {
        $kernel = $this->container->get('kernel');
        $rootPath = $kernel->getProjectDir();

        $illustrationRepo = $this->container->get(IllustrationRepository::class);

        // Adding users to the resource node tree.
        $batchSize = self::BATCH_SIZE;
        $counter = 1;
        $q = $this->entityManager->createQuery('SELECT u FROM App\CoreBundle\Entity\User u');

        $sql = "SELECT * FROM settings WHERE variable = 'split_users_upload_directory' AND access_url = 1";
        $result = $this->connection->executeQuery($sql);
        $setting = $result->fetchAssociative();

        /** @var User $userEntity */
        foreach ($q->toIterable() as $userEntity) {
            if ($userEntity->hasResourceNode()) {
                continue;
            }
            $id = $userEntity->getId();
            $picture = $userEntity->getPictureUri();
            if (empty($picture)) {
                continue;
            }
            $path = "users/{$id}/";
            if (!empty($setting) && $setting['selected_value'] === 'true') {
                $path = 'users/' . substr((string)$id, 0, 1) . '/' . $id . '/';
            }
            $picturePath = $this->getUpdateRootPath() . '/app/upload/' . $path . '/' . $picture;
            error_log('MIGRATIONS :: $filePath -- ' . $picturePath . ' ...');
            if ($this->fileExists($picturePath)) {
                $mimeType = mime_content_type($picturePath);
                $file = new UploadedFile($picturePath, $picture, $mimeType, null, true);
                $illustrationRepo->addIllustration($userEntity, $userEntity, $file);
            }

            if (($counter % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear(); // Detaches all objects from Doctrine!
            }
            $counter++;
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        // Migrate Usergroup.
        $counter = 1;
        $q = $this->entityManager->createQuery('SELECT u FROM App\CoreBundle\Entity\Usergroup u');
        $admin = $this->getAdmin();

        $userGroupRepo = $this->container->get(UsergroupRepository::class);
        $urlRepo = $this->container->get(AccessUrlRepository::class);

        $urlList = $urlRepo->findAll();

        /** @var AccessUrl $url */
        $url = $urlList[0];

        /** @var Usergroup $userGroup */
        foreach ($q->toIterable() as $userGroup) {
            if ($userGroup->hasResourceNode()) {
                continue;
            }

            $userGroup->setCreator($admin);

            if ($userGroup->getUrls()->count() === 0) {
                $accessUrlRelUserGroup = (new AccessUrlRelUserGroup())
                    ->setUserGroup($userGroup)
                    ->setUrl($url)
                ;
                $userGroup->getUrls()->add($accessUrlRelUserGroup);
            }
            $userGroupRepo->addResourceNode($userGroup, $admin, $url);
            $this->entityManager->persist($userGroup);
            $this->entityManager->flush();
        }
        $this->entityManager->clear();

        // Migrate Usergroup images.
        $q = $this->entityManager->createQuery('SELECT u FROM App\CoreBundle\Entity\Usergroup u');

        /** @var Usergroup $userGroup */
        foreach ($q->toIterable() as $userGroup) {
            if (!$userGroup->hasResourceNode()) {
                continue;
            }

            $picture = $userGroup->getPicture();
            if (empty($picture)) {
                continue;
            }
            $id = $userGroup->getId();
            $path = "groups/{$id}/";
            if (!empty($setting) && $setting['selected_value'] === 'true') {
                $path = 'groups/' . substr((string)$id, 0, 1) . '/' . $id . '/';
            }
            $picturePath = $this->getUpdateRootPath() . '/app/upload/' . $path . '/' . $picture;
            error_log('MIGRATIONS :: $filePath -- ' . $picturePath . ' ...');
            if ($this->fileExists($picturePath)) {
                $mimeType = mime_content_type($picturePath);
                $file = new UploadedFile($picturePath, $picture, $mimeType, null, true);
                $illustrationRepo->addIllustration($userGroup, $admin, $file);
            }

            if (($counter % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear(); // Detaches all objects from Doctrine!
            }
            $counter++;
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
