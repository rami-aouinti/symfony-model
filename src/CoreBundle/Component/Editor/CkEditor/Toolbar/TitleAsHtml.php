<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Component\Editor\CkEditor\Toolbar;

/**
 * Toolbar used to allow titles to have an HTML format.
 */
class TitleAsHtml extends Basic
{
    public function getConfig()
    {
        $config['toolbar'] = [
            [
                'name' => 'clipboard',
                'groups' => ['clipboard', 'undo'],
                'items' => ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'],
            ],
            [
                'name' => 'basicstyles',
                'groups' => ['basicstyles', 'cleanup'],
                'items' => ['Bold', 'Italic', 'Underline', 'Strike', 'TextColor', 'BGColor'],
            ],
            //            [
            //                'name' => 'paragraph',
            //                'groups' => ['list', 'indent', 'blocks', 'align', 'bidi'],
            //                'items' => ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
            //            ],
            [
                'name' => 'links',
                'items' => ['Link', 'Unlink', 'Source'],
            ],
        ];

        $config['height'] = '100';

        return $config;
    }
}
