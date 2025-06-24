<?php

namespace App\Tests\Controller;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MessageControllerTest extends WebTestCase
{
    public function testIndexRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/messages/');
        $this->assertResponseRedirects('/login');
    }

    public function testConversationRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/messages/conversation/1');
        $this->assertResponseRedirects('/login');
    }

    // Pour tester l'affichage réel, il faudrait un utilisateur connecté et des fixtures
    // Ici on vérifie juste que la route existe et nécessite une authentification
} 