<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [new NotBlank()],
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => false,
                'invalid_message' => 'Format de date invalide.',
                'constraints' => [new NotBlank(['message' => 'La date de naissance est obligatoire.'])],
                'getter' => function(User $user, FormInterface $form): ?string {
                    return $user->getBirthDate()?->format('Y-m-d');
                },
                'setter' => function(User &$user, mixed $value, FormInterface $form): void {
                    if ($value instanceof \DateTime) {
                        $user->setBirthDate($value);
                    } elseif (is_string($value) && $value) {
                        $user->setBirthDate(new \DateTime($value));
                    }
                },
            ])
            ->add('socialSecurityNumber', TextType::class, [
                'label' => 'Numéro de sécurité sociale',
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^[12][0-9]{14}$/',
                        'message' => 'Numéro de sécurité sociale invalide (15 chiffres)',
                    ]),
                ],
            ])
            ->add('fighterAlias', TextType::class, [
                'label' => 'Pseudo de combattant',
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])
            ->add('accreditationNumber', TextType::class, [
                'label' => 'Numéro d\'accréditation CERFA 666',
                'constraints' => [new NotBlank()],
            ])
            ->add('starterPokemon', ChoiceType::class, [
                'label' => 'Starter Pokémon ',
                'choices' => [
                    'Bulbizarre' => 'bulbasaur',
                    'Carapuce' => 'squirtle',
                    'Salamèche' => 'charmander',
                ],
                'constraints' => [new NotBlank()],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new NotBlank(), new Email()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
