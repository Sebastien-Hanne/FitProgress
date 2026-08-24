<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class ProfileAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom complet', 'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 100)]])
            ->add('phone', TelType::class, ['label' => 'Numéro de téléphone', 'required' => false, 'constraints' => [new Assert\Length(max: 30)]])
            ->add('photoFile', FileType::class, ['label' => 'Nouvelle photo de profil (optionnelle)', 'mapped' => false, 'required' => false, 'constraints' => [new Assert\File(maxSize: '3M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Choisissez une image JPG, PNG ou WebP.')]]);

        if ($options['show_health_fields']) {
            $builder->add('heightCm', IntegerType::class, ['label' => 'Taille (cm)', 'mapped' => false, 'constraints' => [new Assert\Range(min: 80, max: 250)]])
            ->add('targetWeight', NumberType::class, ['label' => 'Poids cible (kg)', 'mapped' => false, 'scale' => 1, 'constraints' => [new Assert\Range(min: 20, max: 500)]])
            ->add('birthDate', DateType::class, ['label' => 'Date de naissance', 'mapped' => false, 'widget' => 'single_text', 'input' => 'datetime_immutable', 'constraints' => [new Assert\NotBlank(), new Assert\LessThan('-12 years')]])
                ->add('gender', ChoiceType::class, [
                    'label' => 'Genre (optionnel)',
                    'mapped' => false,
                    'required' => false,
                    'placeholder' => 'Non renseigné (optionnel)',
                    'choices' => [
                        'Homme' => Gender::Male,
                        'Femme' => Gender::Female,
                        'Autre' => Gender::Other,
                        'Ne préfère pas répondre' => Gender::PreferNotToSay,
                    ],
                    'choice_value' => static fn (?Gender $gender): ?string => $gender?->value,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class, 'show_health_fields' => true]);
        $resolver->setAllowedTypes('show_health_fields', 'bool');
    }
}
