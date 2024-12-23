<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity\Skill;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\CoreBundle\Entity\Course\Course;
use App\CoreBundle\Entity\Listener\SkillRelUserListener;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Traits\UserTrait;
use App\Platform\Domain\Entity\Level;
use App\Session\Domain\Entity\Session;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(security: "is_granted('VIEW', object)"),
        new Put(security: "is_granted('EDIT', object)"),
        new Delete(security: "is_granted('DELETE', object)"),
        new Post(securityPostDenormalize: "is_granted('CREATE', object)"),
    ],
    normalizationContext: [
        'groups' => ['skill_rel_user:read'],
    ],
    denormalizationContext: [
        'groups' => ['skill_rel_user:write'],
    ],
    security: "is_granted('ROLE_USER')"
)]
#[ApiFilter(SearchFilter::class, properties: [
    'user' => 'exact',
])]
#[ORM\Table(name: 'skill_rel_user')]
#[ORM\Index(name: 'idx_select_cs', columns: ['course_id', 'session_id'])]
#[ORM\Index(name: 'idx_select_s_c_u', columns: ['session_id', 'course_id', 'user_id'])]
#[ORM\Index(name: 'idx_select_sk_u', columns: ['skill_id', 'user_id'])]
#[ORM\Entity]
#[ORM\EntityListeners([SkillRelUserListener::class])]
class SkillRelUser
{
    use UserTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'achievedSkills', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected User $user;

    #[Groups(['skill_rel_user:read'])]
    #[ORM\ManyToOne(targetEntity: Skill::class, inversedBy: 'issuedSkills', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'skill_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?Skill $skill = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'issuedSkills', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?Course $course = null;

    #[ORM\ManyToOne(targetEntity: Session::class, inversedBy: 'issuedSkills', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'session_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?Session $session = null;

    /**
     * @var Collection<int, SkillRelUserComment>
     */
    #[ORM\OneToMany(targetEntity: SkillRelUserComment::class, mappedBy: 'skillRelUser', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $comments;

    #[ORM\ManyToOne(targetEntity: Level::class)]
    #[ORM\JoinColumn(name: 'acquired_level', referencedColumnName: 'id')]
    protected ?Level $acquiredLevel = null;

    #[ORM\Column(name: 'acquired_skill_at', type: 'datetime', nullable: false)]
    protected DateTime $acquiredSkillAt;

    /**
     * Whether this has been confirmed by a teacher or not
     * Only set to 0 when the skill_rel_item says requires_validation = 1.
     */
    #[ORM\Column(name: 'validation_status', type: 'integer')]
    protected int $validationStatus;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'assigned_by', type: 'integer', nullable: false)]
    protected int $assignedBy;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'argumentation', type: 'text')]
    protected string $argumentation;

    #[ORM\Column(name: 'argumentation_author_id', type: 'integer')]
    protected int $argumentationAuthorId;

    public function __construct()
    {
        $this->validationStatus = 0;
        $this->comments = new ArrayCollection();
        $this->acquiredLevel = null;
        $this->acquiredSkillAt = new DateTime();
    }

    public function setSkill(Skill $skill): self
    {
        $this->skill = $skill;

        return $this;
    }

    /**
     * Get skill.
     *
     * @return Skill
     */
    public function getSkill()
    {
        return $this->skill;
    }

    public function setCourse(Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get course.
     *
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }

    public function setSession(Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get session.
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    public function setAcquiredSkillAt(DateTime $acquiredSkillAt): self
    {
        $this->acquiredSkillAt = $acquiredSkillAt;

        return $this;
    }

    /**
     * Get acquiredSkillAt.
     *
     * @return DateTime
     */
    public function getAcquiredSkillAt()
    {
        return $this->acquiredSkillAt;
    }

    public function setAssignedBy(int $assignedBy): self
    {
        $this->assignedBy = $assignedBy;

        return $this;
    }

    /**
     * Get assignedBy.
     *
     * @return int
     */
    public function getAssignedBy()
    {
        return $this->assignedBy;
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

    public function setAcquiredLevel(Level $acquiredLevel): self
    {
        $this->acquiredLevel = $acquiredLevel;

        return $this;
    }

    /**
     * Get acquiredLevel.
     *
     * @return Level
     */
    public function getAcquiredLevel()
    {
        return $this->acquiredLevel;
    }

    public function setArgumentationAuthorId(int $argumentationAuthorId): self
    {
        $this->argumentationAuthorId = $argumentationAuthorId;

        return $this;
    }

    /**
     * Get argumentationAuthorId.
     *
     * @return int
     */
    public function getArgumentationAuthorId()
    {
        return $this->argumentationAuthorId;
    }

    public function setArgumentation(string $argumentation): self
    {
        $this->argumentation = $argumentation;

        return $this;
    }

    /**
     * Get argumentation.
     *
     * @return string
     */
    public function getArgumentation()
    {
        return $this->argumentation;
    }

    /**
     * Get the source which the skill was obtained.
     *
     * @return string
     */
    public function getSourceName()
    {
        $source = '';
        if ($this->session !== null) {
            $source .= \sprintf('[%s] ', $this->session->getTitle());
        }

        if ($this->course !== null) {
            $source .= $this->course->getTitle();
        }

        return $source;
    }

    /**
     * Get the URL for the issue.
     *
     * @return string
     */
    public function getIssueUrl()
    {
        return api_get_path(WEB_PATH) . \sprintf('badge/%s', $this->id);
    }

    /**
     * Get the URL for the All issues page.
     *
     * @return string
     */
    public function getIssueUrlAll()
    {
        return api_get_path(WEB_PATH) . \sprintf('skill/%s/user/%s', $this->skill->getId(), $this->user->getId());
    }

    /**
     * Get the URL for the assertion.
     *
     * @return string
     */
    public function getAssertionUrl()
    {
        $url = api_get_path(WEB_CODE_PATH) . 'skills/assertion.php?';

        return $url . http_build_query([
            'user' => $this->user->getId(),
            'skill' => $this->skill->getId(),
            'course' => $this->course !== null ? $this->course->getId() : 0,
            'session' => $this->session !== null ? $this->session->getId() : 0,
        ]);
    }

    public function getComments(bool $sortDescByDateTime = false): Collection
    {
        if ($sortDescByDateTime) {
            $criteria = Criteria::create();
            $criteria->orderBy([
                'feedbackDateTime' => Criteria::DESC,
            ]);

            return $this->comments->matching($criteria);
        }

        return $this->comments;
    }

    /**
     * Calculate the average value from the feedback comments.
     */
    public function getAverage(): string
    {
        $sum = 0;
        $countValues = 0;
        foreach ($this->comments as $comment) {
            if ($comment->getFeedbackValue() === 0) {
                continue;
            }

            $sum += $comment->getFeedbackValue();
            $countValues++;
        }

        $average = $countValues > 0 ? $sum / $countValues : 0;

        return number_format($average, 2);
    }

    /**
     * @return int
     */
    public function getValidationStatus()
    {
        return $this->validationStatus;
    }

    /**
     * @return SkillRelUser
     */
    public function setValidationStatus(int $validationStatus)
    {
        $this->validationStatus = $validationStatus;

        return $this;
    }
}
