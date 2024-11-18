<?php

declare(strict_types=1);

namespace App\Property\Transport\Form\Type;

use App\Property\Domain\Entity\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @package App\Property\Transport\Form\Type
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class CurrencyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currency_title', null, [
                'attr' => [
                    'autofocus' => true,
                ],
                'label' => 'label.currency_title',
            ])
            ->add('code', null, [
                'label' => 'label.code',
            ])
            ->add('symbol_left', null, [
                'label' => 'label.symbol_left',
            ])
            ->add('symbol_right', null, [
                'label' => 'label.symbol_right',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Currency::class,
        ]);
    }
}
