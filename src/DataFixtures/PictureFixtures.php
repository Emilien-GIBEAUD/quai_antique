<?php

namespace App\DataFixtures;

use Exception;
use App\Entity\{Picture, Restaurant};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    public const PICTURE_REFERENCES = "picture";
    public const PICTURE_NB_TUPLES = 5 * RestaurantFixtures::RESTAURANT_NB_TUPLES;

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i <= self::PICTURE_NB_TUPLES; $i++) {
            $picture = (new Picture())
            ->setTitle("Image n°$i")
            ->setSlug("slug-title")
            ->setCreatedAt(new \DateTimeImmutable());
            if ($i <= RestaurantFixtures::RESTAURANT_NB_TUPLES) {
                $picture->setRestaurant($this->getReference(
                    RestaurantFixtures::RESTAURANT_REFERENCES . $i,Restaurant::class
                ));
            } else {
                $picture->setRestaurant($this->getReference(
                    RestaurantFixtures::RESTAURANT_REFERENCES . random_int(1,RestaurantFixtures::RESTAURANT_NB_TUPLES),Restaurant::class
                ));
            }

            $manager->persist($picture);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [RestaurantFixtures::class];
    }
}
