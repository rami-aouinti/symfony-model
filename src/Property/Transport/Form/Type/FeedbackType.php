<?php

declare(strict_types=1);

namespace App\Property\Transport\Form\Type;

use App\Platform\Application\Dto\FeedbackDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackType
 *
 * @package App\Form\Type
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('from_name', null, [
                'label' => 'label.name',
            ])
            ->add('from_email', null, [
                'attr' => [
                    'placeholder' => 'placeholder.enter_email',
                ],
                'label' => 'label.email',
            ])
            ->add('message', TextareaType::class, [
                'attr' => [
                    'rows' => '7',
                ],
                'label' => 'label.message',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FeedbackDto::class,
        ]);
    }
}
