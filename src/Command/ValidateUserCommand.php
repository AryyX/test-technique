<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:validate-user',
    description: 'Valide un utilisateur en attente et lui envoie un email de validation',
)]
class ValidateUserCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Valide un utilisateur en attente d\'inscription');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pendingUsers = $this->userRepository->findBy(['status' => 'pending']);

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

        if (!$user || $user->getStatus() !== 'pending') {
            $io->error('Utilisateur invalide ou déjà traité.');
            return Command::FAILURE;
        }

        $io->confirm('Valider ' . $user->getFighterAlias() . ' ?', true);

        // Génération internalId et token
        $user->setInternalId('FC-' . strtoupper(substr(md5(uniqid()), 0, 8)));
        $user->setStatus('validated');
        $user->setValidationToken(bin2hex(random_bytes(32)));
        $user->setTokenExpiresAt(new \DateTime('+24 hours'));

        $this->em->flush();

        // Envoi email
        $link = $this->urlGenerator->generate(
            'app_set_password',
            ['token' => $user->getValidationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new Email())
            ->from('noreply@fightclubportal.com')
            ->to($user->getEmail())
            ->subject('Votre inscription au FightClubPortal a été validée')
            ->html(
                '<h1>Bienvenue ' . $user->getFighterAlias() . '</h1>' .
                '<p>Votre inscription a été validée. Cliquez sur le lien ci-dessous pour créer votre mot de passe :</p>' .
                '<a href="' . $link . '">Créer mon mot de passe</a>' .
                '<p>Ce lien expire dans 24 heures.</p>'
            );

        $this->mailer->send($email);

        $io->success('Utilisateur validé et email envoyé à ' . $user->getEmail());

        return Command::SUCCESS;
    }
}
