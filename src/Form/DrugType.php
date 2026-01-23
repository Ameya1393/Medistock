<?php

namespace App\Form;

use App\Entity\Drug;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DrugType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('category', null, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('threshold', null, [
                'attr' => ['class' => 'form-control'],
            ])
            ->add('stockQuantity', null, [
                'attr' => ['class' => 'form-control', 'min' => 0],
                'label' => 'Initial Stock Quantity',
                'data' => 0,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Drug::class,
        ]);
    }
}
