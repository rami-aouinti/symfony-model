<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\Twig;

use App\CoreBundle\Settings\SettingsManager;
use Sylius\Bundle\SettingsBundle\Templating\Helper\SettingsHelper as SylusSettingsHelper;

/**
 * Class SettingsHelper
 *
 * @package App\CoreBundle\Twig
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class SettingsHelper extends SylusSettingsHelper
{
    public function getName(): string
    {
        return 'chamilo_settings';
    }

    /**
     * @param string $parameter Example: `platform.setting`
     */
    public function getSettingsParameter(string $parameter)
    {
        return $this->settingsManager instanceof SettingsManager ? $this->settingsManager->getSetting($parameter) : '';
    }
}
