<?php

declare(strict_types=1);

namespace App\Place\Transport\Form\EventSubscriber;

use App\Place\Domain\Entity\District;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @package App\Form\EventSubscriber
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class AddDistrictFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'onCitySelected',
        ];
    }

    public function onCitySelected(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();
        $city = $data->getCity();

        if ($city) {
            $form->add('district', EntityType::class, [
                'class' => District::class,
                'placeholder' => 'placeholder.select_district',
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
                'label' => 'label.district',
                'choices' => $city->getDistricts(),
            ]);
        }
    }
}
