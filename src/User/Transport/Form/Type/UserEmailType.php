<?php

declare(strict_types=1);

namespace App\User\Transport\Form\Type;

use App\User\Application\Validator\RegisteredUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @package App\User\Transport\Form
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class UserEmailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'placeholder.enter_email',
                ],
                'label' => 'label.email',
                'constraints' => [new Length([
                    'min' => 5,
                ]), new RegisteredUser()],
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
