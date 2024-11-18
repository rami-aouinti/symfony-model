<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Migrations\Schema\V200;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Migrations\AbstractMigrationChamilo;
use App\CoreBundle\Repository\Node\CourseRepository;
use App\CourseBundle\Repository\CForumCategoryRepository;
use App\CourseBundle\Repository\CForumPostRepository;
use App\CourseBundle\Repository\CForumRepository;
use App\CourseBundle\Repository\CForumThreadRepository;
use App\Forum\Domain\Entity\CForum;
use App\Forum\Domain\Entity\CForumCategory;
use App\Forum\Domain\Entity\CForumPost;
use App\Forum\Domain\Entity\CForumThread;
use App\Kernel;
use Doctrine\DBAL\Schema\Schema;

final class Version20201215160445 extends AbstractMigrationChamilo
{
    public function getDescription(): string
    {
        return 'Migrate c_forum tables';
    }

    public function up(Schema $schema): void
    {
        $forumCategoryRepo = $this->container->get(CForumCategoryRepository::class);
        $forumRepo = $this->container->get(CForumRepository::class);
        $forumThreadRepo = $this->container->get(CForumThreadRepository::class);
        $forumPostRepo = $this->container->get(CForumPostRepository::class);
        $courseRepo = $this->container->get(CourseRepository::class);

        /** @var Kernel $kernel */
        $kernel = $this->container->get('kernel');
        $rootPath = $kernel->getProjectDir();

        $q = $this->entityManager->createQuery('SELECT c FROM App\CoreBundle\Entity\Course c');

        /** @var Course $course */
        foreach ($q->toIterable() as $course) {
            $courseId = $course->getId();
            $course = $courseRepo->find($courseId);

            $admin = $this->getAdmin();

            // Categories.
            $sql = "SELECT * FROM c_forum_category WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];

                /** @var CForumCategory $resource */
                $resource = $forumCategoryRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }
                $result = $this->fixItemProperty(
                    'forum_category',
                    $forumCategoryRepo,
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

            $this->entityManager->flush();
            $this->entityManager->clear();

            // Forums.
            $sql = "SELECT * FROM c_forum_forum WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();

            $admin = $this->getAdmin();
            foreach ($items as $itemData) {
                $id = $itemData['iid'];

                /** @var CForum $resource */
                $resource = $forumRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }

                $course = $courseRepo->find($courseId);

                $parent = null;
                $categoryId = $itemData['forum_category'];
                if (!empty($categoryId)) {
                    $parent = $forumCategoryRepo->find($categoryId);
                }

                // Parent should not be null, because every forum must have a category, in this case use the course
                // as parent.
                if ($parent === null) {
                    $parent = $course;
                }

                $result = $this->fixItemProperty(
                    'forum',
                    $forumRepo,
                    $course,
                    $admin,
                    $resource,
                    $parent
                );

                $this->entityManager->persist($resource);
                $this->entityManager->flush();

                $forumImage = $itemData['forum_image'];
                if (!empty($forumImage)) {
                    $filePath = $this->getUpdateRootPath() . '/app/courses/' . $course->getDirectory() . '/upload/forum/images/' . $forumImage;
                    error_log('MIGRATIONS :: $filePath -- ' . $filePath . ' ...');
                    if ($this->fileExists($filePath)) {
                        $this->addLegacyFileToResource($filePath, $forumRepo, $resource, $id, $forumImage);
                    }
                }

                if ($result === false) {
                    continue;
                }
                $this->entityManager->persist($resource);
                $this->entityManager->flush();
            }
            $this->entityManager->flush();
            $this->entityManager->clear();

            // Threads.
            $sql = "SELECT * FROM c_forum_thread WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            $admin = $this->getAdmin();

            foreach ($items as $itemData) {
                $id = (int)$itemData['iid'];

                /** @var CForumThread $resource */
                $resource = $forumThreadRepo->find($id);
                if ($resource->hasResourceNode()) {
                    continue;
                }

                $forumId = (int)$itemData['forum_id'];
                if (empty($forumId)) {
                    continue;
                }

                /** @var CForum|null $forum */
                $forum = $forumRepo->find($forumId);
                if ($forum === null) {
                    continue;
                }

                $course = $courseRepo->find($courseId);

                $result = $this->fixItemProperty(
                    'forum_thread',
                    $forumThreadRepo,
                    $course,
                    $admin,
                    $resource,
                    $forum
                );

                if ($result === false) {
                    continue;
                }

                $this->entityManager->persist($resource);
                $this->entityManager->flush();
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            // Posts.
            $sql = "SELECT * FROM c_forum_post WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();
            $admin = $this->getAdmin();
            foreach ($items as $itemData) {
                $id = (int)$itemData['iid'];

                /** @var CForumPost $resource */
                $resource = $forumPostRepo->find($id);

                if ($resource->hasResourceNode()) {
                    continue;
                }

                if (empty(trim($resource->getTitle()))) {
                    $resource->setTitle(\sprintf('Post #%s', $resource->getIid()));
                }

                $threadId = (int)$itemData['thread_id'];

                if (empty($threadId)) {
                    continue;
                }

                /** @var CForumThread|null $thread */
                $thread = $forumThreadRepo->find($threadId);

                if ($thread === null) {
                    continue;
                }

                $forum = $thread->getForum();

                // For some reason the thread doesn't have a forum, so we ignore the thread posts.
                if ($forum === null) {
                    continue;
                }

                $course = $courseRepo->find($courseId);

                $result = $this->fixItemProperty(
                    'forum_post',
                    $forumPostRepo,
                    $course,
                    $admin,
                    $resource,
                    $thread
                );

                if ($result === false) {
                    continue;
                }

                $this->entityManager->persist($resource);
                $this->entityManager->flush();
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            // Post attachments
            $sql = "SELECT * FROM c_forum_attachment WHERE c_id = {$courseId}
                    ORDER BY iid";
            $result = $this->connection->executeQuery($sql);
            $items = $result->fetchAllAssociative();

            foreach ($items as $itemData) {
                $id = $itemData['iid'];
                $postId = (int)$itemData['post_id'];
                $path = $itemData['path'];
                $fileName = $itemData['filename'];

                /** @var CForumPost|null $post */
                $post = $forumPostRepo->find($postId);

                if ($post === null || !$post->hasResourceNode()) {
                    continue;
                }

                if (!empty($fileName) && !empty($path)) {
                    $filePath = $this->getUpdateRootPath() . '/app/courses/' . $course->getDirectory() . '/upload/forum/' . $path;
                    error_log('MIGRATIONS :: $filePath -- ' . $filePath . ' ...');
                    if ($this->fileExists($filePath)) {
                        $this->addLegacyFileToResource($filePath, $forumPostRepo, $post, $id, $fileName);
                        $this->entityManager->persist($post);
                        $this->entityManager->flush();
                    }
                }
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }
}
