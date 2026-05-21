Feature: Inscription au FightClubPortal
    En tant que visiteur
    Je veux pouvoir m'inscrire sur le portail
    Afin d'attendre la validation de mon compte

    Scenario: Un visiteur accède à la page d'inscription
        Given je suis sur la page "/register"
        Then je vois "Inscription au FightClubPortal"

    Scenario: Un visiteur soumet le formulaire avec des données valides
        Given je suis sur la page "/register"
        When je remplis "Prénom" avec "John"
        And je remplis "Nom" avec "Doe"
        And je remplis "Adresse" avec "1 rue du Fight Club"
        And je remplis "Date de naissance" avec "1990-01-01"
        And je remplis "Numéro de sécurité sociale" avec "190017500000018"
        And je remplis "Pseudo de combattant" avec "JohnFighter"
        And je remplis "Numéro d'accréditation CERFA 666" avec "CERFA-001"
        And je remplis "Email" avec "john@fight.club"
        And je clique sur "Rejoindre le Fight Club"
        Then je vois "Votre demande est en attente de validation"
#        And je clique sur "Rejoindre le Fight Club"
#        Then j'affiche la page
