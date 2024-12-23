<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Quiz\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Quiz rel question categories.
 */
#[ORM\Table(name: 'c_quiz_rel_category')]
#[ORM\Entity]
class CQuizRelQuestionCategory
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[ORM\ManyToOne(targetEntity: CQuizQuestionCategory::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'iid', onDelete: 'CASCADE')]
    protected CQuizQuestionCategory $category;

    #[ORM\ManyToOne(targetEntity: CQuiz::class, cascade: ['persist'], inversedBy: 'questionsCategories')]
    #[ORM\JoinColumn(name: 'exercise_id', referencedColumnName: 'iid', onDelete: 'CASCADE')]
    protected CQuiz $quiz;

    #[ORM\Column(name: 'count_questions', type: 'integer', nullable: false)]
    protected int $countQuestions;

    public function getIid(): ?int
    {
        return $this->iid;
    }

    public function getCategory(): CQuizQuestionCategory
    {
        return $this->category;
    }

    public function setCategory(CQuizQuestionCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getQuiz(): CQuiz
    {
        return $this->quiz;
    }

    public function setQuiz(CQuiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getCountQuestions(): int
    {
        return $this->countQuestions;
    }

    public function setCountQuestions(int $countQuestions): self
    {
        $this->countQuestions = $countQuestions;

        return $this;
    }
}
