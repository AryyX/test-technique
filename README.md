# FightClubPortal

Application web secrète permettant aux membres de s'échanger des messages en toute discrétion.

## Prérequis

- Docker Desktop
- Git

## Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd TestTech
```

### 2. Lancer l'environnement Docker

```bash
docker-compose up -d --build
```

### 3. Installer les dépendances PHP

```bash
docker-compose exec php composer install
```

### 4. Configurer l'environnement

Copier le fichier `.env` et adapter si nécessaire :

```bash
cp .env .env.local
```

Les variables importantes :
- `DATABASE_URL` — connexion MySQL (déjà configurée pour Docker)
- `MAILER_DSN` — connexion MailHog (déjà configurée pour Docker)

### 5. Créer la base de données et exécuter les migrations

```bash
docker-compose exec php php bin/console doctrine:migrations:migrate
```

### 6. Compiler les assets

```bash
docker-compose exec php php bin/console asset-map:compile
```

## Accès

| Service | URL |
|---|---|
| Application | http://localhost:8080 |
| MailHog (emails) | http://localhost:8025 |

## Utilisation

### Flux d'inscription

1. Se rendre sur http://localhost:8080/register
2. Remplir le formulaire d'inscription
3. Un administrateur valide l'inscription via la commande CLI
4. L'utilisateur reçoit un email avec un lien de validation
5. L'utilisateur crée son mot de passe
6. L'utilisateur accède au portail

### Commande de validation admin

```bash
docker-compose exec php php bin/console app:validate-user
```

## Tests

### PHPUnit (tests unitaires et fonctionnels)

```bash
docker-compose exec php php bin/phpunit
```

### Behat (tests comportementaux)

```bash
docker-compose exec php vendor/bin/behat
```

## Architecture

Voir `docs/architecture.md`

## Stack technique

- **PHP 8.2** avec Symfony 7.4
- **MySQL 8.0**
- **Nginx**
- **MailHog** (emails en développement)
- **Symfony UX** (Live Components, Twig Components)
- **Bootstrap 5**
- **PHPUnit** + **Behat**
