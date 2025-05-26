<?php

namespace App\DataFixtures;

use Exception;
use Symfony\Component\Uid\Uuid;
use App\Entity\{Restaurant, User};
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class RestaurantFixtures extends Fixture implements DependentFixtureInterface
{
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i <= 3; $i++) {
            $restaurant = (new Restaurant())
            ->setUuid(Uuid::v7())
            ->setName("Restaurant n°$i")
            ->setDescription("Description restaurant n°$i")
            ->setMaxGuest(random_int(50,100))
            ->setCreatedAt(new \DateTimeImmutable())
            ->setAmOpeningTime([])
            ->setPmOpeningTime([])
            ->setUser($this->getReference("user$i",User::class));

            $manager->persist($restaurant);
            $this->addReference("restaurant$i", $restaurant);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
