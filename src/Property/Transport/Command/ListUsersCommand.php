<?php

declare(strict_types=1);

namespace App\Property\Transport\Command;

use App\User\Domain\Entity\User;
use App\User\Infrastructure\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package App\Property\Transport\Command
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsCommand(
    name: 'app:list-users',
    description: 'Lists all the existing users',
)]
final class ListUsersCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'limit',
            null,
            InputOption::VALUE_OPTIONAL,
            'Limits the number of users listed',
            50
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = $input->getOption('limit');

        $allUsers = $this->users->findBy([], [
            'id' => 'DESC',
        ], $limit);

        $usersAsPlainArrays = array_map(
            fn (User $user) => [
                $user->getId(),
                $user->getProfile()->getFullName(),
                $user->getUsername(),
                $user->getEmail(),
                implode(', ', $user->getRoles()),
            ],
            $allUsers
        );

        $bufferedOutput = new BufferedOutput();
        $io = new SymfonyStyle($input, $bufferedOutput);
        $io->table(
            ['ID', 'Full Name', 'Username', 'Email', 'Roles'],
            $usersAsPlainArrays
        );

        $usersAsATable = $bufferedOutput->fetch();
        $output->write($usersAsATable);

        return Command::SUCCESS;
    }
}
