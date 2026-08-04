<?php

namespace App\Form;

use App\Entity\JournalEntry;
use App\Enum\EnergyLevel;
use App\Enum\Mood;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JournalEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mood', ChoiceType::class, [
                'choices' => Mood::cases(),
                'choice_label' => static fn (Mood $mood): string => $mood->label(),
                'choice_value' => static fn (?Mood $mood): ?string => $mood?->value,
                'required' => false,
                'label' => 'Humeur',
                'expanded' => true,
            ])
            ->add('energyLevel', ChoiceType::class, [
                'choices' => EnergyLevel::cases(),
                'choice_label' => static fn (EnergyLevel $level): string => $level->label(),
                'choice_value' => static fn (?EnergyLevel $level): ?string => $level === null ? null : (string) $level->value,
                'required' => false,
                'label' => 'Niveau d’énergie',
                'expanded' => true,
            ])
            ->add('sleepHours', NumberType::class, [
                'label' => 'Durée du sommeil',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'attr' => ['min' => 0, 'max' => 24, 'step' => '0.25', 'inputmode' => 'decimal'],
            ])
            ->add('sleepQuality', ChoiceType::class, [
                'label' => 'Qualité du sommeil',
                'required' => false,
                'choices' => [
                    'Très mauvaise' => 1,
                    'Mauvaise' => 2,
                    'Moyenne' => 3,
                    'Bonne' => 4,
                    'Excellente' => 5,
                ],
                'expanded' => true,
            ])
            ->add('waterIntakeMl', IntegerType::class, [
                'label' => 'Hydratation',
                'required' => false,
                'attr' => ['min' => 0, 'step' => 100, 'inputmode' => 'numeric'],
            ])
            ->add('activityMinutes', IntegerType::class, [
                'label' => 'Activité physique',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 1440, 'inputmode' => 'numeric'],
            ])
            ->add('weight', NumberType::class, [
                'label' => 'Poids',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'attr' => ['min' => 1, 'max' => 500, 'step' => '0.1', 'inputmode' => 'decimal'],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Réflexions',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'maxlength' => 5000,
                    'placeholder' => 'Comment vous êtes-vous senti aujourd’hui ? Avez-vous rencontré des difficultés particulières ou remporté des victoires ?',
                ],
            ])
            ->add('meals', CollectionType::class, [
                'entry_type' => MealType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => JournalEntry::class]);
    }
}
