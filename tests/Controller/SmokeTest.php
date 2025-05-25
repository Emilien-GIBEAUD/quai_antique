<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testApiDocUrlIsSuccessfull(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request("GET", "api/doc");
        self::assertResponseIsSuccessful();
    }

    public function testLoginCanConnectValidUser(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);

        // // Création user, à ne pas faire en test normalement (voir suite du cours)
        // $client->request('POST', '/api/registration', [], [], ["CONTENT_TYPE" => "application/json"], json_encode([
        //     "firstName" => "prénom",
        //     "lastName" => "nom",
        //     "email" => "test@gmail.com",
        //     "password" => "Azerty@11",
        // ]));
        // $statusCode = $client->getResponse()->getStatusCode();
        // dd($statusCode);

        $client->request('POST', '/api/login', [], [], ["CONTENT_TYPE" => "application/json"], json_encode([
            "username" => "test@gmail.com",
            "password" => "Azerty@11",
        ]));
        // $statusCode = $client->getResponse()->getStatusCode();
        // dd($statusCode);
        self::assertResponseStatusCodeSame(200);

        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString("apiToken",$content);
        // dd($content);

    }

    public function testApiAccountUrlIsSecure(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);
        $client->request('GET', '/api/me');
        self::assertResponseStatusCodeSame(401);

        $client->request('PUT', '/api/edit');
        self::assertResponseStatusCodeSame(401);
    }
}
