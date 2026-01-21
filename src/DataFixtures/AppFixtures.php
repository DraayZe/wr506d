<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Actor;
use App\Entity\Movie;
use Faker\Factory;
use Faker\Generator;
use Xylis\FakerCinema\Provider\Person;
use Xylis\FakerCinema\Provider\Movie as MovieProvider;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private Generator $fakerMovie;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct()
    {
        $this->faker = Factory::create();
        $this->faker->addProvider(new Person($this->faker));

        $this->fakerMovie = Factory::create();
        $this->fakerMovie->addProvider(new MovieProvider($this->fakerMovie));
    }

    public function load(ObjectManager $manager): void
    {
        $actorsArray = [];
        $actors = $this->faker->actors($gender = null, $count = 190, $duplicates = false);
        foreach ($actors as $item) {
            $actor = new Actor();
            $fullname = $item;
            $names = explode(" ", $fullname);

            $actor->setFirstName($names[0] ?? '');
            $actor->setLastName($names[1] ?? '');

            $actor->setBio($this->faker->paragraph(6, true));
            $dob = $this->faker->dateTimeThisCentury();
            $actor->setDob($dob);

            if ($this->faker->boolean(90)) {
                $actor->setDod(
                    $this->faker->dateTimeBetween($dob, 'now')
                );
            }
            $actorsArray[] = $actor;
            $manager->persist($actor);
        }

        $categoriesArray = [];
        $movies = $this->fakerMovie->movies(199);

        foreach ($movies as $item) {
            $movie = new Movie();

            $movie->setName($item);
            $movie->setDescription($this->fakerMovie->overview);

            $durationMin = 60 * 60;
            $durationMax = 270 * 60;
            $movie->setDuration($this->fakerMovie->numberBetween($durationMin, $durationMax));

            $categoryName = $this->fakerMovie->movieGenre;
            if (!array_key_exists($categoryName, $categoriesArray)) {
                $category = new Category();
                $category->setName($categoryName);
                $manager->persist($category);
                $categoriesArray[$categoryName] = $category;
            }

            $category = $categoriesArray[$categoryName];

            shuffle($actorsArray);
            foreach (array_slice($actorsArray, 0, rand(2, 6)) as $actorObject) {
                $movie->addActor($actorObject);
            }
            $movie->addCategory($category);
            $manager->persist($movie);
        }

        $manager->flush();
    }
}
