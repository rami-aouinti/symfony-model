<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Settings;

use App\CoreBundle\Form\Type\YesNoType;
use Sylius\Bundle\SettingsBundle\Schema\AbstractSettingsBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class WebServiceSettingsSchema extends AbstractSettingsSchema
{
    public function buildSettings(AbstractSettingsBuilder $builder): void
    {
        $builder
            ->setDefaults(
                [
                    'messaging_allow_send_push_notification' => 'false',
                    'messaging_gdc_project_number' => '',
                    'messaging_gdc_api_key' => '',
                    'allow_download_documents_by_api_key' => 'false',
                ]
            )
        ;
        $allowedTypes = [
            // commenting this line allows setting to be null
            // 'header_extra_content' => array('string'),
            // 'footer_extra_content' => array('string'),
            // 'messaging_gdc_project_number' => ['string'],
            // 'messaging_gdc_api_key' => ['string'],
        ];
        $this->setMultipleAllowedTypes($allowedTypes, $builder);
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('messaging_allow_send_push_notification', YesNoType::class)
            ->add('messaging_gdc_project_number')
            ->add('messaging_gdc_api_key')
            ->add('allow_download_documents_by_api_key', YesNoType::class)
        ;

        $this->updateFormFieldsFromSettingsInfo($builder);
    }
}
