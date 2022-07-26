<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Hangout;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campusOrganizerSite', EntityType::class, [
                'class' => Campus::class,
                'query_builder' => function (CampusRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'label'=>false,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Quel campus ?',
                'attr' => ['class' => 'inptFilter'],
            ])
            ->add('name', SearchType::class, [
                'required' => false,
                'label'=>false,
                'attr' => [
                    'class' => 'inptFilter',
                    'placeholder' => 'Le nom de la sortie contient',
                ],
            ])
            ->add('startDate', DateType::class, [
                'mapped' => false,
                'required' => false,
                'widget' => 'single_text',
                'label'=>false,
                'attr' => [
                    'class' => 'inptFilter',
                    'color' => 'white',
                ],

            ])
            ->add('endDate', DateType::class, [
                'mapped' => false,
                'required' => false,
                'widget' => 'single_text',
                'label'=>false,
                'attr' => ['class' => 'inptFilter'],
            ])

            ->add('isOrganizer', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label'=>false,
                'attr' => ['class' => 'inptCheckFilter'],
            ])
            ->add('isSubscribe', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label'=>false,
                'attr' => ['class' => 'inptCheckFilter'],
            ])
            ->add('isNotSuscribe', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label'=>false,
                'attr' => ['class' => 'inptCheckFilter'],
            ])
            ->add('isHangoutPassed', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label'=>false,
                'attr' => ['class' => 'inptCheckFilter'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hangout::class,
        ]);
    }
}
