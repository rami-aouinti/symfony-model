<?php

declare(strict_types=1);

namespace App\User\Transport\Form\Type;

use App\User\Application\Validator\ConfirmPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @package App\User\Transport\Form
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', null, [
                'attr' => [
                    'autofocus' => true,
                ],
                'label' => 'label.new_password',
                'constraints' => [
                    new Length([
                        'min' => 5,
                    ])],
            ])
            ->add('password_confirmation', null, [
                'label' => 'label.confirm_password',
                'constraints' => [new Length([
                    'min' => 5,
                ]), new ConfirmPassword()],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
