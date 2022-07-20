<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;

class EditProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')
            ->add('name')
            ->add('lastName')
            ->add('phone')
            ->add('email')
            ->add('plainPassword',RepeatedType::class,
                [
                    'type'=>PasswordType::class,
                    'required'=>false,
                    'first_options'=>['attr'=>['autocomplete'=>'password'],'label'=>'password'],
                    'mapped'=>false,
                    'second_options'=>['attr'=>['autocomplete'=>'confirmation password'],'label'=>'confirmation password'],
            ])
            ->add('campus',EntityType::class,['class'=>Campus::class,'choice_label'=>'name'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}