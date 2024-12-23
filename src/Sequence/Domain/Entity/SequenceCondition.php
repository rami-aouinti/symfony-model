<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Sequence\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SequenceCondition
 *
 * @package App\Sequence\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'sequence_condition')]
#[ORM\Entity]
class SequenceCondition
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: false)]
    protected string $description;

    #[ORM\Column(name: 'mat_op', type: 'string')]
    protected string $mathOperation;

    #[ORM\Column(name: 'param', type: 'float')]
    protected float $param;

    #[ORM\Column(name: 'act_true', type: 'integer')]
    protected int $actTrue;

    #[ORM\Column(name: 'act_false', type: 'string')]
    protected string $actFalse;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getMathOperation()
    {
        return $this->mathOperation;
    }

    public function setMathOperation(string $mathOperation): self
    {
        $this->mathOperation = $mathOperation;

        return $this;
    }

    public function getParam(): float
    {
        return $this->param;
    }

    public function setParam(float $param): self
    {
        $this->param = $param;

        return $this;
    }

    public function getActTrue(): int
    {
        return $this->actTrue;
    }

    public function setActTrue(int $actTrue): self
    {
        $this->actTrue = $actTrue;

        return $this;
    }

    public function getActFalse(): string
    {
        return $this->actFalse;
    }

    public function setActFalse(string $actFalse): self
    {
        $this->actFalse = $actFalse;

        return $this;
    }
}
