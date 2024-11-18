<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity\Gradebook;

use Doctrine\ORM\Mapping as ORM;

/**
 * GradeModel.
 */
#[ORM\Table(name: 'grade_model')]
#[ORM\Entity]
class GradeModel
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: false)]
    protected string $title;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(name: 'default_lowest_eval_exclude', type: 'boolean', nullable: true)]
    protected ?bool $defaultLowestEvalExclude = null;

    #[ORM\Column(name: 'default_external_eval', type: 'boolean', nullable: true)]
    protected ?bool $defaultExternalEval = null;

    #[ORM\Column(name: 'default_external_eval_prefix', type: 'string', length: 140, nullable: true)]
    protected ?string $defaultExternalEvalPrefix = null;

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     */
    public function getTitle(): string
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
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDefaultLowestEvalExclude(bool $defaultLowestEvalExclude): self
    {
        $this->defaultLowestEvalExclude = $defaultLowestEvalExclude;

        return $this;
    }

    /**
     * Get defaultLowestEvalExclude.
     */
    public function getDefaultLowestEvalExclude(): ?bool
    {
        return $this->defaultLowestEvalExclude;
    }

    public function setDefaultExternalEval(bool $defaultExternalEval): self
    {
        $this->defaultExternalEval = $defaultExternalEval;

        return $this;
    }

    /**
     * Get defaultExternalEval.
     */
    public function getDefaultExternalEval(): ?bool
    {
        return $this->defaultExternalEval;
    }

    public function setDefaultExternalEvalPrefix(string $defaultExternalEvalPrefix): self
    {
        $this->defaultExternalEvalPrefix = $defaultExternalEvalPrefix;

        return $this;
    }

    /**
     * Get defaultExternalEvalPrefix.
     */
    public function getDefaultExternalEvalPrefix(): ?string
    {
        return $this->defaultExternalEvalPrefix;
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
