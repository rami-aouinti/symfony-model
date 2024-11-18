<?php

declare(strict_types=1);

namespace App\Place\Transport\Form\EventSubscriber;

use App\Place\Domain\Entity\Neighborhood;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @package App\Form\EventSubscriber
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class UpdateNeighborhoodFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'onCityChanged',
        ];
    }

    public function onCityChanged(FormEvent $event): void
    {
        $form = $event->getForm();

        if ($form->getData()) {
            // Neighborhoods of the city
            $neighborhoods = $form->getData()->getNeighborhoods();

            $form->getParent()->add('neighborhood', EntityType::class, [
                'class' => Neighborhood::class,
                'placeholder' => 'placeholder.select_neighborhood',
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
                'label' => 'label.neighborhood',
                'choices' => $neighborhoods,
            ]);
        }
    }
}
