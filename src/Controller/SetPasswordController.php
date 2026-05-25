<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Enum\UserStatus;
use App\Service\PasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SetPasswordController extends AbstractController
{
    public function __construct(private PasswordService $passwordService) {}

    #[Route('/set-password/{token}', name: 'app_set_password')]
    public function setPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
    ): Response {
        $user = $userRepository->findOneBy(['validationToken' => $token]);

        if (!$user || $user->getStatus() !== UserStatus::Validated->value) {
            throw $this->createNotFoundException('Lien invalide ou déjà utilisé.');
        }

        if ($user->getTokenExpiresAt() < new \DateTime()) {
            throw $this->createNotFoundException('Ce lien a expiré.');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');

            $error = $this->passwordService->getPasswordError($password, $confirm);

            if ($error === null) {
                $this->passwordService->setPassword($user, $password);
                return $this->redirectToRoute('app_portal');
            }
        }

        return $this->render('set_password/set_password.html.twig', [
            'error' => $error,
            'token' => $token,
        ]);
    }
}
