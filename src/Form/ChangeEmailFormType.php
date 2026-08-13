<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangeEmailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newEmail', EmailType::class, [
                'label' => 'Nouvelle adresse e-mail', 'mapped' => false,
                'attr' => ['autocomplete' => 'email'],
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
            ])
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel', 'mapped' => false,
                'attr' => ['autocomplete' => 'current-password'],
                'constraints' => [new Assert\NotBlank(), new UserPassword(message: 'Le mot de passe actuel est incorrect.')],
            ]);
    }
}
