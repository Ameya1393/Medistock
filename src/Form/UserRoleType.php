<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['data'];
        $currentRole = $user->isAdmin() ? 'ROLE_ADMIN' : 'ROLE_USER';

        $builder
            ->add('role', ChoiceType::class, [
                'label' => 'User Role',
                'choices' => [
                    'Admin (Full Access)' => 'ROLE_ADMIN',
                    'Ground Level (Limited Access)' => 'ROLE_USER',
                ],
                'data' => $currentRole,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

