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
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Campus : ',
                'attr' => ['class' => ''],
            ])
            ->add('name', SearchType::class, [
                'required' => false,
                'label' => 'Le nom de la sortie contient : ',
                'attr' => ['class' => ''],
            ])
            ->add('startDate', DateType::class, [
                'mapped' => false,
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Entre ',
                'attr' => ['class' => ''],
            ])
            ->add('endDate', DateType::class, [
                'mapped' => false,
                'required' => false,
                'widget' => 'single_text',
                'label' => 'et  ',
                'attr' => ['class' => ''],
            ])

            ->add('isOrganizer', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => ' Sorties dont je suis l\'organisateur/trice',
                'attr' => ['class' => ''],
            ])
            ->add('isSubscribe', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => ' Sorties auxquelles je suis inscrit/e',
                'attr' => ['class' => ''],
            ])
            ->add('isNotSuscribe', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => ' Sorties auxquelles je ne suis pas inscrit/e',
                'attr' => ['class' => ''],
            ])
            ->add('isTripsPassed', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => ' Sorties passées',
                'attr' => ['class' => ''],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hangout::class,
        ]);
    }
}
