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

### Authentification GraphQL

Pour les mutations (création, modification, suppression), ajouter le header :

```http
Authorization: Bearer <token>
```

---

## Queries (Lecture)

### Movies

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

#### Liste des films avec pagination

```graphql
query {
  movies(first: 10, after: "cursor") {
    edges {
      node {
        id
        name
        duration
      }
      cursor
    }
    pageInfo {
      hasNextPage
      endCursor
    }
    totalCount
  }
}
```

#### Liste des films avec filtre

```graphql
query {
  movies(name: "Avatar") {
    edges {
      node {
        id
        name
        description
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
    image
    url
    nbEntries
    createdAt
    director {
      id
      lastname
      firstname
    }
    categories {
      edges {
        node {
          id
          name
        }
      }
    }
    actors {
      edges {
        node {
          id
          fullName
          bio
        }
      }
    }
    reviews {
      edges {
        node {
          id
          comment
          rating
          createdAt
        }
      }
    }
  }
}
```

---

### Actors

#### Liste des acteurs

```graphql
query {
  actors {
    edges {
      node {
        id
        lastname
        firstname
        fullName
        age
        bio
        dob
        dod
        createdAt
        photo {
          contentUrl
        }
        movies {
          edges {
            node {
              id
              name
            }
          }
        }
      }
    }
  }
}
```

#### Liste des acteurs avec filtre

```graphql
query {
  actors(lastname: "DiCaprio") {
    edges {
      node {
        id
        fullName
        bio
      }
    }
  }
}
```

#### Acteur par ID

```graphql
query {
  actor(id: "/api/actors/1") {
    id
    lastname
    firstname
    fullName
    age
    bio
    dob
    dod
    createdAt
    photo {
      contentUrl
    }
    movies {
      edges {
        node {
          id
          name
          duration
        }
      }
    }
  }
}
```

---

### Directors

#### Liste des réalisateurs

```graphql
query {
  directors {
    edges {
      node {
        id
        lastname
        firstname
        dob
        dod
        movies {
          edges {
            node {
              id
              name
            }
          }
        }
      }
    }
  }
}
```

#### Réalisateur par ID

```graphql
query {
  director(id: "/api/directors/1") {
    id
    lastname
    firstname
    dob
    dod
    movies {
      edges {
        node {
          id
          name
          duration
          releaseData
        }
      }
    }
  }
}
```

---

### Categories

#### Liste des catégories

```graphql
query {
  categories {
    edges {
      node {
        id
        name
        createdAt
        movies {
          edges {
            node {
              id
              name
            }
          }
        }
      }
    }
  }
}
```

#### Catégorie par ID

```graphql
query {
  category(id: "/api/categories/1") {
    id
    name
    createdAt
    movies {
      edges {
        node {
          id
          name
          duration
        }
      }
    }
  }
}
```

---

### Reviews

#### Liste des critiques

```graphql
query {
  reviews {
    edges {
      node {
        id
        comment
        rating
        createdAt
        updatedAt
        movie {
          id
          name
        }
        user {
          id
          email
        }
      }
    }
  }
}
```

#### Critique par ID

```graphql
query {
  review(id: "/api/reviews/1") {
    id
    comment
    rating
    createdAt
    updatedAt
    movie {
      id
      name
      description
    }
    user {
      id
      email
    }
  }
}
```

---

### Users

#### Utilisateur par ID

```graphql
query {
  user(id: "/api/users/1") {
    id
    email
    reviews {
      edges {
        node {
          id
          comment
          rating
        }
      }
    }
  }
}
```

---

### Media Objects

#### Liste des médias

```graphql
query {
  mediaObjects {
    edges {
      node {
        id
        contentUrl
        filePath
      }
    }
  }
}
```

#### Média par ID

```graphql
query {
  mediaObject(id: "/api/media_objects/1") {
    id
    contentUrl
    filePath
  }
}
```

---

## Mutations (Écriture) - ROLE_ADMIN requis

### Movies

#### Créer un film

```graphql
mutation {
  createMovie(input: {
    name: "Inception"
    description: "Un film de Christopher Nolan"
    duration: 148
    budget: 160000000
    director: "/api/directors/1"
    categories: ["/api/categories/1", "/api/categories/2"]
  }) {
    movie {
      id
      name
      description
      duration
    }
  }
}
```

#### Modifier un film

```graphql
mutation {
  updateMovie(input: {
    id: "/api/movies/1"
    name: "Inception (Updated)"
    description: "Description mise à jour"
    duration: 150
  }) {
    movie {
      id
      name
      description
      duration
    }
  }
}
```

#### Supprimer un film

```graphql
mutation {
  deleteMovie(input: {
    id: "/api/movies/1"
  }) {
    movie {
      id
    }
  }
}
```

---

### Actors

#### Créer un acteur

```graphql
mutation {
  createActor(input: {
    lastname: "DiCaprio"
    firstname: "Leonardo"
    bio: "Acteur américain né en 1974"
    dob: "1974-11-11"
  }) {
    actor {
      id
      fullName
      bio
    }
  }
}
```

#### Modifier un acteur

```graphql
mutation {
  updateActor(input: {
    id: "/api/actors/1"
    lastname: "DiCaprio"
    firstname: "Leonardo"
    bio: "Bio mise à jour"
  }) {
    actor {
      id
      fullName
      bio
    }
  }
}
```

#### Supprimer un acteur

```graphql
mutation {
  deleteActor(input: {
    id: "/api/actors/1"
  }) {
    actor {
      id
    }
  }
}
```

---

### Directors

#### Créer un réalisateur

```graphql
mutation {
  createDirector(input: {
    lastname: "Nolan"
    firstname: "Christopher"
    dob: "1970-07-30"
  }) {
    director {
      id
      lastname
      firstname
    }
  }
}
```

#### Modifier un réalisateur

```graphql
mutation {
  updateDirector(input: {
    id: "/api/directors/1"
    lastname: "Nolan"
    firstname: "Christopher"
  }) {
    director {
      id
      lastname
      firstname
    }
  }
}
```

#### Supprimer un réalisateur

```graphql
mutation {
  deleteDirector(input: {
    id: "/api/directors/1"
  }) {
    director {
      id
    }
  }
}
```

---

### Categories

#### Créer une catégorie

```graphql
mutation {
  createCategory(input: {
    name: "Science-Fiction"
  }) {
    category {
      id
      name
    }
  }
}
```

#### Modifier une catégorie

```graphql
mutation {
  updateCategory(input: {
    id: "/api/categories/1"
    name: "Sci-Fi"
  }) {
    category {
      id
      name
    }
  }
}
```

#### Supprimer une catégorie

```graphql
mutation {
  deleteCategory(input: {
    id: "/api/categories/1"
  }) {
    category {
      id
    }
  }
}
```

---

### Reviews

#### Créer une critique

```graphql
mutation {
  createReview(input: {
    comment: "Excellent film !"
    rating: 5
    movie: "/api/movies/1"
  }) {
    review {
      id
      comment
      rating
      createdAt
    }
  }
}
```

#### Modifier une critique

```graphql
mutation {
  updateReview(input: {
    id: "/api/reviews/1"
    comment: "Commentaire modifié"
    rating: 4
  }) {
    review {
      id
      comment
      rating
    }
  }
}
```

#### Supprimer une critique

```graphql
mutation {
  deleteReview(input: {
    id: "/api/reviews/1"
  }) {
    review {
      id
    }
  }
}
```

---

### Users

#### Créer un utilisateur (inscription)

```graphql
mutation {
  createUser(input: {
    email: "newuser@example.com"
    plainPassword: "SecurePassword123"
  }) {
    user {
      id
      email
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
