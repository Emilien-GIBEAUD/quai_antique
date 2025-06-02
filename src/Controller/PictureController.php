<?php

namespace App\Controller;

use App\Entity\{User, Picture};
use App\Repository\PictureRepository;
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
        private PictureRepository $repository,
        private SerializerInterface $serializer,
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
        // TO DO SECURISER LE FICHIER TO DO SECURISER LE FICHIER TO DO SECURISER LE FICHIER
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
            ),
            new OA\Response(
                response: 404,
                description: 'Restaurant et/ou images non trouvé(es)'
            )
        ]
    )]
    public function showAll($id): JsonResponse
    {
        $pictures = $this->repository->findBy(["Restaurant" => $id]);
        $responseData = $this->serializer->serialize($pictures,"json", ['groups' => ['picture']]);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[route("/edit/{pictureName}", name: "edit", methods: "POST")]
    #[OA\Post(
        path: '/api/picture/edit/{pictureName}',
        summary: 'Modifier une image par son nom de fichier',
        parameters: [
            new OA\Parameter(
                name: 'pictureName',
                in: 'path',
                required: true,
                description: 'nom de fichier de l\'image à modifier',
                schema: new OA\Schema(type: 'string', example: "fichier_16546846616561.png")
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Données éventuelles de l\'image à modifier (un champ au minimum doit être saisi).',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Titre de l\'image'),
                        new OA\Property(property: 'pictureFile', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'Image modifiée avec succès'
            ),
            new OA\Response(
                response: 400,
                description: 'Aucun champ saisi'
            ),
            new OA\Response(
                response: 404,
                description: 'Image non trouvée'
            )
        ]
    )]
    public function edit(string $pictureName, Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        $picture = $this->repository->findOneBy(["pictureName" => $pictureName]);

        if ($picture) {
            $title = $request->request->get("title");
            // TO DO SECURISER LE FICHIER TO DO SECURISER LE FICHIER TO DO SECURISER LE FICHIER
            // TO DO RELIER A USER RELIER A USER RELIER A USER RELIER A USER RELIER A USER
            $pictureFile = $request->files->get("pictureFile");
            if ($title !== "" || $pictureFile !== null) {
                if ($title !== "") {
                    $picture->setTitle($title);
                }
                if ($pictureFile !== null) {
                    $picture->setPictureFile($pictureFile);
                }
                $picture->setUpdatedAt(new \DateTimeImmutable());
                $this->manager->flush();
                return $this->json(['message' => 'Modification réalisée avec succès'], Response::HTTP_OK);
            }
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    
    #[route("/{pictureName}", name: "delete", methods: "DELETE")]
    #[OA\Delete(
        path: '/api/picture/{pictureName}',
        summary: 'Supprimer une image par son nom de fichier',
        parameters: [
            new OA\Parameter(
                name: 'pictureName',
                in: 'path',
                required: true,
                description: 'nom de fichier de l\'image à supprimer',
                schema: new OA\Schema(type: 'string', example: "fichier_16546846616561.png")
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
    public function delete(string $pictureName): JsonResponse
    {
        $picture = $this->repository->findOneBy(["pictureName" => $pictureName]);
        
        if ($picture) {
            $this->manager->remove($picture);
            $this->manager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

}
