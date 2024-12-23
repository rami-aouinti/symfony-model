<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Settings;

use App\CoreBundle\Form\Type\YesNoType;
use Sylius\Bundle\SettingsBundle\Schema\AbstractSettingsBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class DropboxSettingsSchema extends AbstractSettingsSchema
{
    public function buildSettings(AbstractSettingsBuilder $builder): void
    {
        $builder
            ->setDefaults(
                [
                    'dropbox_allow_overwrite' => 'true',
                    'dropbox_max_filesize' => '100000000',
                    'dropbox_allow_just_upload' => 'true',
                    'dropbox_allow_student_to_student' => 'true',
                    'dropbox_allow_group' => 'true',
                    'dropbox_allow_mailing' => 'false',
                    'dropbox_hide_course_coach' => 'false',
                    'dropbox_hide_general_coach' => 'false',
                ]
            )
        ;

        $allowedTypes = [
            'dropbox_allow_overwrite' => ['string'],
        ];
        $this->setMultipleAllowedTypes($allowedTypes, $builder);
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('dropbox_allow_overwrite', YesNoType::class)
            ->add('dropbox_max_filesize')
            ->add('dropbox_allow_just_upload', YesNoType::class)
            ->add('dropbox_allow_student_to_student', YesNoType::class)
            ->add('dropbox_allow_group', YesNoType::class)
            ->add('dropbox_allow_mailing', YesNoType::class)
            ->add('dropbox_hide_course_coach', YesNoType::class)
            ->add('dropbox_hide_general_coach', YesNoType::class)
        ;

        $this->updateFormFieldsFromSettingsInfo($builder);
    }
}
