<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Component\Editor\CkEditor\Toolbar;

/**
 * TestProposedAnswer toolbar configuration.
 */
class TestProposedAnswer extends Basic
{
    /**
     * Get the toolbar config.
     *
     * @return array
     */
    public function getConfig()
    {
        $config['toolbarCanCollapse'] = true;
        $config['toolbarStartupExpanded'] = false;
        if (api_get_setting('more_buttons_maximized_mode') !== 'true') {
            $config['toolbar'] = $this->getNormalToolbar();
        } else {
            $config['toolbar_minToolbar'] = $this->getMinimizedToolbar();

            $config['toolbar_maxToolbar'] = $this->getMaximizedToolbar();
        }

        return $config;
    }

    /**
     * Get the toolbar configuration when CKEditor is maximized.
     *
     * @return array
     */
    protected function getMaximizedToolbar()
    {
        return $this->getNormalToolbar();
    }

    /**
     * Get the default toolbar configuration when the setting more_buttons_maximized_mode is false.
     *
     * @return array
     */
    protected function getNormalToolbar()
    {
        return [
            ['Bold', 'Subscript', 'Superscript'],
            [
                'Image',
                'Link',
                'Audio',
                'Table',
                'PasteFromWord',
                'inserthtml',
                api_get_setting('enabled_mathjax') === 'true' ? 'Mathjax' : '',
            ],
            ['Asciimath', 'Asciisvg'],
            ['Maximize', 'Source'],
        ];
    }

    /**
     * Get the toolbar configuration when CKEditor is minimized.
     *
     * @return array
     */
    protected function getMinimizedToolbar()
    {
        return $this->getNormalToolbar();
    }
}
