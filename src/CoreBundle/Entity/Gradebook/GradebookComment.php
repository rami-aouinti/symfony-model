<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity\Gradebook;

use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Traits\UserTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Class GradebookComment
 *
 * @package App\CoreBundle\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'gradebook_comment')]
#[ORM\Entity]
class GradebookComment
{
    use TimestampableEntity;
    use UserTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'gradeBookComments')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\ManyToOne(targetEntity: GradebookCategory::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'gradebook_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected GradebookCategory $gradeBook;

    #[ORM\Column(name: 'comment', type: 'text')]
    protected ?string $comment;

    public function __construct()
    {
        $this->comment = '';
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

    public function getGradeBook(): GradebookCategory
    {
        return $this->gradeBook;
    }

    public function setGradeBook(GradebookCategory $gradeBook): self
    {
        $this->gradeBook = $gradeBook;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
