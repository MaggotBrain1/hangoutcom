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
    private CampusRepository $campusRepository;
    private $tabTest;
    public function __construct( Generator $factory)
    {
        $this->faker = $factory;
        $this->faker->locale('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        for($i = 0; $i < 25;$i++)
        {
            $user = new User();
            $user->setName($this->faker->firstName());
            $user->setLastName($this->faker->lastName());
            $phone = $this->faker->phoneNumber();
            $phone = str_replace('+33','0',$phone);
            $phone = str_replace(' ','',$phone);
            $user->setPhone('0606060606');
            file_put_contents('test.txt',$phone);
            $user->setRoles(['ROLE_USER']);
            $user->setEmail($this->faker->email());
            $user->setIsActive(1);
            $user->setPassword('aaaaaa');
            $user->setCampus($this->getCampus());
            $manager->persist($user);
        }
        $manager->flush();
    }
    public function getCampus(CampusRepository $campusRepository) : Campus
{
    $campus = $campusRepository->find(rand(0,1));
    echo ($campus);
    //$campus = $this->campusRepository[rand(0,count($this->campusRepository)-1)];
    return $campus;
}
}
