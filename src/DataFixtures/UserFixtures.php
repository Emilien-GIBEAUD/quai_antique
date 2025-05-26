<?php

namespace App\DataFixtures;

use Exception;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }
    /** @throws Exception */
    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i < 10; $i++) {
            $user = (new User())
            ->setUuid(Uuid::v7())
            ->setFirstName("prénom$i")
            ->setLastName("nom$i")
            ->setGuestNumber(random_int(2,5))
            ->setEmail("email$i@mail.fr")
            ->setCreatedAt(new \DateTimeImmutable());

            $user->setPassword($this->passwordHasher->hashPassword($user,"Azerty@$i"));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
