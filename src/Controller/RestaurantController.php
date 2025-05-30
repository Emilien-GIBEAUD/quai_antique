<?php

namespace App\Controller;

use App\Entity\{User, Restaurant};
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;


#[route("api/restaurant", name: "app_api_restaurant_")]
final class RestaurantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
    }

    #[route(name: "new", methods: "POST")]
    #[OA\Post(
        path: '/api/restaurant',
        summary: 'Création d\'un nouveau restaurant',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données du restaurant à créer',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'nom du restaurant'),
                    new OA\Property(property: 'description', type: 'string', example: 'description du restaurant'),
                    new OA\Property(property: 'max_guest', type: 'integer', example: 60)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Restaurant créé avec succès',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'nom du restaurant'),
                    new OA\Property(property: 'description', type: 'string', example: 'description du restaurant'),
                    new OA\Property(property: 'createdAt', type: 'string', format:"date-time"),
                    new OA\Property(property: 'max_guest', type: 'integer', example: 60)
                    ]
                )
            )
        ]
    )]
    public function new(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        $restaurant = $this->serializer->deserialize($request->getContent(), Restaurant::class, "json");
        $restaurant->setCreatedAt(new \DateTimeImmutable());
        $restaurant->setAmOpeningTime(["11h30","13h30"]);   // TO DO TO DO TO DO TO DO TO DO TO DO TO DO
        $restaurant->setPmOpeningTime(["19h30","22h00"]);   // TO DO TO DO TO DO TO DO TO DO TO DO TO DO
        $restaurant->setUuid(Uuid::v7());
        $restaurant->setUser($user);

        // Tell Doctrine you want to (eventually) save the restaurant (no queries yet)
        $this->manager->persist($restaurant);
        // Actually executes the queries (i.e. the INSERT query)
        $this->manager->flush();

        // Send on the created page
        $responseData = $this->serializer->serialize($restaurant, "json", ['groups' => ['restaurant']]);
        $location = $this->urlGenerator->generate(
            "app_api_restaurant_show",
            ["id" => $restaurant->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["location" => $location], true);
    }
    
    #[route("/{id}", name: "show", methods: "GET")]
    #[OA\Get(
        path: '/api/restaurant/{id}',
        summary: 'Afficher un restaurant par son id',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'id du restaurant à afficher',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Restaurant trouvé avec succès',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'nom du restaurant'),
                    new OA\Property(property: 'description', type: 'string', example: 'description du restaurant'),
                    new OA\Property(property: 'createdAt', type: 'string', format:"date-time"),
                    new OA\Property(property: 'max_guest', type: 'integer', example: 60)
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Restaurant non trouvé'
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(["id" => $id]);
        if ($restaurant) {
            $responseData = $this->serializer->serialize($restaurant, "json", ['groups' => ['restaurant']]);
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    
    #[route("/{id}", name: "edit", methods: "PUT")]
    #[OA\Put(
        path: '/api/restaurant/{id}',
        summary: 'Modifier un restaurant par son id',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'id du restaurant à modifier',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données éventuelles du restaurant à modifier (supprimer les lignes inutiles, une "," doit être présente à la fin de chaque ligne sauf la dernière).',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'nom du restaurant'),
                    new OA\Property(property: 'description', type: 'string', example: 'description du restaurant'),
                    new OA\Property(property: 'max_guest', type: 'integer', example: 60)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'Restaurant modifié avec succès'
            ),
            new OA\Response(
                response: 404,
                description: 'Restaurant non trouvé'
            )
        ]
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(["id" => $id]);
        
        if ($restaurant) {
            $restaurant = $this->serializer->deserialize(
                $request->getContent(),
                Restaurant::class,
                "json",
                [AbstractNormalizer::OBJECT_TO_POPULATE => $restaurant]
            );
            $restaurant->setUpdatedAt(new \DateTimeImmutable());
            $this->manager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    
    #[route("/{id}", name: "delete", methods: "DELETE")]
    #[OA\Delete(
        path: '/api/restaurant/{id}',
        summary: 'Supprimer un restaurant par son id',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'id du restaurant à supprimer',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Restaurant supprimé avec succès'
            ),
            new OA\Response(
                response: 404,
                description: 'Restaurant non trouvé'
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $restaurant = $this->repository->findOneBy(["id" => $id]);
        
        if ($restaurant) {
            $this->manager->remove($restaurant);
            $this->manager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    
}
