<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function isPasswordValid(string $password, string $confirm): bool
    {
        if (strlen($password) < 12) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[0-9]/', $password)) return false;
        if (!preg_match('/[\W_]/', $password)) return false;
        if ($password !== $confirm) return false;

        return true;
    }

    public function getPasswordError(string $password, string $confirm): ?string
    {
        if (strlen($password) < 12) return 'Le mot de passe doit contenir au moins 12 caractères.';
        if (!preg_match('/[A-Z]/', $password)) return 'Le mot de passe doit contenir au moins une majuscule.';
        if (!preg_match('/[a-z]/', $password)) return 'Le mot de passe doit contenir au moins une minuscule.';
        if (!preg_match('/[0-9]/', $password)) return 'Le mot de passe doit contenir au moins un chiffre.';
        if (!preg_match('/[\W_]/', $password)) return 'Le mot de passe doit contenir au moins un caractère spécial.';
        if ($password !== $confirm) return 'Les mots de passe ne correspondent pas.';

        return null;
    }

    public function setPassword(User $user, string $password): void
    {
        $hashed = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashed);
        $user->setStatus('active');
        $user->setValidationToken(null);
        $user->setTokenExpiresAt(null);
        $this->em->flush();
    }
}
