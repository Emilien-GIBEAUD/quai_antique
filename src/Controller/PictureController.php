<?php

namespace App\Controller;

use App\Entity\{User, Picture};
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Vich\UploaderBundle\Naming\SlugNamer;

#[route("api/picture", name: "app_api_picture_")]
final class PictureController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        )
    {
    }

    #[Route(name: 'new', methods: "POST")]
    #[OA\Post(
        path: '/api/picture',
        summary: 'Création d\'une nouvelle image',
        requestBody: new OA\RequestBody(
            description: 'Données de l\'image à créer',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'pictureFile'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Titre de l\'image'),
                        new OA\Property(property: 'pictureFile', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Image créée avec succès',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string'),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Requête invalide'
            )
        ]
    )]
    public function new(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        $picture = new Picture();
        $picture->setTitle($request->request->get("title"));
        $picture->setSlug("à faire");
        $picture->setCreatedAt(new \DateTimeImmutable());
        $picture->setRestaurant($user->getRestaurant());
        $picture->setPicturefile($request->files->get("pictureFile"));

        $this->manager->persist($picture);
        $this->manager->flush();

        return $this->json(['message' => 'Image créée avec succès'], Response::HTTP_CREATED);
    }

    #[Route("/{id}", name: 'showAll', methods: "GET")]
    #[OA\Get(
        path: '/api/picture/{id}',
        summary: 'Afficher toutes les images d\'un restaurant par son id',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'id du restaurant dont les images sont à afficher',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Images trouvées avec succès',
                // content: new OA\JsonContent(
                //     type: 'object',
                //     properties: [
                //     new OA\Property(property: 'id', type: 'integer', example: 1),
                //     new OA\Property(property: 'name', type: 'string', example: 'nom du restaurant'),
                //     new OA\Property(property: 'description', type: 'string', example: 'description du restaurant'),
                //     new OA\Property(property: 'createdAt', type: 'string', format:"date-time"),
                //     new OA\Property(property: 'max_guest', type: 'integer', example: 60)
                //     ]
                // )
            ),
            new OA\Response(
                response: 404,
                description: 'Restaurant et/ou images non trouvé(es)'
            )
        ]
    )]
    public function showAll($id): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_OK);
    }

    #[route("/{id}", name: "edit", methods: "PUT")]
    #[OA\Put(
        path: '/api/picture/{id}',
        summary: 'Modifier une image par son id',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'id de l\'image à modifier',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Données éventuelles de l\'image à modifier (supprimer les lignes inutiles, une "," doit être présente à la fin de chaque ligne sauf la dernière).',
            // content: new OA\JsonContent(
            //     type: 'object',
            //     properties: [
            //         new OA\Property(property: 'name', type: 'string', example: 'nom du restaurant'),
            //         new OA\Property(property: 'description', type: 'string', example: 'description du restaurant'),
            //         new OA\Property(property: 'max_guest', type: 'integer', example: 60)
            //     ]
            // )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'Image modifiée avec succès'
            ),
            new OA\Response(
                response: 404,
                description: 'Image non trouvée'
            )
        ]
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        // TO UPDATE TO UPDATE TO UPDATE TO UPDATE TO UPDATE
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
        path: '/api/picture/{id}',
        summary: 'Supprimer une image par son id',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'id de l\'image à supprimer',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Image supprimée avec succès'
            ),
            new OA\Response(
                response: 404,
                description: 'Image non trouvée'
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        // TO UPDATE TO UPDATE TO UPDATE TO UPDATE TO UPDATE
        $restaurant = $this->repository->findOneBy(["id" => $id]);
        
        if ($restaurant) {
            $this->manager->remove($restaurant);
            $this->manager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

}
