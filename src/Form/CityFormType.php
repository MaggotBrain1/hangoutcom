<?php

namespace App\Form;

use App\Entity\City;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,[
                'label'=>false,
                'attr' => ['class' => 'inptFormCityAdd'],
                'row_attr' => ['class' => 'rowInptCityAdd'] ,

            ])
            ->add('zipCode',IntegerType::class,[
                'label'=>false,
                'attr' => ['class' => 'inptFormZipAdd'],
                'row_attr' => ['class' => 'rowInptCityAdd'] ,

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => City::class,
        ]);
    }
}
