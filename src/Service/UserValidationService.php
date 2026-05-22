<?php

namespace App\Service;

use App\Entity\User;
use App\Enum\UserStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserValidationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function validateUser(User $user): void
    {
        $user->setInternalId($this->generateInternalId());
        $user->setStatus(UserStatus::Validated->value);
        $user->setValidationToken($this->generateToken());
        $user->setTokenExpiresAt(new \DateTime('+24 hours'));

        $this->em->flush();
        $this->sendValidationEmail($user);
    }

    private function generateInternalId(): string
    {
        return 'FC-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function sendValidationEmail(User $user): void
    {
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
    }
}
