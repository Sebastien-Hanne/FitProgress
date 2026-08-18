<?php

namespace App\Form;

use App\Entity\CoachProfile;
use App\Enum\CoachingStyle;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class CoachProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bio', TextareaType::class, ['label' => 'Bio professionnelle', 'attr' => ['maxlength' => 500], 'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 500)]])
            ->add('specialties', TextType::class, ['label' => 'Spécialités', 'help' => 'Séparez les spécialités par des virgules.', 'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 150)]])
            ->add('coachingStyle', ChoiceType::class, ['choices' => CoachingStyle::cases(), 'expanded' => true, 'label' => 'Style de coaching', 'choice_label' => static fn (CoachingStyle $style) => match ($style) { CoachingStyle::Analytical => 'Analytique', CoachingStyle::Motivational => 'Motivation', CoachingStyle::Structured => 'Structuré' }, 'choice_value' => static fn (?CoachingStyle $style) => $style?->value])
            ->add('experienceYears', IntegerType::class, ['label' => 'Années d’expérience', 'required' => false, 'constraints' => [new Assert\Range(min: 0, max: 80)]])
            ->add('maxCapacity', RangeType::class, ['label' => 'Capacité maximale', 'attr' => ['min' => 1, 'max' => 30], 'constraints' => [new Assert\Range(min: 1, max: 30)]])
            ->add('isAvailable', CheckboxType::class, ['label' => 'Disponible pour de nouveaux clients', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CoachProfile::class]);
    }
}
