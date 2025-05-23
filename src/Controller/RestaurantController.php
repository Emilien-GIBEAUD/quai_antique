<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Restaurant;
use Symfony\Component\Uid\Uuid;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


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
