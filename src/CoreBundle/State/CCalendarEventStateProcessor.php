<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Calendar\Domain\Entity\CCalendarEvent;
use App\CoreBundle\Entity\AgendaReminder;
use App\CoreBundle\Entity\User\User;
use App\CoreBundle\Settings\SettingsManager;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

final class CCalendarEventStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    /**
     * @param mixed $data
     *
     * @throws Exception
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): CCalendarEvent
    {
        \assert($data instanceof CCalendarEvent);

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $data->setCreator($currentUser);

        if ($this->isPersonalEvent($data)) {
            if ($currentUser->getResourceNode()->getId() !== $data->getParentResourceNode()) {
                throw new Exception('Not allowed');
            }
        }

        $data->getReminders()->forAll(function (int $i, AgendaReminder $reminder) {
            return $reminder->decodeDateInterval();
        });

        /** @var CCalendarEvent $result */
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function isPersonalEvent(CCalendarEvent $event): bool
    {
        $type = 'personal';

        if (!empty($event->getResourceLinkArray())) {
            foreach ($event->getResourceLinkArray() as $link) {
                if (isset($link['cid'])) {
                    $type = 'course';

                    break;
                }
            }
        }

        return $type === 'personal';
    }
}
