<?php

/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\XApi\ToolExperience\Activity;

use Xabbuh\XApi\Model\Activity;

/**
 * Class BaseActivity.
 *
 * @package Chamilo\PluginBundle\XApi\ToolExperience\Activity
 */
abstract class BaseActivity
{
    /**
     * @var \Chamilo\UserBundle\Entity\User
     */
    protected $user;
    /**
     * @var \App\CoreBundle\Entity\Course|null
     */
    protected $course;
    /**
     * @var \App\Session\Domain\Entity\Session|null
     */
    protected $session;

    abstract public function generate(): Activity;

    protected function generateIri(string $path, string $resource, array $params = []): string
    {
        $cidReq = api_get_cidreq();

        $url = api_get_path($path).$resource;

        if ($params) {
            $url .= '?'.http_build_query($params).'&';
        } elseif (empty($params) && $cidReq) {
            $url .= '?';
        }

        if ($cidReq) {
            $url .= $cidReq;
        }

        return $url;
    }
}
