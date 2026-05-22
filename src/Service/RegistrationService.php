<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function registerUser(User $user): void
    {
        $user->setStatus('pending');
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();
    }
}
