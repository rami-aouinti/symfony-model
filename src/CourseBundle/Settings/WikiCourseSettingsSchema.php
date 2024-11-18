<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CourseBundle\Settings;

use App\CoreBundle\Form\Type\YesNoNumericType;
use App\CoreBundle\Form\Type\YesNoType;
use App\CoreBundle\Settings\AbstractSettingsSchema;
use Sylius\Bundle\SettingsBundle\Schema\AbstractSettingsBuilder;
use Symfony\Component\Form\FormBuilderInterface;

class WikiCourseSettingsSchema extends AbstractSettingsSchema
{
    public function buildSettings(AbstractSettingsBuilder $builder): void
    {
        $builder
            ->setDefaults([
                'enabled' => 1,
                'wiki_categories_enabled' => 'false',
                'wiki_html_strict_filtering' => 'false',
            ])
        ;
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add('enabled', YesNoNumericType::class)
            ->add('wiki_categories_enabled', YesNoType::class)
            ->add('wiki_html_strict_filtering', YesNoType::class)
        ;
    }
}
