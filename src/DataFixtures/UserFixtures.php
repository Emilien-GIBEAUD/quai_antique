<?php

namespace App\DataFixtures;

use App\Entity\Restaurant;
use Exception;
use Faker\Factory;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USER_REFERENCES = "user";
    public const USER_NB_TUPLES = 20;

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i=1; $i <= self::USER_NB_TUPLES; $i++) {
            $user = (new User())
            ->setUuid(Uuid::v7())
            ->setFirstName($faker->firstName())
            ->setLastName($faker->lastName())
            ->setGuestNumber(random_int(2,5))
            ->setEmail("email$i@mail.fr")
            ->setCreatedAt(new \DateTimeImmutable());

            $user->setPassword($this->passwordHasher->hashPassword($user,"Azerty@$i"));

            if ($i <= RestaurantFixtures::RESTAURANT_NB_TUPLES) {
                $user->setRoles(["ROLE_ADMIN"]);
            }

            $manager->persist($user);
            $this->addReference(self::USER_REFERENCES . $i, $user);
        }

        $manager->flush();
    }
}
