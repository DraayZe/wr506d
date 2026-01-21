# API Movies - Documentation des Requetes

API REST et GraphQL pour la gestion de films, acteurs, realisateurs et critiques.

## Base URL

```
http://localhost:8319/api
```

## Authentification

L'API utilise **JWT (JSON Web Tokens)** pour l'authentification.

### Obtenir un token

```bash
POST /auth
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Reponse :**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### Utiliser le token

Ajouter le header `Authorization` a chaque requete authentifiee :
```
Authorization: Bearer <token>
```

---

# REST API

## Movies

### Liste des films
```http
GET /api/movies
```

**Filtres disponibles :**
```http
GET /api/movies?name=Avatar
GET /api/movies?duration[gte]=100&duration[lte]=200
```

### Detail d'un film
```http
GET /api/movies/{id}
```

### Creer un film
```http
POST /api/movies
Content-Type: application/ld+json

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

### Modifier un film
```http
PUT /api/movies/{id}
Content-Type: application/ld+json

{
  "name": "Inception (Updated)",
  "description": "Description mise a jour",
  "duration": 150
}
```

### Supprimer un film
```http
DELETE /api/movies/{id}
```

---

## Actors

### Liste des acteurs
```http
GET /api/actors
```

**Filtres disponibles :**
```http
GET /api/actors?lastname=DiCaprio
GET /api/actors?firstname=Leonardo
GET /api/actors?dob[after]=1970-01-01
```

### Detail d'un acteur
```http
GET /api/actors/{id}
```

### Creer un acteur
```http
POST /api/actors
Content-Type: application/ld+json

{
  "lastname": "DiCaprio",
  "firstname": "Leonardo",
  "bio": "Acteur americain ne en 1974",
  "dob": "1974-11-11"
}
```

### Modifier un acteur
```http
PUT /api/actors/{id}
Content-Type: application/ld+json

{
  "lastname": "DiCaprio",
  "firstname": "Leonardo",
  "bio": "Bio mise a jour"
}
```

### Supprimer un acteur
```http
DELETE /api/actors/{id}
```

---

## Directors

### Liste des realisateurs
```http
GET /api/directors
```

### Detail d'un realisateur
```http
GET /api/directors/{id}
```

### Creer un realisateur
```http
POST /api/directors
Content-Type: application/ld+json

{
  "lastname": "Nolan",
  "firstname": "Christopher",
  "dob": "1970-07-30"
}
```

### Modifier un realisateur
```http
PUT /api/directors/{id}
Content-Type: application/ld+json

{
  "lastname": "Nolan",
  "firstname": "Christopher"
}
```

### Supprimer un realisateur
```http
DELETE /api/directors/{id}
```

---

## Categories

### Liste des categories
```http
GET /api/categories
```

### Detail d'une categorie
```http
GET /api/categories/{id}
```

### Creer une categorie
```http
POST /api/categories
Content-Type: application/ld+json

{
  "name": "Science-Fiction"
}
```

### Modifier une categorie
```http
PUT /api/categories/{id}
Content-Type: application/ld+json

{
  "name": "Sci-Fi"
}
```

### Supprimer une categorie
```http
DELETE /api/categories/{id}
```

---

## Reviews (Authentification requise)

### Liste des critiques
```http
GET /api/reviews
```

**Filtres disponibles :**
```http
GET /api/reviews?movie=1
```

### Detail d'une critique
```http
GET /api/reviews/{id}
```

### Creer une critique
```http
POST /api/reviews
Authorization: Bearer <token>
Content-Type: application/ld+json

{
  "comment": "Excellent film !",
  "rating": 5,
  "movie": "/api/movies/1"
}
```

### Modifier sa critique
```http
PATCH /api/reviews/{id}
Authorization: Bearer <token>
Content-Type: application/merge-patch+json

{
  "comment": "Commentaire modifie",
  "rating": 4
}
```

### Supprimer sa critique
```http
DELETE /api/reviews/{id}
Authorization: Bearer <token>
```

---

## Users

### Inscription
```http
POST /api/users
Content-Type: application/ld+json

{
  "email": "newuser@example.com",
  "plainPassword": "SecurePassword123"
}
```

### Informations utilisateur courant
```http
GET /api/me
Authorization: Bearer <token>
```

---

## Media Objects

### Liste des medias
```http
GET /api/media_objects
```

### Detail d'un media
```http
GET /api/media_objects/{id}
```

### Upload un media
```http
POST /api/media_objects
Content-Type: multipart/form-data

file: <binary>
```

---

## 2FA (Two-Factor Authentication)

### Configurer la 2FA
```http
POST /api/2fa/setup
Authorization: Bearer <token>
```

### Activer la 2FA
```http
POST /api/2fa/enable
Authorization: Bearer <token>
Content-Type: application/json

{
  "code": "123456"
}
```

---

# GraphQL API

**Endpoint :** `POST /api/graphql`

## Queries

### Liste des films
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
            node {
              name
            }
          }
        }
        actors {
          edges {
            node {
              fullName
            }
          }
        }
      }
    }
  }
}
```

### Film par ID
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
        node {
          comment
          rating
        }
      }
    }
  }
}
```

### Liste des acteurs
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
        movies {
          edges {
            node {
              name
            }
          }
        }
      }
    }
  }
}
```

### Acteur par ID
```graphql
query {
  actor(id: "/api/actors/1") {
    id
    lastname
    firstname
    fullName
    age
    dob
    bio
  }
}
```

### Liste des realisateurs
```graphql
query {
  directors {
    edges {
      node {
        id
        lastname
        firstname
        movies {
          edges {
            node {
              name
            }
          }
        }
      }
    }
  }
}
```

### Liste des categories
```graphql
query {
  categories {
    edges {
      node {
        id
        name
        movies {
          edges {
            node {
              name
            }
          }
        }
      }
    }
  }
}
```

### Liste des critiques
```graphql
query {
  reviews {
    edges {
      node {
        id
        comment
        rating
        movie {
          name
        }
        user {
          email
        }
        createdAt
      }
    }
  }
}
```

### Filtrage et pagination
```graphql
query {
  movies(first: 10, after: "cursor", name: "Avatar") {
    edges {
      node {
        id
        name
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

---

## Mutations

### Creer un film
```graphql
mutation {
  createMovie(input: {
    name: "Inception"
    description: "Un film de Christopher Nolan"
    duration: 148
    director: "/api/directors/1"
    categories: ["/api/categories/1"]
    actors: ["/api/actors/1"]
  }) {
    movie {
      id
      name
    }
  }
}
```

### Modifier un film
```graphql
mutation {
  updateMovie(input: {
    id: "/api/movies/1"
    name: "Inception (Updated)"
    description: "Description mise a jour"
  }) {
    movie {
      id
      name
      description
    }
  }
}
```

### Supprimer un film
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

### Creer un acteur
```graphql
mutation {
  createActor(input: {
    lastname: "DiCaprio"
    firstname: "Leonardo"
    bio: "Acteur americain"
    dob: "1974-11-11"
  }) {
    actor {
      id
      fullName
    }
  }
}
```

### Modifier un acteur
```graphql
mutation {
  updateActor(input: {
    id: "/api/actors/1"
    bio: "Bio mise a jour"
  }) {
    actor {
      id
      fullName
      bio
    }
  }
}
```

### Supprimer un acteur
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

### Creer un realisateur
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

### Creer une categorie
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

### Creer une critique (authentifie)
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
    }
  }
}
```

### Modifier sa critique (authentifie)
```graphql
mutation {
  updateReview(input: {
    id: "/api/reviews/1"
    comment: "Commentaire modifie"
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

### Supprimer sa critique (authentifie)
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

### Inscription utilisateur
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

- **Swagger UI (REST):** http://localhost:8319/api/docs
- **GraphQL Playground:** http://localhost:8319/api/graphql

---

## Codes de reponse HTTP

| Code | Description |
|------|-------------|
| 200 | Succes (GET, PUT, PATCH) |
| 201 | Ressource creee (POST) |
| 204 | Suppression reussie (DELETE) |
| 400 | Requete invalide |
| 401 | Non authentifie |
| 403 | Acces refuse |
| 404 | Ressource non trouvee |
| 422 | Erreur de validation |
| 500 | Erreur serveur |
