<?php

namespace App\Form;

use App\Entity\Consumption;
use App\Entity\Drug;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsumptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('drug', EntityType::class, [
                'class' => Drug::class,
                'choice_label' => 'name',
                'attr' => ['class' => 'form-control'],
                'placeholder' => 'Select a drug',
            ])
            ->add('quantity', IntegerType::class, [
                'attr' => ['class' => 'form-control', 'min' => 1],
                'label' => 'Quantity Used',
            ])
            ->add('consumedAt', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Date & Time',
            ])
            ->add('loggedBy', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Logged By',
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consumption::class,
        ]);
    }
}










