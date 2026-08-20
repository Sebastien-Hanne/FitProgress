<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CoachSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choices' => $options['clients'],
                'choice_label' => 'name',
                'label' => 'Client',
                'placeholder' => 'Sélectionner un client',
            ])
            ->add('title', TextType::class, ['label' => 'Intitulé'])
            ->add('startAt', DateTimeType::class, [
                'label' => 'Date et horaire',
                'widget' => 'single_text',
            ])
            ->add('durationMinutes', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'attr' => ['min' => 15, 'max' => 480, 'step' => 15],
            ])
            ->add('location', TextType::class, ['label' => 'Lieu', 'required' => false])
            ->add('notes', TextareaType::class, ['label' => 'Notes', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Session::class, 'clients' => []]);
        $resolver->setAllowedTypes('clients', 'array');
    }
}
