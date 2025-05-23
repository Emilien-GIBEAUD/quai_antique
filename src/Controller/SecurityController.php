<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[route("api", name: "app_api_")]
final class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer,
        )
    {
    }

    #[route("/registration", name: "registration", methods: "POST")]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, "json");
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUuid(Uuid::v7());

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            ["user" => $user->getUserIdentifier(),
                "apiToken" => $user->getApiToken(),
                "roles" => $user->getRoles(),
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            return new JsonResponse(['message' => 'Missing credentials to login'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }
    
    #[route("/me", name: "me", methods: "GET")]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            return new JsonResponse(['message' => 'Missing credentials to see profile'], Response::HTTP_UNAUTHORIZED);
        }

        $responseData = $this->serializer->serialize($user, "json", ['groups' => ['user']]);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        
    }

    #[route("/edit", name: "edit", methods: "PUT")]
    public function edit(
                        #[CurrentUser] ?User $user,
                        Request $request,
                        UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        if ($user) {
            $user = $this->serializer->deserialize(
                $request->getContent(),
                User::class,
                "json",
                [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
            );
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
            $user->setUpdatedAt(new \DateTimeImmutable());
            $this->manager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

}
