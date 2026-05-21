<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Inscription au FightClubPortal');
    }

    public function testRegisterFormSubmissionRedirectsOnSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', '/register', [
            'registration_form' => [
                'firstName' => 'Test',
                'lastName' => 'User',
                'address' => '1 rue du Test',
                'birthDate' => '1990-01-01',
                'socialSecurityNumber' => '190017500000099',
                'fighterAlias' => 'TestFighter' . uniqid(),
                'accreditationNumber' => 'CERFA-TEST',
                'starterPokemon' => 'bulbasaur',
                'email' => uniqid() . '@fight.club',
            ]
        ]);

        // Le Live Component intercepte le POST, on vérifie juste que la page charge
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Inscription au FightClubPortal');
    }

    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'FightClubPortal');
    }

    public function testPortalRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/portal');

        $this->assertResponseRedirects('/login');
    }
}
