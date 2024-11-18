<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\Platform\Domain\Entity;

use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Traits\UserTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Class ExtraFieldSavedSearch
 *
 * @package App\Platform\Domain\Entity
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[ORM\Table(name: 'extra_field_saved_search')]
#[ORM\Entity]
class ExtraFieldSavedSearch
{
    use TimestampableEntity;
    use UserTrait;

    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ExtraField::class)]
    #[ORM\JoinColumn(name: 'field_id', referencedColumnName: 'id')]
    protected ExtraField $field;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected User $user;

    #[ORM\Column(name: 'value', type: 'array', unique: false, nullable: true)]
    protected ?array $value;

    public function __construct()
    {
        $this->value = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getField(): ExtraField
    {
        return $this->field;
    }

    public function setField(ExtraField $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?array
    {
        return $this->value;
    }

    public function setValue(array $value): self
    {
        $this->value = $value;

        return $this;
    }
}
