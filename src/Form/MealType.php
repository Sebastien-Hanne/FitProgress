<?php

namespace App\Form;

use App\Entity\Meal;
use App\Enum\MealType as MealKind;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MealType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => MealKind::cases(),
                'choice_label' => static fn (MealKind $type): string => $type->label(),
                'choice_value' => static fn (?MealKind $type): ?string => $type?->value,
                'label' => 'Type de repas',
            ])
            ->add('title', TextType::class, [
                'label' => 'Repas',
                'attr' => ['placeholder' => 'Ex. Porridge, poulet grillé…'],
            ])
            ->add('calories', IntegerType::class, [
                'label' => 'Calories',
                'required' => false,
                'attr' => ['min' => 0, 'inputmode' => 'numeric', 'placeholder' => '0'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Meal::class]);
    }
}
