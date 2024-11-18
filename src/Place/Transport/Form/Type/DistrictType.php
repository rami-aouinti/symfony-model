<?php

declare(strict_types=1);

namespace App\Place\Transport\Form\Type;

use App\Place\Domain\Entity\City;
use App\Place\Domain\Entity\District;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package App\Place\Transport\Form\Type
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class DistrictType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'label' => 'city',
            ])
            ->add('name', null, [
                'label' => 'label.district_name',
            ])
            ->add('slug', null, [
                'label' => 'label.slug',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => District::class,
        ]);
    }
}
