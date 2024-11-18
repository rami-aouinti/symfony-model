<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Command;

use App\CoreBundle\Framework\Container;
use Database;
use Doctrine\ORM\EntityManager;
use Notification;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SendNotificationsCommand
 *
 * @package App\CoreBundle\Command
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsCommand(
    name: 'app:send-notifications',
    description: 'Creates users and stores them in the database'
)]
class SendNotificationsCommand extends Command
{
    protected static $defaultName = 'app:send-notifications';

    public function __construct(
        private readonly EntityManager $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send notifications')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Enable debug mode')
            ->setHelp('This command sends notifications using the Notification class.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Database::setManager($this->em);

        $container = $this->getApplication()->getKernel()->getContainer();
        Container::setContainer($container);

        $io = new SymfonyStyle($input, $output);
        $debug = $input->getOption('debug');

        if ($debug) {
            error_log('Debug mode activated');
            $io->note('Debug mode activated');
        }

        $notification = new Notification();
        $notification->send();

        if ($debug) {
            error_log('Notifications have been sent.');
            $io->success('Notifications have been sent successfully.');
        }

        return Command::SUCCESS;
    }
}
