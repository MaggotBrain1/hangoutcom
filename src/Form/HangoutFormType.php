<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Hangout;
use App\Entity\Place;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class HangoutFormType extends AbstractType
{
    public function __construct(EntityManagerInterface $em,CityRepository $cityRepository)
    {
        $this->em = $em;
        $this->city = $cityRepository->find(1);

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom de la sortie',
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],
            ])
            ->add('startTime', DateTimeType::class, [
                'required' => true,
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie',
                'constraints' => [
                    new NotBlank(['message' => 'La date doit être supérieure ou égale à la date du jour']),

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
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],

            ])
            ->add('registerDateLimit', DateTimeType::class, [
                'required' => true,
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure limite d\'inscription',
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être supérieure ou égale à la date du jour !'
                    ]),
                    new NotBlank(['message' => 'La date doit être supérieure ou égale à la date du jour'])

                ],
                'placeholder' => [
                    'day' => 'Jour', 'month' => 'Mois', 'year' => 'Année'
                ],
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],

            ])
            ->add('MaxOfRegistration', IntegerType::class, [
                'required' => true,
                'label' => ' Nombre limite de places',
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],
                'constraints' => [
                    new Positive(['message' => 'le nombre de participants doit être positif']),
                    new NotBlank(['message' => 'le nombre de participants doit être positif'])
                ]
            ])
            ->add('duration', TimeType::class, [
                'required' => true,
                'html5' => true,
                'label' => 'Durée',
                'widget' => 'single_text',
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'le nombre de participants doit être positif'])
                ],
                'input' => 'datetime',

            ])
            ->add('hangoutInformations', TextType::class, [
                'required' => true,
                'label' => 'Descripton et infos',
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],

            ])
            ->add('campusOrganizerSite', EntityType::class, [
                'required' => true,
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'name',
                'data' => $options['defaultCampus'],
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],
            ]);


        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addElements(FormInterface $form, City $city = null, $zip ) {
        // 4. Add the province element
        $form->add('city', EntityType::class, array(
            'required' => true,
            'choice_label' => 'name',
            'data' => $city,
            'placeholder' => 'Choisissez une ville',
            'class' => City::class,
            'mapped'=>false,
            'attr' => ['class' => 'intptFormCreateHang'],
            'row_attr' => [
                'class' => 'rowFormCreateHang',
            ],

        ));

        $places = array();
        // If there is a city stored in the City entity, load the Place of it
        if ($city) {
            // Fetch Place of the City if there's a selected city
            $placeRepo = $this->em->getRepository(Place::class);

            $places = $placeRepo->createQueryBuilder("p")
                ->where("p.city = :cityid")
                ->setParameter("cityid", $city->getId())
                ->getQuery()
                ->getResult();
        }

        // Add the Place field with the properly data
        $form->add('place', EntityType::class, array(
            'required' => true,
            'placeholder' => 'choisissez un lieu',
            'class' => Place::class,
            'choice_label' => 'name',
            'choices' => $places,
            'attr' => ['class' => 'intptFormCreateHang'],
            'row_attr' => [
                'class' => 'rowFormCreateHang',
            ],
        ))

            ->add('street', TextType::class, [
                'label' => 'Rue',
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],
                'mapped' => false,
                'disabled' => true,
                //"data"=>$street,


            ])
            ->add('zip', IntegerType::class, [
                'label' => 'Code postal',
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],
                'mapped' => false,
                'disabled' => true,
                'data'=>$zip


            ])
            ->add('latitude', TextType::class, [
                'label' => 'Latitude',
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],
                'mapped' => false,
                'disabled' => true,
                //'data'=>$lat


            ])
            ->add('longitude', TextType::class, [
                'label' => 'Longitude',
                'attr' => ['class' => 'intptFormCreateHang'],
                'row_attr' => [
                    'class' => 'rowFormCreateHang',
                ],
                'mapped' => false,
                'disabled' => true,
                //'data'=>$long

            ]);
    }
    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        // Search for selected City and convert it into an Entity
        $city = $this->em->getRepository(City::class)->find($data['city']);
        $this->addElements($form, $city,$city->getZipCode());
    }

    function onPreSetData(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();
        // When you create a new person, the City is always empty
        $city = $this->city;
        $zip = $city->getZipCode();
        $this->addElements($form, $city,$zip);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hangout::class,
        ]);
        $resolver->setRequired(['defaultCampus']);
    }
}
