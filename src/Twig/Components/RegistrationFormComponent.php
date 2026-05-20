<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveAction;

#[AsLiveComponent]
class RegistrationFormComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public bool $submitted = false;

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(RegistrationFormType::class, new User());
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        $form = $this->getForm();

        if (!$form->isValid()) {
            return;
        }

        /** @var User $user */
        $user = $form->getData();
        $user->setStatus('pending');
        $user->setCreatedAt(new \DateTime());

        if (!$user->getBirthDate()) {
            $birthDateString = $form->get('birthDate')->getViewData();
            if ($birthDateString) {
                $user->setBirthDate(new \DateTime($birthDateString));
            }
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->submitted = true;
    }
}
