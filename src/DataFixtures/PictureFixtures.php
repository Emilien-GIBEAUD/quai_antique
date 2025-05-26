<?php

namespace App\DataFixtures;

use Exception;
use App\Entity\{Picture, Restaurant};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i <= 12; $i++) {
            $picture = (new Picture())
            ->setTitle("Image n°$i")
            ->setSlug("slug-title")
            ->setCreatedAt(new \DateTimeImmutable());
            if ($i <= 3) {
                $picture->setRestaurant($this->getReference(
                    "restaurant$i",Restaurant::class
                ));
            } else {
                $picture->setRestaurant($this->getReference(
                    "restaurant".random_int(1,3),Restaurant::class
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
