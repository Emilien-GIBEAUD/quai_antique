<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/registration',
        summary: 'Inscription d\'un nouvel utilisateur',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données de l\'utilisateur à inscrire',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'prénom'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'nom'),
                    new OA\Property(property: 'email', type: 'string', example: 'adresse@email.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Mdp@13charMIN')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: 'Utilisateur inscrit avec succès',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'user', type: 'string', example: 'adresse@email.com'),
                        new OA\Property(property: 'apiToken', type: 'string', example: '31a023e212f116124a36af14ea0c1c3806eb9378'),
                        new OA\Property(
                            property: 'roles', 
                            type: 'array', 
                            items: new OA\Items(type: 'string', example: 'ROLE_USER')
                        )
                    ]
                )
            )
        ]
    )]
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
    #[OA\Post(
        path: '/api/login',
        summary: 'Connexion d\'un utilisateur',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données de l\'utilisateur à connecter (le compte doit préalablement avoir été créé avec /api/registration)',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'adresse@email.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Mdp@13charMIN')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Utilisateur connecté',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'user', type: 'string', example: 'adresse@email.com'),
                        new OA\Property(property: 'apiToken', type: 'string', example: '31a023e212f116124a36af14ea0c1c3806eb9378'),
                        new OA\Property(
                            property: 'roles', 
                            type: 'array', 
                            items: new OA\Items(type: 'string', example: 'ROLE_USER')
                        )
                    ]
                )
            )
        ]
    )]
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
    #[OA\Get(
        path: '/api/me',
        summary: 'Récupération du profil de l\'utilisateur connecté',
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Profil de l\'utilisateur connecté',
            )
        ]
    )]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if ($user === null) {
            return new JsonResponse(['message' => 'Missing credentials to see profile'], Response::HTTP_UNAUTHORIZED);
        }

        $responseData = $this->serializer->serialize($user, "json", ['groups' => ['user']]);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        
    }

    #[route("/edit", name: "edit", methods: "PUT")]
    #[OA\Put(
        path: '/api/edit',
        summary: 'Modification d\'un profil utilisateur (un ou plusieurs champs)',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Champs éventuels à mettre à jour (supprimer les lignes inutiles, une "," doit être présente à la fin de chaque ligne sauf la dernière). ATTENTION, modification de l\'apiToken si un password est envoyé.',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'prénom'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'nom'),
                    new OA\Property(property: 'email', type: 'string', example: 'adresse@email.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Mdp@13charMIN')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204, 
                description: 'Utilisateur modifié (ATTENTION, apiToken modifié si un password a été envoyé)',
            )
        ]
    )]
    public function edit(#[CurrentUser] ?User $user, Request $request,
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
