<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Quiz\Domain\Entity;

use App\CourseBundle\Repository\CQuizQuestionRepository;
use App\Platform\Domain\Entity\AbstractResource;
use App\Platform\Domain\Entity\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CQuizQuestion.
 */
#[ORM\Table(name: 'c_quiz_question')]
#[ORM\Index(columns: ['position'], name: 'position')]
#[ORM\Entity(repositoryClass: CQuizQuestionRepository::class)]
class CQuizQuestion extends AbstractResource implements ResourceInterface, Stringable
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'question', type: 'text', nullable: false)]
    protected string $question;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(name: 'ponderation', type: 'float', precision: 6, scale: 2, nullable: false, options: [
        'default' => 0,
    ])]
    protected float $ponderation;

    #[ORM\Column(name: 'position', type: 'integer', nullable: false)]
    protected int $position;

    #[ORM\Column(name: 'type', type: 'integer', nullable: false)]
    protected int $type;

    #[ORM\Column(name: 'picture', type: 'string', length: 50, nullable: true)]
    protected ?string $picture = null;

    #[ORM\Column(name: 'level', type: 'integer', nullable: false)]
    protected int $level;

    #[ORM\Column(name: 'feedback', type: 'text', nullable: true)]
    protected ?string $feedback = null;

    #[ORM\Column(name: 'extra', type: 'string', length: 255, nullable: true)]
    protected ?string $extra = null;

    #[ORM\Column(name: 'question_code', type: 'string', length: 10, nullable: true)]
    protected ?string $questionCode = null;

    #[ORM\JoinTable(name: 'c_quiz_question_rel_category')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'iid')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'iid')]
    #[ORM\ManyToMany(targetEntity: CQuizQuestionCategory::class, inversedBy: 'questions')]
    protected Collection $categories;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: CQuizRelQuestion::class, cascade: ['persist'])]
    protected Collection $relQuizzes;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: CQuizAnswer::class, cascade: ['persist'])]
    protected Collection $answers;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: CQuizQuestionOption::class, cascade: ['persist'])]
    protected Collection $options;

    #[ORM\Column(name: 'mandatory', type: 'integer')]
    protected int $mandatory;

    #[ORM\Column(name: 'duration', type: 'integer', nullable: true)]
    protected ?int $duration = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->relQuizzes = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->ponderation = 0.0;
        $this->mandatory = 0;
    }

    public function __toString(): string
    {
        return $this->getQuestion();
    }

    public function addCategory(CQuizQuestionCategory $category): void
    {
        if ($this->categories->contains($category)) {
            return;
        }

        $this->categories->add($category);
        $category->addQuestion($this);
    }

    public function updateCategory(CQuizQuestionCategory $category): void
    {
        if ($this->categories->count() === 0) {
            $this->addCategory($category);
        }

        if ($this->categories->contains($category)) {
            return;
        }

        foreach ($this->categories as $item) {
            $this->categories->removeElement($item);
        }

        $this->addCategory($category);
    }

    public function removeCategory(CQuizQuestionCategory $category): void
    {
        if (!$this->categories->contains($category)) {
            return;
        }

        $this->categories->removeElement($category);
        $category->removeQuestion($this);
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setPonderation(float $ponderation): self
    {
        $this->ponderation = $ponderation;

        return $this;
    }

    /**
     * Get ponderation.
     */
    public function getPonderation(): float
    {
        return $this->ponderation;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     */
    public function getType(): int
    {
        return $this->type;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture.
     */
    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level.
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    public function setExtra(?string $extra): self
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Get extra.
     */
    public function getExtra(): ?string
    {
        return $this->extra;
    }

    public function setQuestionCode(string $questionCode): self
    {
        $this->questionCode = $questionCode;

        return $this;
    }

    /**
     * Get questionCode.
     */
    public function getQuestionCode(): ?string
    {
        return $this->questionCode;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setFeedback(?string $feedback): self
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * @return CQuizQuestionCategory[]|Collection
     */
    public function getCategories(): array|Collection
    {
        return $this->categories;
    }

    /**
     * @return CQuizRelQuestion[]|Collection
     */
    public function getRelQuizzes(): array|Collection
    {
        return $this->relQuizzes;
    }

    /**
     * @return CQuizAnswer[]|Collection
     */
    public function getAnswers(): array|Collection
    {
        return $this->answers;
    }

    public function getMandatory(): int
    {
        return $this->mandatory;
    }

    /**
     * @return CQuizQuestionOption[]|Collection
     */
    public function getOptions(): array|Collection
    {
        return $this->options;
    }

    /**
     * @param CQuizQuestionOption[]|Collection $options
     */
    public function setOptions(array|Collection $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get iid.
     */
    public function getIid(): ?int
    {
        return $this->iid;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getResourceIdentifier(): int|Uuid
    {
        return $this->getIid();
    }

    public function getResourceName(): string
    {
        return $this->getQuestion();
    }

    public function setResourceName(string $name): self
    {
        return $this->setQuestion($name);
    }
}