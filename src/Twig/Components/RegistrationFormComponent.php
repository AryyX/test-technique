<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\Component\HttpFoundation\Response;

#[AsLiveComponent]
class RegistrationFormComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public bool $submitted = false;

    public function __construct(private EntityManagerInterface $em) {}

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

        $user = $form->getData();
        $user->setStatus('pending');
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        return $this->redirectToRoute('app_register_success');
    }
}
