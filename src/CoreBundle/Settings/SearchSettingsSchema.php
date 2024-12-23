<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Settings;

use App\CoreBundle\Form\Type\YesNoType;
use Sylius\Bundle\SettingsBundle\Schema\AbstractSettingsBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchSettingsSchema extends AbstractSettingsSchema
{
    public function buildSettings(AbstractSettingsBuilder $builder): void
    {
        $builder
            ->setDefaults(
                [
                    'search_enabled' => 'false',
                    'search_prefilter_prefix' => '',
                    'search_show_unlinked_results' => 'true',
                ]
            )
        ;
        $allowedTypes = [];
        $this->setMultipleAllowedTypes($allowedTypes, $builder);
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('search_enabled', YesNoType::class)
            ->add('search_prefilter_prefix', YesNoType::class)
            ->add(
                'search_show_unlinked_results',
                ChoiceType::class,
                [
                    'choices' => [
                        'Search shows unlinked results' => 'true',
                        'Search hides unlinked results' => 'false',
                    ],
                ]
            )
        ;

        $this->updateFormFieldsFromSettingsInfo($builder);
    }
}
