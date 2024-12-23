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

namespace App\Platform\Transport\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Abstract class BaseController
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-03-19)
 * @package App\Controller
 */
abstract class BaseController extends AbstractController
{
    final public const string KEY_NAME_ENCODED = 'encoded';

    final public const string ROUTE_NAME_APP_INDEX = 'app_index';

    final public const string ROUTE_NAME_APP_IMPRESS = 'app_impress';

    final public const string ROUTE_NAME_APP_LOCATION = 'app_location';

    final public const string ROUTE_NAME_APP_LOCATION_VIEW = 'app_location_view';

    final public const string ROUTE_NAME_APP_CALENDAR_INDEX = 'app_calendar_index';

    final public const string ROUTE_NAME_APP_CALENDAR_INDEX_ENCODED = 'app_calendar_index_encoded';

    final public const string ROUTE_NAME_APP_CALENDAR_INDEX_ENCODED_SHORT = 'app_calendar_index_encoded_short';

    final public const array CONFIG_APP_CALENDAR_INDEX = [
        'path' => 'calendar',
        'pathShort' => 'c',
        'parameter' => [
            'hash' => 'string',
            'userId' => 'string',
            'calendarId' => 'integer',
        ],
        'parameterEncoded' => [
            self::KEY_NAME_ENCODED => 'string',
        ],
    ];

    final public const string ROUTE_NAME_APP_CALENDAR_DETAIL = 'app_calendar_detail';

    final public const string ROUTE_NAME_APP_CALENDAR_DETAIL_ENCODED = 'app_calendar_detail_encoded';

    final public const string ROUTE_NAME_APP_CALENDAR_DETAIL_ENCODED_SHORT = 'app_calendar_detail_encoded_short';

    final public const array CONFIG_APP_CALENDAR_DETAIL = [
        'path' => 'calendar/detail',
        'pathShort' => 'd',
        'parameter' => [
            'hash' => 'string',
            'userId' => 'string',
            'calendarImageId' => 'integer',
        ],
        'parameterEncoded' => [
            self::KEY_NAME_ENCODED => 'string',
        ],
    ];
}
