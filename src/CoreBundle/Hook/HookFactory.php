<?php

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Hook;

use App\CoreBundle\Hook\Interfaces\HookEventInterface;
use Doctrine\ORM\EntityManager;
use Exception;

/**
 * Class HookFactory.
 */
class HookFactory
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws Exception
     *
     * @return HookEventInterface
     */
    public function build(string $type)
    {
        if (!class_exists($type)) {
            throw new Exception('Class "' . $type . '" fot found');
        }

        return $type::create($this->entityManager);
    }
}
