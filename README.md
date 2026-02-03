# API Movies – Documentation

À noter, ce projet est aussi liée au frontend Moview. Vous pouvez retrouver le projet : https://github.com/DraayZe/WR505-frontend-moview

## Présentation du projet

API REST et GraphQL pour la gestion d'une base de données de films. Ce projet a été développé avec **Symfony 7.3** et **API Platform 4**.

### Fonctionnalités principales

- **Gestion des films** : CRUD complet avec relations vers acteurs, réalisateurs et catégories
- **Gestion des acteurs** : Informations biographiques, filmographie, photos
- **Gestion des réalisateurs** : Filmographie complète
- **Gestion des catégories** : Classification des films par genre
- **Système de critiques** : Les utilisateurs peuvent noter et commenter les films
- **Authentification JWT** : Sécurisation des endpoints avec JSON Web Tokens
- **Authentification 2FA** : Double authentification optionnelle
- **API Key** : Authentification alternative par clé API
- **Upload de médias** : Gestion des images (photos d'acteurs)

### Technologies utilisées

| Technologie | Version | Description |
|-------------|---------|-------------|
| PHP | 8.3 | Langage backend |
| Symfony | 7.3 | Framework PHP |
| API Platform | 4.x | Framework API REST/GraphQL |
| Doctrine ORM | 3.x | Gestion de la base de données |
| MariaDB | 10.x | Base de données |
| Lexik JWT | 3.x | Authentification JWT |
| VichUploader | 2.x | Upload de fichiers |

---

## Installation

### Prérequis

- Docker et Docker Compose
- Git

### Installation locale avec Docker

```bash
# Cloner le projet
git clone https://github.com/votre-repo/wr506d.git
cd wr506d

# Copier le fichier d'environnement
cp .env .env.local

# Configurer les variables d'environnement dans .env.local
# DATABASE_URL, JWT_PASSPHRASE, etc.

# Lancer les conteneurs Docker
docker compose up -d

# Installer les dépendances
docker compose exec php composer install

# Générer les clés JWT
docker compose exec php php bin/console lexik:jwt:generate-keypair

# Créer la base de données et les tables
docker compose exec php php bin/console doctrine:database:create
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# (Optionnel) Charger les données de test
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

### Variables d'environnement requises

```env
APP_ENV=prod
APP_SECRET=<générer avec: openssl rand -hex 32>
DATABASE_URL=mysql://user:password@host:3306/database?serverVersion=10.11.2-MariaDB&charset=utf8mb4
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=<votre passphrase>
CORS_ALLOW_ORIGIN=^https?://(localhost|votre-domaine\.com)(:[0-9]+)?$
MESSENGER_TRANSPORT_DSN=doctrine://default
```

### Accès à l'application

- **API REST** : `http://localhost:8000/api`
- **Documentation Swagger** : `http://localhost:8000/api/docs`
- **GraphQL Playground** : `http://localhost:8000/api/graphql`

---

## Structure du projet

```
wr506d/
├── config/                     # Configuration Symfony
│   ├── jwt/                    # Clés JWT (private.pem, public.pem)
│   ├── packages/               # Configuration des bundles
│   │   ├── api_platform.yaml   # Configuration API Platform
│   │   ├── doctrine.yaml       # Configuration Doctrine ORM
│   │   ├── lexik_jwt_authentication.yaml
│   │   ├── security.yaml       # Firewall et contrôle d'accès
│   │   └── ...
│   └── routes/                 # Définition des routes
│
├── migrations/                 # Migrations Doctrine (structure BDD)
│
├── public/                     # Point d'entrée web
│   ├── index.php               # Front controller
│   └── media/                  # Fichiers uploadés
│
├── src/                        # Code source de l'application
│   ├── Controller/             # Contrôleurs personnalisés
│   │   ├── MeController.php    # Endpoint /api/me
│   │   └── TwoFactorController.php  # Endpoints 2FA
│   │
│   ├── Entity/                 # Entités Doctrine (modèles)
│   │   ├── Actor.php           # Entité Acteur
│   │   ├── Category.php        # Entité Catégorie
│   │   ├── Director.php        # Entité Réalisateur
│   │   ├── MediaObject.php     # Entité Média (uploads)
│   │   ├── Movie.php           # Entité Film
│   │   ├── Review.php          # Entité Critique
│   │   └── User.php            # Entité Utilisateur
│   │
│   ├── Repository/             # Repositories Doctrine
│   │
│   ├── Security/               # Authentification personnalisée
│   │   └── ApiKeyAuthenticator.php
│   │
│   └── State/                  # State Processors API Platform
│       ├── ReviewProcessor.php
│       └── UserPasswordHasher.php
│
├── var/                        # Cache et logs
│   ├── cache/
│   └── log/
│
├── vendor/                     # Dépendances Composer
│
├── .env                        # Variables d'environnement (défaut)
├── .env.local                  # Variables d'environnement (local)
├── composer.json               # Dépendances PHP
├── Dockerfile                  # Configuration Docker
├── docker-entrypoint.sh        # Script de démarrage Docker
└── symfony.lock
```

### Entités et relations

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Director  │────<│    Movie    │>────│   Category  │
└─────────────┘     └─────────────┘     └─────────────┘
      1                   │   │               N
                          │   │
                          │   │
      N                   │   │               N
┌─────────────┐           │   │         ┌─────────────┐
│    Actor    │>──────────┘   └────────<│   Review    │
└─────────────┘                         └─────────────┘
      │                                       │
      │                                       │
      │ 1                                   N │
┌─────────────┐                         ┌─────────────┐
│ MediaObject │                         │    User     │
└─────────────┘                         └─────────────┘
```

**Relations :**
- Un **Director** peut réaliser plusieurs **Movies** (1-N)
- Un **Movie** peut avoir plusieurs **Actors** (N-N)
- Un **Movie** peut avoir plusieurs **Categories** (N-N)
- Un **Movie** peut avoir plusieurs **Reviews** (1-N)
- Un **User** peut écrire plusieurs **Reviews** (1-N)
- Un **Actor** peut avoir une **MediaObject** (photo) (1-1)

---

## Sécurité et rôles

### Rôles disponibles

| Rôle | Description |
|------|-------------|
| `ROLE_USER` | Utilisateur standard (peut créer des reviews) |
| `ROLE_ADMIN` | Administrateur (accès complet CRUD) |

### Permissions par endpoint

| Endpoint | GET | POST | PATCH | DELETE |
|----------|-----|------|-------|--------|
| /api/movies | Public | ADMIN | ADMIN | ADMIN |
| /api/actors | Public | ADMIN | ADMIN | ADMIN |
| /api/directors | Public | ADMIN | ADMIN | ADMIN |
| /api/categories | Public | ADMIN | ADMIN | ADMIN |
| /api/reviews | Public | USER | USER* | USER* |
| /api/users | - | Public | - | - |
| /api/media_objects | Public | ADMIN | - | - |

*L'utilisateur ne peut modifier/supprimer que ses propres reviews.

Lenny FERNET - S5
