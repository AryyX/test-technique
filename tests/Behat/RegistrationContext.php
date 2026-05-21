<?php

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class RegistrationContext extends RawMinkContext implements Context
{
    public function __construct(
        private KernelInterface $kernel,
        private EntityManagerInterface $em,
    ) {}

    #[\Behat\Behat\Hook\Attribute\BeforeScenario]
    public function cleanDatabase(): void
    {
        try {
            $this->em->getConnection()->executeStatement('DELETE FROM `user` WHERE fighter_alias = \'JohnFighter\'');
        } catch (\Exception $e) {
            // Ignore si la table n'existe pas encore
        }
    }
    #[Given('je suis sur la page :path')]
    public function jeSuisSurLaPage(string $path): void
    {
        $this->visitPath($path);
    }

    #[Then('je vois :text')]
    public function jeVois(string $text): void
    {
        $this->assertSession()->pageTextContains($text);
    }

    #[When('je remplis :field avec :value')]
    public function jeRemplisAvec(string $field, string $value): void
    {
        $this->getSession()->getPage()->fillField($field, $value);
    }

    #[When('je clique sur :button')]
    public function jeCliqueSur(string $button): void
    {
        $page = $this->getSession()->getPage();

        $data = [
            'registration_form' => [
                'firstName' => $page->findField('registration_form[firstName]')?->getValue() ?? '',
                'lastName' => $page->findField('registration_form[lastName]')?->getValue() ?? '',
                'address' => $page->findField('registration_form[address]')?->getValue() ?? '',
                'birthDate' => $page->findField('registration_form[birthDate]')?->getValue() ?? '',
                'socialSecurityNumber' => $page->findField('registration_form[socialSecurityNumber]')?->getValue() ?? '',
                'fighterAlias' => $page->findField('registration_form[fighterAlias]')?->getValue() ?? '',
                'accreditationNumber' => $page->findField('registration_form[accreditationNumber]')?->getValue() ?? '',
                'starterPokemon' => $page->findField('registration_form[starterPokemon]')?->getValue() ?? 'bulbasaur',
                'email' => $page->findField('registration_form[email]')?->getValue() ?? '',
            ]
        ];

        // Utilise le kernel directement
        $request = Request::create(
            '/register',
            'POST',
            $data
        );

        $response = $this->kernel->handle($request);

        if ($response->isRedirect()) {
            $redirectRequest = Request::create(
                $response->headers->get('Location'),
                'GET'
            );
            $response = $this->kernel->handle($redirectRequest);
        }

        // Met à jour la session Mink avec la nouvelle réponse
        $this->getSession()->getDriver()->getClient()->request(
            'GET',
            $response->headers->get('Location') ?? '/register/success'
        );
    }

    #[Then("j'affiche la page")]
    public function jAfficheLaPage(): void
    {
        echo $this->getSession()->getPage()->getContent();
    }
}
