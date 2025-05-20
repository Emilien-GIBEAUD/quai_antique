<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[route("api/restaurant", name: "app_api_restaurant_")]
final class RestaurantController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private RestaurantRepository $repository)
    {
        
    }

    #[route(name: "new", methods: "POST")]
    public function new(): Response
    {
        $restaurant = new Restaurant();
        $restaurant->setName("envoi statique_Quai Antique");
        $restaurant->setDescription("envoi statique_Bienvenue sur le site de notre restaurant gastronomique situé dans la magnifique ville de Chambéry. Le chef Arnaud Michant vous invite pour un voyage culinaire mémorable.");
        $restaurant->setMaxGuest(80);
        $restaurant->setCreatedAt(new \DateTimeImmutable());
        $restaurant->setAmOpeningTime(["11h30","13h30"]);
        $restaurant->setPmOpeningTime(["19h30","22h00"]);


        // Tell Doctrine you want to (eventually) save the restaurant (no queries yet)
        $this->manager->persist($restaurant);
        // Actually executes the queries (i.e. the INSERT query)
        $this->manager->flush();

        return $this->json(
            ["message"=>"Restaurant ressource created with id = {$restaurant->getId()}"], 
            Response::HTTP_CREATED,
        );
    }
    
    #[route("/{id}", name: "show", methods: "GET")]
    public function show(int $id): Response
    {
        $restaurant = $this->repository->findOneBy(["id" => $id]);

        if (!$restaurant) {
            throw $this->createNotFoundException("No restaurant found with id = {$id}");
        }

        return $this->json(
            ["message" => "A restaurant was found : {$restaurant->getName()} for id = {$restaurant->getId()}"]
        );
    }
    
    #[route("/{id}", name: "edit", methods: "PUT")]
    public function edit(int $id): Response
    {
        $restaurant = $this->repository->findOneBy(["id" => $id]);
        
        if (!$restaurant) {
            throw $this->createNotFoundException("No restaurant found with id = {$id}");
        }

        $restaurant->setName("Update of restaurant id = {$id}");
        $this->manager->flush();

        return $this->redirectToRoute("app_api_restaurant_show", ["id" => $restaurant->getId()]);
    }
    
    #[route("/{id}", name: "delete", methods: "DELETE")]
    public function delete(int $id): Response
    {
        $restaurant = $this->repository->findOneBy(["id" => $id]);
        
        if (!$restaurant) {
            throw $this->createNotFoundException("No restaurant found with id = {$id}");
        }

        $this->manager->remove($restaurant);
        $this->manager->flush();

        return $this->json(
            ["message" => "Deleting from the DB the restaurant with id = {$id}"], 
            Response::HTTP_NO_CONTENT,
        );

    }
    
}
