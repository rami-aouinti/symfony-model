<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Settings;

use App\CoreBundle\Form\Type\YesNoType;
use Sylius\Bundle\SettingsBundle\Schema\AbstractSettingsBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class GlossarySettingsSchema extends AbstractSettingsSchema
{
    public function buildSettings(AbstractSettingsBuilder $builder): void
    {
        $builder
            ->setDefaults(
                [
                    'show_glossary_in_extra_tools' => '',
                    'default_glossary_view' => 'table',
                    'allow_remove_tags_in_glossary_export' => 'false',
                ]
            )
        ;

        $allowedTypes = [
            'show_glossary_in_extra_tools' => ['string'],
        ];
        $this->setMultipleAllowedTypes($allowedTypes, $builder);
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add(
                'show_glossary_in_extra_tools',
                ChoiceType::class,
                [
                    'choices' => [
                        'None' => 'none',
                        'Exercise' => 'exercise',
                        'Learning path' => 'lp',
                        'Exercise and learning path' => 'exercise_and_lp',
                    ],
                ]
            )
            ->add(
                'default_glossary_view',
                ChoiceType::class,
                [
                    'choices' => [
                        'Table' => 'table',
                        'List' => 'list',
                    ],
                ]
            )
            ->add('allow_remove_tags_in_glossary_export', YesNoType::class)
        ;

        $this->updateFormFieldsFromSettingsInfo($builder);
    }
}
