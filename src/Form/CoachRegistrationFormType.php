<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;

class CoachRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom Prénom',
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez renseigner votre nom.'
                    ),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'Email Professionnel',
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir une adresse email.'
                    ),
                    new Email(
                        message: 'Veuillez saisir une adresse email valide.'
                    ),
                ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les mots de passe doivent être identiques.',

                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],

                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],

                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez entrer un mot de passe.'
                    ),
                    new Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'Votre mot de passe doit faire au moins {{ limit }} caractères.'
                    ),
                    new Regex(pattern: '/\p{Lu}/u', message: 'Le mot de passe doit contenir au moins une majuscule.'),
                    new Regex(pattern: '/\p{Ll}/u', message: 'Le mot de passe doit contenir au moins une minuscule.'),
                    new Regex(pattern: '/\p{N}/u', message: 'Le mot de passe doit contenir au moins un chiffre.'),
                    new Regex(pattern: '/[^\p{L}\p{N}\s]/u', message: 'Le mot de passe doit contenir au moins un caractère spécial.'),
                ],
            ])

            ->add('certification', TextType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Certification professionnelle',
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez renseigner votre certification professionnelle.'
                    ),
                    new Length(
                        min: 3,
                        max: 100,
                        minMessage: 'La certification doit faire au moins {{ limit }} caractères.',
                        maxMessage: 'La certification ne peut pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'Vous devez accepter les conditions générales.'
                    ),
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
