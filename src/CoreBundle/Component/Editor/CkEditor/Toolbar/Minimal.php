<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Component\Editor\CkEditor\Toolbar;

/**
 * CKEditor Minimal toolbar.
 */
class Minimal extends Basic
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
                'items' => ['Bold', 'Italic', 'Underline', 'Strike', 'TextColor'],
            ],
            [
                'name' => 'paragraph',
                'groups' => ['list', 'indent', 'blocks', 'align', 'bidi'],
                'items' => ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
            ],
            [
                'name' => 'links',
                'items' => ['Link', 'Unlink', 'Anchor', 'Source'],
            ],
        ];

        return $config;
    }
}
