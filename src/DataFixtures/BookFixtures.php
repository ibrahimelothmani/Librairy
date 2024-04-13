<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Book;
use Faker\Factory;

class BookFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 40; $i++) {
            $book = new Book();
            $book->setTitle($faker->sentence(6));
            $book->setAuthor($faker->name);
            $book->setSummary($faker->text);
            $book->setPublication(new \DateTimeImmutable($faker->date('Y-m-d')));
            $book->setAvailability(true);

            // Utilisation de mt_rand pour choisir aléatoirement une catégorie et une librairie
            $category = $this->getReference("category" . mt_rand(0, 9));
            $librairy = $this->getReference("librairy" . mt_rand(0, 3));
            $book->setCategory($category);
            $book->setLibrairy($librairy);

            $manager->persist($book);
        }

        // Enregistrer les livres dans la base de données
        $manager->flush();
    }

    public function getDependencies(): array
    {
        // Retourne les dépendances à d'autres fixtures
        return [
            CategoryFixtures::class,
            LibrairyFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        // Permet de grouper les fixtures, par exemple par environnement
        return ['book'];
    }
}
