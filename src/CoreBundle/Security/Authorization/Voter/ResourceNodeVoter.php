<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Security\Authorization\Voter;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Settings\SettingsManager;
use App\CourseBundle\Entity\Group\CGroup;
use App\Platform\Domain\Entity\ResourceLink;
use App\Platform\Domain\Entity\ResourceNode;
use App\Platform\Domain\Entity\ResourceRight;
use App\Session\Domain\Entity\Session;
use ChamiloSession;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource;
use Laminas\Permissions\Acl\Role\GenericRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<'CREATE'|'VIEW'|'EDIT'|'DELETE'|'EXPORT', ResourceNode>
 */
class ResourceNodeVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const CREATE = 'CREATE';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const EXPORT = 'EXPORT';
    public const ROLE_CURRENT_COURSE_TEACHER = 'ROLE_CURRENT_COURSE_TEACHER';
    public const ROLE_CURRENT_COURSE_STUDENT = 'ROLE_CURRENT_COURSE_STUDENT';
    public const ROLE_CURRENT_COURSE_GROUP_TEACHER = 'ROLE_CURRENT_COURSE_GROUP_TEACHER';
    public const ROLE_CURRENT_COURSE_GROUP_STUDENT = 'ROLE_CURRENT_COURSE_GROUP_STUDENT';
    public const ROLE_CURRENT_COURSE_SESSION_TEACHER = 'ROLE_CURRENT_COURSE_SESSION_TEACHER';
    public const ROLE_CURRENT_COURSE_SESSION_STUDENT = 'ROLE_CURRENT_COURSE_SESSION_STUDENT';

    public function __construct(
        private Security $security,
        private RequestStack $requestStack,
        private SettingsManager $settingsManager
    ) {
    }

    public static function getReaderMask(): int
    {
        $builder = (new MaskBuilder())
            ->add(self::VIEW)
        ;

        return $builder->get();
    }

    public static function getEditorMask(): int
    {
        $builder = (new MaskBuilder())
            ->add(self::VIEW)
            ->add(self::EDIT)
        ;

        return $builder->get();
    }

    protected function supports(string $attribute, $subject): bool
    {
        $options = [
            self::VIEW,
            self::CREATE,
            self::EDIT,
            self::DELETE,
            self::EXPORT,
        ];

        // if the attribute isn't one we support, return false
        if (!\in_array($attribute, $options, true)) {
            return false;
        }

        // only vote on ResourceNode objects inside this voter
        return $subject instanceof ResourceNode;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        // Make sure there is a user object (i.e. that the user is logged in)
        // Update. No, anons can enter a node depending in the visibility.
        // $user = $token->getUser();
        /*if (!$user instanceof UserInterface) {
            return false;
        }*/
        /** @var ResourceNode $resourceNode */
        $resourceNode = $subject;
        $resourceTypeName = $resourceNode->getResourceType()->getTitle();

        // Illustrations are always visible, nothing to check.
        if ($resourceTypeName === 'illustrations') {
            return true;
        }

        // Courses are also a Resource but courses are protected using the CourseVoter, not by ResourceNodeVoter.
        if ($resourceTypeName === 'courses') {
            return true;
        }

        // Checking admin role.
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // @todo
        switch ($attribute) {
            case self::VIEW:
                if ($resourceNode->isPublic()) {
                    return true;
                }

                // no break
            case self::EDIT:
                break;
        }

        $user = $token->getUser();
        // Check if I'm the owner.
        $creator = $resourceNode->getCreator();

        if (
            $creator instanceof UserInterface
            && $user instanceof UserInterface
            && $user->getUserIdentifier() === $creator->getUserIdentifier()
        ) {
            return true;
        }

        // Checking links connected to this resource.
        $request = $this->requestStack->getCurrentRequest();

        $courseId = (int)$request->get('cid');
        $sessionId = (int)$request->get('sid');
        $groupId = (int)$request->get('gid');

        // Try Session values.
        if (empty($courseId) && $request->hasSession()) {
            $courseId = (int)$request->getSession()->get('cid');
            $sessionId = (int)$request->getSession()->get('sid');
            $groupId = (int)$request->getSession()->get('gid');

            if ($courseId === 0) {
                $courseId = (int)ChamiloSession::read('cid');
                $sessionId = (int)ChamiloSession::read('sid');
                $groupId = (int)ChamiloSession::read('gid');
            }
        }

        $links = $resourceNode->getResourceLinks();
        $firstLink = $resourceNode->getResourceLinks()->first();
        if ($resourceNode->hasResourceFile() && $firstLink) {
            if ($courseId === 0 && $firstLink->getCourse() instanceof Course) {
                $courseId = (int)$firstLink->getCourse()->getId();
            }
            if ($sessionId === 0 && $firstLink->getSession() instanceof Session) {
                $sessionId = (int)$firstLink->getSession()->getId();
            }
            if ($groupId === 0 && $firstLink->getGroup() instanceof CGroup) {
                $groupId = (int)$firstLink->getGroup()->getIid();
            }
            if (
                $firstLink->getUser() instanceof UserInterface
                && $this->settingsManager->getSetting('security.access_to_personal_file_for_all') === 'true'
            ) {
                return true;
            }
        }

        $linkFound = 0;
        $link = null;

        // @todo implement view, edit, delete.
        foreach ($links as $link) {
            // Check if resource was sent to the current user.
            $linkUser = $link->getUser();
            if (
                $linkUser instanceof UserInterface
                && $user instanceof UserInterface
                && $linkUser->getUserIdentifier() === $user->getUserIdentifier()
            ) {
                $linkFound = 2;

                break;
            }

            $linkCourse = $link->getCourse();

            // Course found, but courseId not set, skip course checking.
            if ($linkCourse instanceof Course && empty($courseId)) {
                continue;
            }

            $linkSession = $link->getSession();
            $linkGroup = $link->getGroup();
            // $linkUserGroup = $link->getUserGroup();

            // @todo Check if resource was sent to a usergroup

            // Check if resource was sent inside a group in a course session.
            if (
                $linkUser === null
                && $linkGroup instanceof CGroup && !empty($groupId)
                && $linkSession instanceof Session && !empty($sessionId)
                && $linkCourse instanceof Course
                && ($linkCourse->getId() === $courseId
                && $linkSession->getId() === $sessionId
                && $linkGroup->getIid() === $groupId)
            ) {
                $linkFound = 3;

                break;
            }

            // Check if resource was sent inside a group in a base course.
            if (
                $linkUser === null
                && empty($sessionId)
                && $linkGroup instanceof CGroup && !empty($groupId)
                && $linkCourse instanceof Course && ($linkCourse->getId() === $courseId
                && $linkGroup->getIid() === $groupId)
            ) {
                $linkFound = 4;

                break;
            }

            // Check if resource was sent to a course inside a session.
            if (
                $linkUser === null
                && $linkSession instanceof Session && !empty($sessionId)
                && $linkCourse instanceof Course && ($linkCourse->getId() === $courseId
                && $linkSession->getId() === $sessionId)
            ) {
                $linkFound = 5;

                break;
            }

            // Check if resource was sent to a course.
            if (
                $linkUser === null
                && $linkCourse instanceof Course && $linkCourse->getId() === $courseId
            ) {
                $linkFound = 6;

                break;
            }

            /*if (ResourceLink::VISIBILITY_PUBLISHED === $link->getVisibility()) {
             * $linkFound = true;
             * break;
             * }*/
        }

        // No link was found.
        if ($linkFound === 0) {
            return false;
        }

        // Getting rights from the link
        $rightsFromResourceLink = $link->getResourceRights();
        $allowAnonsToView = false;

        $rights = [];
        if ($rightsFromResourceLink->count() > 0) {
            // Taken rights from the link.
            $rights = $rightsFromResourceLink;
        }

        // Taken the rights from context (request + user roles).
        // $rights = $link->getResourceNode()->getTool()->getToolResourceRight();
        // $rights = $link->getResourceNode()->getResourceType()->getTool()->getToolResourceRight();

        // By default, the rights are:
        // Teachers: CRUD.
        // Students: Only read.
        // Anons: Only read.
        $readerMask = self::getReaderMask();
        $editorMask = self::getEditorMask();

        if ($courseId && $link->hasCourse() && $link->getCourse()->getId() === $courseId) {
            // If teacher.
            if ($this->security->isGranted(self::ROLE_CURRENT_COURSE_TEACHER)) {
                $resourceRight = (new ResourceRight())
                    ->setMask($editorMask)
                    ->setRole(self::ROLE_CURRENT_COURSE_TEACHER)
                ;
                $rights[] = $resourceRight;
            }

            // If student.
            if (
                $this->security->isGranted(self::ROLE_CURRENT_COURSE_STUDENT)
                && $link->getVisibility() === ResourceLink::VISIBILITY_PUBLISHED
            ) {
                $resourceRight = (new ResourceRight())
                    ->setMask($readerMask)
                    ->setRole(self::ROLE_CURRENT_COURSE_STUDENT)
                ;
                $rights[] = $resourceRight;
            }

            // For everyone.
            if (
                $link->getVisibility() === ResourceLink::VISIBILITY_PUBLISHED
                && $link->getCourse()->isPublic()
            ) {
                $allowAnonsToView = true;
                $resourceRight = (new ResourceRight())
                    ->setMask($readerMask)
                    ->setRole('IS_AUTHENTICATED_ANONYMOUSLY')
                ;
                $rights[] = $resourceRight;
            }
        }

        if (!empty($groupId)) {
            if ($this->security->isGranted(self::ROLE_CURRENT_COURSE_GROUP_TEACHER)) {
                $resourceRight = (new ResourceRight())
                    ->setMask($editorMask)
                    ->setRole(self::ROLE_CURRENT_COURSE_GROUP_TEACHER)
                ;
                $rights[] = $resourceRight;
            }

            if ($this->security->isGranted(self::ROLE_CURRENT_COURSE_GROUP_STUDENT)) {
                $resourceRight = (new ResourceRight())
                    ->setMask($readerMask)
                    ->setRole(self::ROLE_CURRENT_COURSE_GROUP_STUDENT)
                ;
                $rights[] = $resourceRight;
            }
        }

        if (!empty($sessionId)) {
            if ($this->security->isGranted(self::ROLE_CURRENT_COURSE_SESSION_TEACHER)) {
                $resourceRight = (new ResourceRight())
                    ->setMask($editorMask)
                    ->setRole(self::ROLE_CURRENT_COURSE_SESSION_TEACHER)
                ;
                $rights[] = $resourceRight;
            }

            if ($this->security->isGranted(self::ROLE_CURRENT_COURSE_SESSION_STUDENT)) {
                $resourceRight = (new ResourceRight())
                    ->setMask($readerMask)
                    ->setRole(self::ROLE_CURRENT_COURSE_SESSION_STUDENT)
                ;
                $rights[] = $resourceRight;
            }
        }

        if (empty($rights) && $link->getVisibility() === ResourceLink::VISIBILITY_PUBLISHED) {
            // Give just read access.
            $resourceRight = (new ResourceRight())
                ->setMask($readerMask)
                ->setRole('ROLE_USER')
            ;
            $rights[] = $resourceRight;
        }

        // Asked mask
        $mask = new MaskBuilder();
        $mask->add($attribute);

        $askedMask = (string)$mask->get();

        // Creating roles
        // @todo move this in a service
        $anon = new GenericRole('IS_AUTHENTICATED_ANONYMOUSLY');
        $userRole = new GenericRole('ROLE_USER');
        $student = new GenericRole('ROLE_STUDENT');
        $teacher = new GenericRole('ROLE_TEACHER');
        $studentBoss = new GenericRole('ROLE_STUDENT_BOSS');

        $currentStudent = new GenericRole(self::ROLE_CURRENT_COURSE_STUDENT);
        $currentTeacher = new GenericRole(self::ROLE_CURRENT_COURSE_TEACHER);

        $currentStudentGroup = new GenericRole(self::ROLE_CURRENT_COURSE_GROUP_STUDENT);
        $currentTeacherGroup = new GenericRole(self::ROLE_CURRENT_COURSE_GROUP_TEACHER);

        $currentStudentSession = new GenericRole(self::ROLE_CURRENT_COURSE_SESSION_STUDENT);
        $currentTeacherSession = new GenericRole(self::ROLE_CURRENT_COURSE_SESSION_TEACHER);

        // Setting Simple ACL.
        $acl = (new Acl())
            ->addRole($anon)
            ->addRole($userRole)
            ->addRole($student)
            ->addRole($teacher)
            ->addRole($studentBoss)

            ->addRole($currentStudent)
            ->addRole($currentTeacher, self::ROLE_CURRENT_COURSE_STUDENT)

            ->addRole($currentStudentSession)
            ->addRole($currentTeacherSession, self::ROLE_CURRENT_COURSE_SESSION_STUDENT)

            ->addRole($currentStudentGroup)
            ->addRole($currentTeacherGroup, self::ROLE_CURRENT_COURSE_GROUP_STUDENT)
        ;

        // Add a security resource.
        $linkId = (string)$link->getId();
        $acl->addResource(new GenericResource($linkId));

        // Check all the right this link has.
        // Set rights from the ResourceRight.
        foreach ($rights as $right) {
            $acl->allow($right->getRole(), null, (string)$right->getMask());
        }

        // Role and permissions settings
        // Student can just view (read)
        // $acl->allow($student, null, self::getReaderMask());

        // Teacher can view/edit
        /*$acl->allow(
            $teacher,
            null,
            [
                self::getReaderMask(),
                self::getEditorMask(),
            ]
        );*/

        // Anons can see.
        if ($allowAnonsToView) {
            $acl->allow($anon, null, (string)self::getReaderMask());
        }

        if ($token instanceof NullToken) {
            return $acl->isAllowed('IS_AUTHENTICATED_ANONYMOUSLY', $linkId, $askedMask);
        }

        $roles = $user->getRoles();

        foreach ($roles as $role) {
            if ($acl->isAllowed($role, $linkId, $askedMask)) {
                return true;
            }
        }

        return false;
    }
}