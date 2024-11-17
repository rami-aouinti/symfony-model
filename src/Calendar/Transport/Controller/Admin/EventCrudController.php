<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Calendar\Transport\Controller\Admin;

use App\Calendar\Transport\Controller\Admin\Base\BaseCrudController;
use App\Event\Domain\Entity\Event;
use App\User\Application\Service\SecurityService;
use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class EventCrudController.
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-12)
 * @package App\Controller\Admin
 */
class EventCrudController extends BaseCrudController
{
    /**
     * @throws Exception
     */
    public function __construct(SecurityService $securityService, TranslatorInterface $translator)
    {
        parent::__construct($securityService, $translator);
    }

    /**
     * Return fqcn of this class.
     */
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    /**
     * Returns the entity of this class.
     */
    #[Pure]
    public function getEntity(): string
    {
        return self::getEntityFqcn();
    }
}
