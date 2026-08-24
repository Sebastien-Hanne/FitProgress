<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'mapped' => false,
                'attr' => ['autocomplete' => 'current-password'],
                'constraints' => [new NotBlank(message: 'Saisissez votre mot de passe actuel.'), new UserPassword(message: 'Le mot de passe actuel est incorrect.')],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank(
                            message: 'Saisissez un nouveau mot de passe.',
                        ),
                        new Length(
                            min: 12,
                            minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                            // max length allowed by Symfony for security reasons
                            max: 4096,
                        ),
                        new PasswordStrength(),
                        new Regex(pattern: '/\p{Lu}/u', message: 'Le mot de passe doit contenir au moins une majuscule.'),
                        new Regex(pattern: '/\p{Ll}/u', message: 'Le mot de passe doit contenir au moins une minuscule.'),
                        new Regex(pattern: '/\p{N}/u', message: 'Le mot de passe doit contenir au moins un chiffre.'),
                        new Regex(pattern: '/[^\p{L}\p{N}\s]/u', message: 'Le mot de passe doit contenir au moins un caractère spécial.'),
                        new NotCompromisedPassword(),
                    ],
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'label' => 'Confirmer le nouveau mot de passe',
                ],
                'invalid_message' => 'Les deux mots de passe doivent être identiques.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
