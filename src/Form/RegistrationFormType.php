<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo',TextType::class,[
                'label'=>'pseudo',
                'required'=>true,
                'attr' => ['class' => 'inptRegsterForm'],// attr pour l'input
                'row_attr' => ['class' => 'rowInpt'] ,// att pour la row de l'input


            ])
            ->add('email', EmailType::class, [
                'label' => 'email',
                'required' => true,
                'attr' => ['class' => 'inptRegsterForm'],
                'row_attr' => ['class' => 'rowInpt'] ,
            ])
            ->add("name", TextType::class, [
                'label' => 'name',
                'required' => true,
                'attr' => ['class' => 'inptRegsterForm'],
                'row_attr' => ['class' => 'rowInpt'] ,

            ])
            ->add("lastName", TextType::class, [
                'label' => 'lastname',
                'required' => true,
                'attr' => ['class' => 'inptRegsterForm'],
                'row_attr' => ['class' => 'rowInpt'] ,

            ])
            ->add("phone", TextType::class, [
                'label' => 'phone',
                'required' => true,
                'attr' => ['class' => 'inptRegsterForm'],
                'row_attr' => ['class' => 'rowInpt'] ,

            ])
            ->add("campus", EntityType::class, [
                'label' => 'campus',
                'class' => Campus::class,
                'choice_label' => 'name',
                'required' => true,
                'attr' => ['class' => 'inptRegsterForm'],
                'row_attr' => ['class' => 'rowInpt'] ,

            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,

                'row_attr' => ['class' => 'rowInpt'] ,
                'attr' => ['autocomplete' => 'new-password','class' => 'inptRegsterForm'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])

            ->add("isActive", HiddenType::class, [
                'label' => 'isActive',
                'data' => true     // 1 marche aussi
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'attr' => ['class' => 'agreeT'],
                'row_attr' => ['class' => 'rowAgreeT'] ,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
