<?php

declare(strict_types=1);

namespace App\Property\Transport\Form\Type;

use App\Property\Domain\Entity\Feature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package App\Property\Transport\Form\Type
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class FeatureType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'attr' => [
                    'autofocus' => true,
                ],
                'label' => 'label.feature',
            ])
            ->add('icon', null, [
                'attr' => [
                    'rows' => '1',
                    'placeholder' => 'placeholder.example_icon',
                ],
                'label' => 'label.custom_icon',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feature::class,
        ]);
    }
}
