<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: Valery Maslov
 * Date: 15.08.2018
 * Time: 19:55.
 */

namespace App\Place\Transport\Form\Type;

use App\Place\Domain\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package App\Place\Transport\Form\Type
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class CityType extends AbstractType
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
                'label' => 'label.name',
            ])
            ->add('slug', null, [
                'label' => 'label.slug',
            ])
            ->add('title', null, [
                'label' => 'label.title',
            ])
            ->add('meta_title', null, [
                'label' => 'label.meta_title',
            ])
            ->add('meta_description', TextareaType::class, [
                'label' => 'label.meta_description',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
