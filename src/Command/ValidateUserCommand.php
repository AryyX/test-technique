<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Enum\UserStatus;
use App\Service\UserValidationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:validate-user',
    description: 'Valide un utilisateur en attente et lui envoie un email de validation',
)]
class ValidateUserCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private UserValidationService $validationService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pendingUsers = $this->userRepository->findBy(['status' => UserStatus::Pending->value]);

        if (empty($pendingUsers)) {
            $io->info('Aucun utilisateur en attente de validation.');
            return Command::SUCCESS;
        }

        $io->title('Utilisateurs en attente de validation');

        $rows = [];
        foreach ($pendingUsers as $user) {
            $rows[] = [
                $user->getId(),
                $user->getFirstName() . ' ' . $user->getLastName(),
                $user->getFighterAlias(),
                $user->getEmail(),
                $user->getCreatedAt()->format('d/m/Y H:i'),
            ];
        }

        $io->table(['ID', 'Nom', 'Pseudo', 'Email', 'Inscrit le'], $rows);

        $userId = $io->ask('Entrez l\'ID de l\'utilisateur à valider');
        $user = $this->userRepository->find($userId);

        if (!$user || $user->getStatus() !== UserStatus::Pending->value) {
            $io->error('Utilisateur invalide ou déjà traité.');
            return Command::FAILURE;
        }

        if (!$io->confirm('Valider ' . $user->getFighterAlias() . ' ?', false)) {
            $io->warning('Validation annulée.');
            return Command::SUCCESS;
        }

        $this->validationService->validateUser($user);

        $io->success('Utilisateur validé et email envoyé à ' . $user->getEmail());

        return Command::SUCCESS;
    }
}
