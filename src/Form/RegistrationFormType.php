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

            ])
            ->add('email', EmailType::class, [
                'label' => 'email',
                'required' => true,
                'attr' => ['class' => 'toto'], // Ajout de classe à la mano
                'row_attr' => ['class' => 'toto'] // Pour utiliser classe Bootstrap
            ])
            ->add("name", TextType::class, [
                'label' => 'name',
                'required' => true
            ])
            ->add("lastName", TextType::class, [
                'label' => 'lastname',
                'required' => true
            ])
            ->add("phone", TextType::class, [
                'label' => 'phone',
                'required' => true
            ])
            ->add("campus", EntityType::class, [
                'label' => 'campus',
                'class' => Campus::class,
                'choice_label' => 'name',
                'required' => true
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
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
