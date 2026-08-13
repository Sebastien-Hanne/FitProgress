<?php

namespace App\Form;

use App\Entity\Goal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class OnboardingGoalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heightCm', IntegerType::class, ['label' => 'Quelle est votre taille ?', 'constraints' => [new Assert\NotBlank(), new Assert\Range(min: 80, max: 250)]])
            ->add('initialWeight', NumberType::class, ['label' => 'Quel est votre poids actuel ?', 'scale' => 1, 'constraints' => [new Assert\NotBlank(), new Assert\Range(min: 20, max: 500)]])
            ->add('targetWeight', NumberType::class, ['label' => 'Quel est votre poids cible ?', 'scale' => 1, 'constraints' => [new Assert\NotBlank(), new Assert\Range(min: 20, max: 500)]])
            ->add('birthDate', DateType::class, ['label' => 'Quelle est votre date de naissance ?', 'widget' => 'single_text', 'input' => 'datetime_immutable', 'constraints' => [new Assert\NotBlank(), new Assert\LessThan('-12 years')]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Goal::class]);
    }
}
