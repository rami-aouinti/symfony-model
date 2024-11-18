<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Controller;

use App\CoreBundle\Controller\BaseController;
use App\CoreBundle\Traits\ControllerTrait;
use App\CoreBundle\Traits\CourseControllerTrait;

/**
 * Each entity controller must extend this class.
 */
abstract class ToolBaseController extends BaseController implements CourseControllerInterface
{
    use ControllerTrait;
    use CourseControllerTrait;
}
