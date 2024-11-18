<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Settings;

use App\CoreBundle\Settings\AbstractSettingsSchema;
use Sylius\Bundle\SettingsBundle\Schema\AbstractSettingsBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class NotebookCourseSettingsSchema extends AbstractSettingsSchema
{
    public function buildSettings(AbstractSettingsBuilder $builder): void
    {
        /*$builder
            ->setDefaults([
                'enabled' => '',
            ])
        ;*/
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        /*$builder
            ->add('enabled', 'yes_no')
        ;*/
    }
}
