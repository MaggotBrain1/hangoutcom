<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;

class EditProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo',TextType::class,[
                'label'=>false,
                'required' => true,

            ])
            ->add('name',TextType::class,[
                'label'=>false,
                 'required' => true,

            ])
            ->add('lastName',TextType::class,[
                'label'=>false,
                'required' => true,

            ])
            ->add('phone',TextType::class,[
                'label'=>false,
                'required' => true,

            ])
            ->add('email',TextType::class,[
                'label'=>false,
                 'required' => true,

            ])
            ->add('password',PasswordType::class,[
                'label'=>false,
                'required' => false,

            ])
            ->add('plainPassword',PasswordType::class,[
                'mapped'=>false,
                'required' => false,

            ],
                )
            /*->add('plainPassword',RepeatedType::class,
                [
                    'type'=>PasswordType::class,
                    'required'=>false,
                    'first_options'=>['attr'=>['autocomplete'=>'password'],'label_attr'=>['style'=>'display:none']],
                    'mapped'=>false,
                    'second_options'=>['attr'=>['autocomplete'=>'confirmation password'],'label_attr'=>['style'=>'display:none']],
            ])*/
            ->add('campus',EntityType::class,
                [
                    'class'=>Campus::class,
                    'choice_label'=>'name'
                ])

            ->add('image',FileType::class,
                [
                'required'=>false,'label'=>'ajouter une photo de profil :',
                'mapped'=>false,
                'constraints'=>[new File([
                     'maxSize'=>'4096k',
                    'mimeTypes'=>[
                       'image/png'
                     ,'image/jpeg']
                ,
                    'mimeTypesMessage'=>'l\'image doit être au format jpg ou png']
                )]
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
