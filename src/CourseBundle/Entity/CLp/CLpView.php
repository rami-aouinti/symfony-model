<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Entity\CLp;

use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\User\User;
use App\Session\Domain\Entity\Session;
use Doctrine\ORM\Mapping as ORM;

/**
 * CLpView.
 */
#[ORM\Table(name: 'c_lp_view')]
#[ORM\Index(name: 'course', columns: ['c_id'])]
#[ORM\Index(name: 'lp_id', columns: ['lp_id'])]
#[ORM\Index(name: 'session_id', columns: ['session_id'])]
#[ORM\Entity]
class CLpView
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\ManyToOne(targetEntity: CLp::class)]
    #[ORM\JoinColumn(name: 'lp_id', referencedColumnName: 'iid', onDelete: 'CASCADE')]
    protected CLp $lp;

    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(name: 'c_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected Course $course;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Session $session = null;

    #[ORM\Column(name: 'view_count', type: 'integer', nullable: false)]
    protected int $viewCount;

    #[ORM\Column(name: 'last_item', type: 'integer', nullable: false)]
    protected int $lastItem;

    #[ORM\Column(name: 'progress', type: 'integer', nullable: true)]
    protected ?int $progress = null;

    public function getIid(): ?int
    {
        return $this->iid;
    }

    public function setViewCount(int $viewCount): self
    {
        $this->viewCount = $viewCount;

        return $this;
    }

    /**
     * Get viewCount.
     *
     * @return int
     */
    public function getViewCount()
    {
        return $this->viewCount;
    }

    public function setLastItem(int $lastItem): self
    {
        $this->lastItem = $lastItem;

        return $this;
    }

    /**
     * Get lastItem.
     *
     * @return int
     */
    public function getLastItem()
    {
        return $this->lastItem;
    }

    public function setProgress(int $progress): self
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress.
     *
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }

    public function getLp(): CLp
    {
        return $this->lp;
    }

    public function setLp(CLp $lp): self
    {
        $this->lp = $lp;

        return $this;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
