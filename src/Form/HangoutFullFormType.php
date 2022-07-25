<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Hangout;
use App\Entity\Place;
use App\Repository\CityRepository;
use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
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

class HangoutFullFormType extends AbstractType
{

    private $em;
    private $CityRepo;

    public function __construct(EntityManagerInterface $em,PlaceRepository $CityRepo  )
    {
        $this->em = $em;
        $this->CityRepo = $CityRepo;
    }

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
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'name',
                'data' => $options['defaultCampus'],
                'row_attr' => [
                    'class' => 'form-floating mb-1',
                ],
            ]);

         $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
         $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addElements(FormInterface $form, Place $place = null) {
        // 4. Add the province element
        $form->add('city', EntityType::class, array(
            'required' => true,
            'data' => $place,
            'placeholder' => 'Select a City...',
            'class' => 'AppBundle:City'
        ));

        $city = array();
        //Si  une place est enregistrer dans une ville, charger les places
        if ($place) {
            // Fetch Neighborhoods of the City if there's a selected city
            $city =$this->CityRepo->createQueryBuilder("c")
                ->where("c = place ")
                ->setParameter("place", $place->getCity())
                ->getQuery()
                ->getResult();
        }

        // Add the place field with the properly data
        $form->add('place', EntityType::class, array(
            'required' => true,
            'placeholder' => 'Select a place first ...',
            'class' => 'AppBundle:Place',
            'choices' => $city
        ));
    }
    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        // Search for selected City and convert it into an Entity
        $place = $this->em->getRepository('AppBundle:Place')->find($data['place']);
        $this->addElements($form, $place);
    }

    function onPreSetData(FormEvent $event) {
        $place = $event->getData();
        $form = $event->getForm();

        // When you create a new person, the City is always empty
        $place = $place->getCity() ? $place->getCity() : null;

        $this->addElements($form, $place);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_place';
    }

    /*
               ->add('city', EntityType::class,[
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
                    'mapped'=>false
                ])
                ->add('place', EntityType::class,[
                    'label' => 'Lieu',
                    'class' => Place::class,
                    'choice_label' => 'name',
                    'row_attr' => [
                        'class' => 'form-floating mb-1',
                    ],
                ])
                ->add('street', TextType::class,[
                    'label' => 'Ville',
                    'row_attr' => [
                        'class' => 'form-floating mb-1',
                    ],
                    'mapped'=>false
                ])


                ->add('zip', IntegerType::class,[
                    'label' => 'Code postal',
                    'row_attr' => [
                        'class' => 'form-floating mb-1',
                    ],
                    'mapped'=>false
                ])
                ->add('latitude', TextType::class,[
                    'label' => 'Latitude',
                    'row_attr' => [
                        'class' => 'form-floating mb-1',
                    ],
                    'mapped'=>false
                ])
                ->add('longitude', TextType::class,[
                    'label' => 'Longitude',
                    'row_attr' => [
                        'class' => 'form-floating mb-1',
                    ],
                    'mapped'=>false
                ]);*/



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hangout::class,
        ]);
        $resolver->setRequired(['defaultCampus']);
    }
}
