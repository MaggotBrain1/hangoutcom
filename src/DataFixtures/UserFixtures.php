<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use App\Repository\CampusRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\fr_FR\Internet;
use Faker\Provider\fr_FR\Person;
use Faker\Provider\fr_FR\PhoneNumber;

class UserFixtures extends Fixture
{

    private  $faker;
    private  $campusRepository;
    private $tabTest;
    public function __construct( CampusRepository $campusRepository, Generator $factory)
    {
        $this->faker = $factory;
        $this->faker->locale('fr_FR');
        //$this->campusRepository = $campusRepository->findAll();
        $this->tabTest = ['Nantes','Rennes'];
    }

    public function load(ObjectManager $manager): void
    {
        for($i = 0; $i < 30;$i++)
        {
            $user = new User();
            $user->setName($this->faker->name());
            $user->setLastName($this->faker->lastName());
            $phone = $this->faker->phoneNumber();
            $phone = str_replace('+33','0',$phone);
            $phone = str_replace(' ','',$phone);
            $user->setPhone('0688888888');
            file_put_contents('test.txt',strlen($user->getPhone()));
            $user->setRoles(['ROLE_USER']);
            $user->setEmail($this->faker->email());
            $user->setIsActive(1);
            $user->setPassword('aaaaaa');
            $campus = new Campus();
            $campus->setName($this->tabTest[rand(0,count($this->tabTest)-1)]);
            $user->setCampus($campus);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
