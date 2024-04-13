<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\DataFixtures\LibrairyFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\User;
use App\Entity\Librairy;
use Faker\Factory;


class UserFixtures extends Fixture implements DependentFixtureInterface
{
  private $passwordHasher;

  public function __construct(UserPasswordHasherInterface $passwordHasher)
  {
      $this->passwordHasher = $passwordHasher;
  }
  public function load(ObjectManager $manager)
  {
      $faker = Factory::create('fr_FR');
      for ($i = 0; $i < 10; $i++) {
          $user = new User();
          $user->setEmail($faker->email);
          $user->setPassword($this->passwordHasher->hashPassword($user, "mypassword$i"));
          $user->setFirstName($faker->firstName);
          $user->setLastName($faker->lastName);
          $user->setCity($faker->city);
          $user->setCode("code$i");
          $random = mt_rand(0, 3);
          $user->setLibrairy($this->getReference("librairy$random"));
          $manager->persist($user);
      }

      $manager->flush();
  }

    public function getDependencies()
    {
        return array(
            LibrairyFixtures::class,
        );
    }
}
