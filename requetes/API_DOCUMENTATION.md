# API Movies – Documentation des requêtes

API REST et GraphQL pour la gestion des **films**, **acteurs**, **réalisateurs**, **catégories** et **critiques**.

---

## Base URL

```text
{{URLPROD}} = https://wr506d.lennyfernet.fr/
```

Toutes les routes ci-dessous sont préfixées par cette URL.

---

## Authentification

L’API utilise **JWT (JSON Web Token)**.

* **Les requêtes GET sont publiques** (pas besoin de token)
* **Les requêtes POST / PUT / PATCH / DELETE nécessitent** :

    * un token JWT valide
    * le **rôle `ROLE_ADMIN`**

### Obtenir un token

```http
POST /auth
Content-Type: application/json
```

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Réponse :**

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### Utiliser le token

Ajouter le header suivant à chaque requête protégée :

```http
Authorization: Bearer <token>
```

---

## REST API

### Movies

#### Liste des films

```http
GET /api/movies
```

**Filtres disponibles :**

```http
GET /api/movies?name=Avatar
GET /api/movies?duration[gte]=100&duration[lte]=200
```

#### Détail d’un film

```http
GET /api/movies/{id}
```

#### Créer un film *(ROLE_ADMIN requis)*

```http
POST /api/movies
Content-Type: application/ld+json
```

```json
{
  "name": "Inception",
  "description": "Un film de Christopher Nolan",
  "duration": 148,
  "releaseData": "2010-07-16",
  "budget": 160000000,
  "director": "/api/directors/1",
  "categories": ["/api/categories/1", "/api/categories/2"],
  "actors": ["/api/actors/1", "/api/actors/2"]
}
```

#### Modifier un film *(ROLE_ADMIN requis)*

```http
PUT /api/movies/{id}
Content-Type: application/ld+json
```

```json
{
  "name": "Inception (Updated)",
  "description": "Description mise à jour",
  "duration": 150
}
```

#### Supprimer un film *(ROLE_ADMIN requis)*

```http
DELETE /api/movies/{id}
```

---

### Actors

#### Liste des acteurs

```http
GET /api/actors
```

**Filtres disponibles :**

```http
GET /api/actors?lastname=DiCaprio
GET /api/actors?firstname=Leonardo
GET /api/actors?dob[after]=1970-01-01
```

#### Détail d’un acteur

```http
GET /api/actors/{id}
```

#### Créer un acteur *(ROLE_ADMIN requis)*

```http
POST /api/actors
Content-Type: application/ld+json
```

```json
{
  "lastname": "DiCaprio",
  "firstname": "Leonardo",
  "bio": "Acteur américain né en 1974",
  "dob": "1974-11-11"
}
```

#### Modifier un acteur *(ROLE_ADMIN requis)*

```http
PUT /api/actors/{id}
Content-Type: application/ld+json
```

```json
{
  "lastname": "DiCaprio",
  "firstname": "Leonardo",
  "bio": "Bio mise à jour"
}
```

#### Supprimer un acteur *(ROLE_ADMIN requis)*

```http
DELETE /api/actors/{id}
```

---

### Directors

#### Liste des réalisateurs

```http
GET /api/directors
```

#### Détail d’un réalisateur

```http
GET /api/directors/{id}
```

#### Créer un réalisateur *(ROLE_ADMIN requis)*

```http
POST /api/directors
Content-Type: application/ld+json
```

```json
{
  "lastname": "Nolan",
  "firstname": "Christopher",
  "dob": "1970-07-30"
}
```

#### Modifier un réalisateur *(ROLE_ADMIN requis)*

```http
PUT /api/directors/{id}
Content-Type: application/ld+json
```

```json
{
  "lastname": "Nolan",
  "firstname": "Christopher"
}
```

#### Supprimer un réalisateur *(ROLE_ADMIN requis)*

```http
DELETE /api/directors/{id}
```

---

### Categories

#### Liste des catégories

```http
GET /api/categories
```

#### Détail d’une catégorie

```http
GET /api/categories/{id}
```

#### Créer une catégorie *(ROLE_ADMIN requis)*

```http
POST /api/categories
Content-Type: application/ld+json
```

```json
{
  "name": "Science-Fiction"
}
```

#### Modifier une catégorie *(ROLE_ADMIN requis)*

```http
PUT /api/categories/{id}
Content-Type: application/ld+json
```

```json
{
  "name": "Sci-Fi"
}
```

#### Supprimer une catégorie *(ROLE_ADMIN requis)*

```http
DELETE /api/categories/{id}
```

---

### Reviews

#### Liste des critiques

```http
GET /api/reviews
```

**Filtres disponibles :**

```http
GET /api/reviews?movie=1
```

#### Détail d’une critique

```http
GET /api/reviews/{id}
```

#### Créer une critique *(ROLE_ADMIN requis)*

```http
POST /api/reviews
Content-Type: application/ld+json
Authorization: Bearer <token>
```

```json
{
  "comment": "Excellent film !",
  "rating": 5,
  "movie": "/api/movies/1"
}
```

#### Modifier une critique *(ROLE_ADMIN requis)*

```http
PATCH /api/reviews/{id}
Content-Type: application/merge-patch+json
Authorization: Bearer <token>
```

```json
{
  "comment": "Commentaire modifié",
  "rating": 4
}
```

#### Supprimer une critique *(ROLE_ADMIN requis)*

```http
DELETE /api/reviews/{id}
Authorization: Bearer <token>
```

---

### Users

#### Inscription

```http
POST /api/users
Content-Type: application/ld+json
```

```json
{
  "email": "newuser@example.com",
  "plainPassword": "SecurePassword123"
}
```

#### Informations de l’utilisateur courant

```http
GET /api/me
Authorization: Bearer <token>
```

---

### Media Objects

#### Liste des médias

```http
GET /api/media_objects
```

#### Détail d’un média

```http
GET /api/media_objects/{id}
```

#### Upload d’un média *(ROLE_ADMIN requis)*

```http
POST /api/media_objects
Content-Type: multipart/form-data
```

---

## GraphQL API

### Endpoint

```http
POST /api/graphql
```

### Exemples de requêtes

#### Liste des films

```graphql
query {
  movies {
    edges {
      node {
        id
        name
        description
        duration
        director {
          lastname
          firstname
        }
        categories {
          edges {
            node { name }
          }
        }
        actors {
          edges {
            node { fullName }
          }
        }
      }
    }
  }
}
```

#### Film par ID

```graphql
query {
  movie(id: "/api/movies/1") {
    id
    name
    description
    duration
    releaseData
    budget
    reviews {
      edges {
        node { comment rating }
      }
    }
  }
}
```

---

## Documentation interactive

* **Swagger UI (REST)** : `/api/docs`
* **GraphQL Playground** : `/api/graphql`

---

## Codes de réponse HTTP

| Code | Description              |
| ---- | ------------------------ |
| 200  | Succès (GET, PUT, PATCH) |
| 201  | Ressource créée          |
| 204  | Suppression réussie      |
| 400  | Requête invalide         |
| 401  | Non authentifié          |
| 403  | Accès refusé             |
| 404  | Ressource non trouvée    |
| 422  | Erreur de validation     |
| 500  | Erreur serveur           |
