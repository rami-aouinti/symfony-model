<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity\Gradebook;

use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Traits\UserTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'gradebook_linkeval_log')]
#[ORM\Entity]
class GradebookLinkevalLog
{
    use UserTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(name: 'id_linkeval_log', type: 'integer', nullable: false)]
    protected int $idLinkevalLog;

    #[ORM\Column(name: 'title', type: 'text')]
    protected string $title;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(name: 'weight', type: 'smallint', nullable: true)]
    protected ?int $weight = null;

    #[ORM\Column(name: 'visible', type: 'boolean', nullable: true)]
    protected ?bool $visible = null;

    #[ORM\Column(name: 'type', type: 'string', length: 20, nullable: false)]
    protected string $type;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'gradeBookLinkEvalLogs')]
    #[ORM\JoinColumn(name: 'user_id_log', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected User $user;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    protected DateTime $createdAt;

    /**
     * Set idLinkevalLog.
     *
     * @return GradebookLinkevalLog
     */
    public function setIdLinkevalLog(int $idLinkevalLog)
    {
        $this->idLinkevalLog = $idLinkevalLog;

        return $this;
    }

    /**
     * Get idLinkevalLog.
     *
     * @return int
     */
    public function getIdLinkevalLog()
    {
        return $this->idLinkevalLog;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdAt.
     *
     * @return GradebookLinkevalLog
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set weight.
     *
     * @return GradebookLinkevalLog
     */
    public function setWeight(int $weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight.
     *
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set visible.
     *
     * @return GradebookLinkevalLog
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set type.
     *
     * @return GradebookLinkevalLog
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
