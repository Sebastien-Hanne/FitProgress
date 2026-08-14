<?php

namespace App\Form;

use App\Entity\Goal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class GoalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('targetWeight', NumberType::class, [
                'label' => 'Poids cible (kg)',
                'scale' => 1,
                'constraints' => [new Assert\NotBlank(), new Assert\Range(min: 20, max: 500)],
            ])
            ->add('targetDate', DateType::class, [
                'label' => 'Date cible',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Goal::class]);
    }
}
