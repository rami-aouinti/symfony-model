<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Security\Authorization\Voter;

use App\CoreBundle\Entity\Course\Course;
use App\CourseBundle\Component\CourseCopy\Resources\Resource;
use App\CourseBundle\Entity\Group\CGroup;
use App\Platform\Domain\Entity\AbstractResource;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<'CREATE'|'VIEW'|'EDIT'|'DELETE'|'EXPORT', Resource>
 */
class ResourceVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const CREATE = 'CREATE';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const EXPORT = 'EXPORT';

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

        // Course/CGroup/ are AbstractResource, but it's checked with the CourseVoter
        if (
            $subject instanceof Course
            || $subject instanceof CGroup
        ) {
            return false;
        }

        // only vote on ResourceNode objects inside this voter
        return $subject instanceof AbstractResource;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return true;
    }
}
