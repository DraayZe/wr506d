<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Actor;
use App\Entity\Movie;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();
        $faker->addProvider(new \Xylis\FakerCinema\Provider\Person($faker));

        $actors = $faker->actors($gender = null, $count = 190, $duplicates = false);
        foreach ($actors as $item) {
            $actor = new Actor();
            $fullname = $item;
            $names = explode(" ", $fullname);

            $actor->setFirstName($names[0] ?? '');
            $actor->setLastName($names[1] ?? '');

            $actor->setBio($faker->paragraph(6, true));
            $dob = $faker->dateTimeThisCentury();
            $actor->setDob($dob);

            if ($faker->boolean(90)) {
                $actor->setDod(
                    $faker->dateTimeBetween($dob, 'now')
                );
            }
            $actorsArray[] = $actor;
            $manager->persist($actor);
        }


        $fakerMovie = \Faker\Factory::create();
        $fakerMovie->addProvider(new \Xylis\FakerCinema\Provider\Movie($fakerMovie));

        $categoriesArray = [];
        $movies = $fakerMovie->movies(199);

        foreach ($movies as $item) {
            $movie = new Movie();

            $movie->setName($item);
            $movie->setDescription($fakerMovie->overview);


            $durationMin = 60 * 60;   // 1h
            $durationMax = 270 * 60;  // 4h30
            $movie->setDuration($fakerMovie->numberBetween($durationMin, $durationMax));

            $categoryName = $fakerMovie->movieGenre;
            if (!array_key_exists($categoryName, $categoriesArray)) {
                $category = new Category();
                $category->setName($categoryName);
                $manager->persist($category);
                $categoriesArray[$categoryName] = $category;
            } else {
                $category = $categoriesArray[$categoryName];
            }

            shuffle($actorsArray);
            foreach (array_slice($actorsArray, 0, rand(2, 6)) as $ActorObject) {
                $movie->addActor($ActorObject);
            }
            $movie->addCategory($category);
            $manager->persist($movie);
        }

        $manager->flush();
    }
}
