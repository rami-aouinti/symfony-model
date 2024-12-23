<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Entity\CLp;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\Usergroup;
use App\CoreBundle\Traits\CourseTrait;
use App\CoreBundle\Traits\SessionTrait;
use App\Session\Domain\Entity\Session;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'c_lp_category_rel_usergroup')]
class CLpCategoryRelUserGroup
{
    use CourseTrait;
    use SessionTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CLpCategory::class)]
    #[ORM\JoinColumn(name: 'lp_category_id', referencedColumnName: 'iid')]
    protected CLpCategory $lpCategory;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', nullable: true)]
    protected ?Session $session = null;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(name: 'c_id', referencedColumnName: 'id', nullable: false)]
    protected Course $course;

    #[ORM\ManyToOne(targetEntity: Usergroup::class)]
    #[ORM\JoinColumn(name: 'usergroup_id', referencedColumnName: 'id', nullable: true)]
    protected ?Usergroup $userGroup = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    protected DateTime $createdAt;

    public function __construct()
    {
    }

    public function getLpCategory(): CLpCategory
    {
        return $this->lpCategory;
    }

    public function setLpCategory(CLpCategory $lpCategory): self
    {
        $this->lpCategory = $lpCategory;

        return $this;
    }

    public function getUserGroup(): ?Usergroup
    {
        return $this->userGroup;
    }

    public function setUserGroup(?Usergroup $userGroup): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
