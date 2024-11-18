<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Entity\Survey;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package App\CourseBundle\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'c_survey_question_option')]
#[ORM\Index(columns: ['question_id'], name: 'idx_survey_qo_qid')]
#[ORM\Entity]
class CSurveyQuestionOption
{
    #[ORM\Column(name: 'iid', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $iid = null;

    #[ORM\ManyToOne(targetEntity: CSurveyQuestion::class, inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'iid', onDelete: 'SET NULL')]
    protected CSurveyQuestion $question;

    #[ORM\ManyToOne(targetEntity: CSurvey::class, inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'iid', onDelete: 'CASCADE')]
    protected CSurvey $survey;

    #[ORM\Column(name: 'option_text', type: 'text', nullable: false)]
    protected string $optionText;

    #[ORM\Column(name: 'sort', type: 'integer', nullable: false)]
    protected int $sort;

    #[ORM\Column(name: 'value', type: 'integer', nullable: false)]
    protected int $value;

    public function __construct()
    {
    }

    public function getIid(): ?int
    {
        return $this->iid;
    }

    public function setOptionText(string $optionText): self
    {
        $this->optionText = $optionText;

        return $this;
    }

    /**
     * Get optionText.
     */
    public function getOptionText(): string
    {
        return $this->optionText;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort.
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     */
    public function getValue(): int
    {
        return $this->value;
    }

    public function getQuestion(): CSurveyQuestion
    {
        return $this->question;
    }

    public function setQuestion(CSurveyQuestion $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getSurvey(): CSurvey
    {
        return $this->survey;
    }

    public function setSurvey(CSurvey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }
}
