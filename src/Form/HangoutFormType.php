<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Hangout;
use App\Entity\Place;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\RangeValidator;

class HangoutFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie',
                'required' => true,
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
            ])
            ->add('startTime', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie',
                'required' => true,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être supérieure ou égale à la date du jour !'
                    ]),
                    new GreaterThan([
                        'propertyPath' => 'parent.all[registerDateLimit].data',
                        'message' => 'La date doit être supérieure à la date limite d\'inscription !'
                    ])
                ],
                'placeholder' => [
                    'day' => 'Jour', 'month' => 'Mois', 'year' => 'Année',
                    'hour' => 'Heure', 'minute' => 'Minute', 'second' => 'Seconde',
                ],
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
            ])
            ->add('registerDateLimit', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure limite d\'inscription',
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être supérieure ou égale à la date du jour !'
                    ])
                ],
                'placeholder' => [
                    'day' => 'Jour', 'month' => 'Mois', 'year' => 'Année'
                ],
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],

            ])
            ->add('MaxOfRegistration', IntegerType::class, [
                'label' => ' Nombre limite de places',
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
                'constraints' => [new Positive(['message' => 'le nombre de participants doit être positif'])]
            ])
            ->add('duration', TimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Durée',
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
                'input' => 'datetime',


            ])
            ->add('hangoutInformations', TextType::class, [
                'label' => 'Descripton et infos',
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],

            ])
            ->add('campusOrganizerSite', EntityType::class, [
                'label' => 'Lieu',
                'class' => Campus::class,
                'choice_label' => 'name',
                'data' => $options['defaultCampus'],
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
            ])
            ->add('city', EntityType::class, [
                'label' => 'Ville',
                'class' => City::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
                'mapped' => false
            ])
            ->add('place', EntityType::class, [
                'label' => 'Lieu',
                'class' => Place::class,
                'choice_label' => 'name',
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
                'required' => true,

            ])
            ->add('street', TextType::class, [
                'label' => 'Ville',
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
                'mapped' => false,
                'disabled' => true,

            ])
            ->add('zip', IntegerType::class, [
                'label' => 'Code postal',
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
                'mapped' => false,
                'disabled' => true,

            ])
            ->add('latitude', TextType::class, [
                'label' => 'Latitude',
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
                'mapped' => false,
                'disabled' => true,

            ])
            ->add('longitude', TextType::class, [
                'label' => 'Longitude',
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
                'mapped' => false,
                'disabled' => true,

            ]);
        $formModifierCity = function (FormInterface $form, City $city = null) {
            $zipCode = null === $city ? "" : $city->getZipCode();
            $places = null === $city ? [] : $city->getPlaces();
            if ($city != null) {
                $form
                    // On remplit le champs code postal en fonction de la ville choisie
                    ->add('zipCode', TextType::class, [
                        'mapped' => false,
                        'disabled' => true,
                        'data' => $zipCode,
                        'attr' => ['value' => $zipCode]
                    ])
                    //On remplit le champs place en fonction de la ville choisie
                    ->add('place', EntityType::class, [
                        'class' => Place::class,
                        'choice_label' => 'name',
                        'placeholder' => 'Choisissez un lieu',
                        'label' => 'Lieu : ',
                        'required' => true,
                        //Trier les places par ordre alphabétique
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('p')
                                ->orderBy('p.name', 'ASC');
                        },
                        'choices' => $places,
                    ]);
            }
        };
        $formModifierPlace = function (FormInterface $form, Place $place = null) {
            $street = null === $place ? "" : $place->getStreet();
            $latitude = null === $place ? "" : $place->getLatitude();
            $longitude = null === $place ? "" : $place->getLongitude();
            if ($place != null) {
                $form
                    // On remplit le champs rue en fonction du lieu choisi
                    ->add('street', TextType::class, [
                        'mapped' => false,
                        'disabled' => true,
                        'data' => $street,
                        'attr' => ['value' => $street]
                    ])
                    // On remplit le champs latitude en fonction du lieu choisi
                    ->add('latitude', TextType::class, [
                        'mapped' => false,
                        'disabled' => true,
                        'data' => $latitude,
                        'attr' => ['value' => $latitude]
                    ])
                    // On remplit le champs longitude en fonction du lieu choisi
                    ->add('longitude', TextType::class, [
                        'mapped' => false,
                        'disabled' => true,
                        'data' => $longitude,
                        'attr' => ['value' => $longitude]
                    ]);
            }
        };

        $builder
            ->get('city')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifierCity) {
                    $city = $event->getForm()->getData();
                    $formModifierCity($event->getForm()->getParent(), $city);
                }
            );
        $builder
            ->get('place')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifierPlace) {
                    $place = $event->getForm()->getData();
                    $formModifierPlace($event->getForm()->getParent(), $place);
                }
            );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hangout::class,
        ]);
        $resolver->setRequired(['defaultCampus']);
    }
}
