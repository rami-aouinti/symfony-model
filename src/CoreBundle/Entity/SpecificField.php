<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SpecificField.
 */
#[ORM\Table(name: 'specific_field')]
#[ORM\UniqueConstraint(name: 'unique_specific_field__code', columns: ['code'])]
#[ORM\Entity]
class SpecificField
{
    #[ORM\Column(name: 'code', type: 'string', length: 1, nullable: false)]
    protected string $code;

    #[ORM\Column(name: 'title', type: 'string', length: 200, nullable: false)]
    protected string $title;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    /**
     * Set code.
     *
     * @return SpecificField
     */
    public function setCode(string $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set title.
     *
     * @return SpecificField
     */
    public function setTitle(string $title)
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
