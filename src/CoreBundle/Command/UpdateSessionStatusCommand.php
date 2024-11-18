<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Command;

use App\CoreBundle\Repository\SessionRepository;
use App\Session\Domain\Entity\Session;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateSessionStatusCommand
 *
 * @package App\CoreBundle\Command
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsCommand(
    name: 'app:update-session-status',
    description: 'Creates users and stores them in the database'
)]
class UpdateSessionStatusCommand extends Command
{
    protected static $defaultName = 'app:update-session-status';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SessionRepository $sessionRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Updates the status of training sessions based on their dates and user count.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Enable debug mode');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $debug = $input->getOption('debug');
        $lineBreak = PHP_SAPI === 'cli' ? PHP_EOL : '<br />';

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $io->text('Today is: ' . $now->format('Y-m-d H:i:s') . $lineBreak);

        $sessions = $this->sessionRepository->findAll();

        foreach ($sessions as $session) {
            $id = $session->getId();
            $start = $session->getDisplayStartDate();
            $end = $session->getDisplayEndDate();
            $userCount = $this->sessionRepository->countUsersBySession($session->getId());

            $status = $this->determineSessionStatus($start, $end, $userCount, $now);

            if ($debug) {
                $startFormatted = $start ? $start->format('Y-m-d H:i:s') : 'N/A';
                $endFormatted = $end ? $end->format('Y-m-d H:i:s') : 'N/A';
                $io->note("Session #{$id}: Start date: {$startFormatted} - End date: {$endFormatted}");
            }

            $session->setStatus($status);
            $this->sessionRepository->update($session);
        }

        if ($debug) {
            $io->success('Session statuses have been updated in debug mode (changes are not saved).');
        } else {
            $this->entityManager->flush();
            $io->success('Session statuses have been updated successfully.');
        }

        return Command::SUCCESS;
    }

    /**
     * Determines the status of a session based on its start/end dates and user count.
     */
    private function determineSessionStatus(?DateTime $start, ?DateTime $end, int $userCount, DateTime $now): int
    {
        if ($start > $now) {
            return Session::STATUS_PLANNED;
        }

        if ($userCount >= 2 && $start <= $now && $end > $now) {
            return Session::STATUS_PROGRESS;
        }

        if ($userCount === 0 && $now > $start) {
            return Session::STATUS_CANCELLED;
        }

        if ($now > $end && $userCount >= 2) {
            return Session::STATUS_FINISHED;
        }

        return Session::STATUS_UNKNOWN;
    }
}
