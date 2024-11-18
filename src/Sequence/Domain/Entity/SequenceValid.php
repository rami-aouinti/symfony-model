<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Sequence\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SequenceValid
 *
 * @package App\Sequence\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'sequence_valid')]
#[ORM\Entity]
class SequenceValid
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SequenceVariable::class)]
    #[ORM\JoinColumn(name: 'sequence_variable_id', referencedColumnName: 'id')]
    protected ?SequenceVariable $variable = null;

    #[ORM\ManyToOne(targetEntity: SequenceCondition::class)]
    #[ORM\JoinColumn(name: 'sequence_condition_id', referencedColumnName: 'id')]
    protected ?SequenceCondition $condition = null;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getVariable(): ?SequenceVariable
    {
        return $this->variable;
    }

    public function setVariable(?SequenceVariable $variable): self
    {
        $this->variable = $variable;

        return $this;
    }

    public function getCondition(): ?SequenceCondition
    {
        return $this->condition;
    }

    public function setCondition(?SequenceCondition $condition): self
    {
        $this->condition = $condition;

        return $this;
    }
}
