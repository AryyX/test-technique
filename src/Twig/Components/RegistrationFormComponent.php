<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class RegistrationFormComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public bool $submitted = false;

    public function __construct(private RegistrationService $registrationService) {}

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(RegistrationFormType::class, new User());
    }

    #[LiveAction]
    public function save(): Response
    {
        $this->submitForm();
        $form = $this->getForm();

        if (!$form->isValid()) {
            return new Response();
        }

        /** @var User $user */
        $user = $form->getData();
        $this->registrationService->registerUser($user);
        $this->submitted = true;

        return $this->redirectToRoute('app_register_success');
    }
}
