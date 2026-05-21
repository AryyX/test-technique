<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SetPasswordController extends AbstractController
{
    #[Route('/set-password/{token}', name: 'app_set_password')]
    public function setPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = $userRepository->findOneBy(['validationToken' => $token]);

        if (!$user || $user->getStatus() !== 'validated') {
            throw $this->createNotFoundException('Lien invalide ou déjà utilisé.');
        }

        if ($user->getTokenExpiresAt() < new \DateTime()) {
            throw $this->createNotFoundException('Ce lien a expiré.');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');

            if (strlen($password) < 12) {
                $error = 'Le mot de passe doit contenir au moins 12 caractères.';
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $error = 'Le mot de passe doit contenir au moins une majuscule.';
            } elseif (!preg_match('/[a-z]/', $password)) {
                $error = 'Le mot de passe doit contenir au moins une minuscule.';
            } elseif (!preg_match('/[0-9]/', $password)) {
                $error = 'Le mot de passe doit contenir au moins un chiffre.';
            } elseif (!preg_match('/[\W_]/', $password)) {
                $error = 'Le mot de passe doit contenir au moins un caractère spécial.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $hashed = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashed);
                $user->setStatus('active');
                $user->setValidationToken(null);
                $user->setTokenExpiresAt(null);
                $em->flush();

                return $this->redirectToRoute('app_portal');
            }
        }

        return $this->render('set_password/set_password.html.twig', [
            'error' => $error,
            'token' => $token,
        ]);
    }
}
