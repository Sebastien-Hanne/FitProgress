<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => "Nom d'utilisateur",
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre nom.'),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir une adresse email.'),
                    new Email(message: 'Veuillez saisir une adresse email valide.'),
                ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',

                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],

                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],

                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir un mot de passe.'),
                    new Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    ),
                    new Regex(pattern: '/\p{Lu}/u', message: 'Le mot de passe doit contenir au moins une majuscule.'),
                    new Regex(pattern: '/\p{Ll}/u', message: 'Le mot de passe doit contenir au moins une minuscule.'),
                    new Regex(pattern: '/\p{N}/u', message: 'Le mot de passe doit contenir au moins un chiffre.'),
                    new Regex(pattern: '/[^\p{L}\p{N}\s]/u', message: 'Le mot de passe doit contenir au moins un caractère spécial.'),
                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => "J'accepte les conditions d'utilisation",
                'constraints' => [
                    new IsTrue(message: 'Vous devez accepter les conditions d’utilisation.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
