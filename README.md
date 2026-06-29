# 🧵 enhanced-api_labs — Sprint Final

**Stack:** Laravel 13 | PHP 8.5 | MySQL 8.0 | Laravel Sail | `laravel/ai` SDK | Laravel Sanctum | Pest

---

## 🗺️ Table des matières

1. [Description du projet](#-description-du-projet)
2. [Stack technique](#-stack-technique)
3. [Prérequis](#-prérequis)
4. [Installation](#-installation)
5. [Lancer le projet](#-lancer-le-projet)
6. [Credentials de test](#-credentials-de-test)
7. [Routes API](#-routes-api)
8. [Architecture](#-architecture)
9. [Concepts clés](#-concepts-clés)
10. [Programme de la semaine](#-programme-de-la-semaine)
11. [Compléter les labs](#-compléter-les-labs)
12. [Livrables](#-livrables)

---

## 📖 Description du projet

**enhanced-api_labs** est le repo de consolidation basé sur ThreadForge — une API REST qui transforme du contenu brut (notes de dev, README, articles) en posts X structurés et optimisés grâce à l'IA.

Le créateur soumet un contenu → l'API répond immédiatement `202 Accepted` → un Job traite la génération en arrière-plan → un post structuré est disponible → un agent ghostwriter conversationnel permet d'affiner le résultat.

**Flux principal :**
```
POST /api/content/repurpose
        │
        ▼ (202 immédiat)
   [Job dispatché]
        │
        ▼ (worker en arrière-plan)
   laravel/ai → Groq
        │
        ▼
   Post structuré en base
        │
        ▼
   GET /api/posts
```

---

## 🛠️ Stack technique

| Outil | Version | Rôle |
|-------|---------|------|
| Laravel | 13.x | Framework PHP |
| PHP | 8.5 | Langage |
| MySQL | 8.0 | Base de données |
| Laravel Sail | latest | Docker |
| Laravel Sanctum | latest | Auth par Bearer Token |
| `laravel/ai` SDK | latest | Structured Output + Agents |
| Groq | — | Provider IA (LLM) |
| Pest | 3.x | Tests automatisés |
| Scribe | latest | Documentation API auto-générée |
| Laravel Debugbar | latest | Détection N+1 |

---

## ✅ Prérequis

- Docker Desktop installé et lancé
- PHP 8.5+ (pour les commandes `composer` hors Sail)
- Composer installé globalement
- Un compte Groq et une clé API → [console.groq.com](https://console.groq.com)
- Git

---

## 🚀 Installation

### 1. Cloner le repo

```bash
git clone git@github.com:<your-username>/enhanced-api_labs.git
cd enhanced-api_labs
```

### 2. Copier et configurer l'environnement

```bash
cp .env.example .env
```

Éditer `.env` avec vos valeurs :

```env
APP_NAME=enhanced-api_labs
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=threadforge
DB_USERNAME=sail
DB_PASSWORD=password

QUEUE_CONNECTION=database

GROQ_API_KEY=groq-VOTRE_CLE_ICI
```

### 3. Installer les dépendances

```bash
composer install
```

### 4. Lancer Docker

```bash
./vendor/bin/sail up -d
```

> 💡 Ajouter un alias pour plus de confort : `alias sail='./vendor/bin/sail'`

### 5. Générer la clé applicative

```bash
sail artisan key:generate
```

### 6. Lancer les migrations et les seeders

```bash
sail artisan migrate:fresh --seed
```

### 7. Installer le SDK IA (si pas déjà fait)

```bash
sail artisan ai:install
sail artisan migrate
```

### 8. Générer la documentation Scribe

```bash
sail artisan scribe:generate
```

---

## ▶️ Lancer le projet

```bash
# Démarrer Docker
sail up -d

# Lancer le worker de queue (terminal séparé — obligatoire pour les LABs 4 et 5)
sail artisan queue:work

# Lancer les tests
php artisan test

# Accès
# → API :          http://localhost
# → phpMyAdmin :   http://localhost:8081
# → Docs Scribe :  http://localhost/docs
```

---

## 🔑 Credentials de test

Les seeders créent les utilisateurs suivants (mot de passe : `password` pour tous) :

| Nom | Email | Rôle |
|-----|-------|------|
| Ayoub Dev | ayoub@threadforge.test | Créateur |
| Sara Tester | sara@threadforge.test | Créateur |

**Obtenir un token en Tinker :**
```bash
sail artisan tinker
>>> $user = App\Models\User::first();
>>> $user->createToken('test')->plainTextToken;
```

**Utiliser le token dans Postman :**
```
Authorization: Bearer <votre_token>
Accept: application/json
```

---

## 🗺️ Routes API

### Authentification — public

| Méthode | Route | Description | Status |
|---------|-------|-------------|--------|
| `POST` | `/api/register` | Inscription + token | `201` |
| `POST` | `/api/login` | Connexion + token | `200` |
| `POST` | `/api/logout` | Déconnexion | `200` |

### Blueprints — `auth:sanctum` requis

| Méthode | Route | Description | Status |
|---------|-------|-------------|--------|
| `GET` | `/api/blueprints` | Lister ses blueprints | `200` |
| `POST` | `/api/blueprints` | Créer un blueprint | `201` |
| `GET` | `/api/blueprints/{id}` | Détail d'un blueprint | `200` |
| `PUT` | `/api/blueprints/{id}` | Modifier un blueprint | `200` |
| `DELETE` | `/api/blueprints/{id}` | Supprimer un blueprint | `200` |

### Génération de contenu — `auth:sanctum` requis

| Méthode | Route | Description | Status |
|---------|-------|-------------|--------|
| `POST` | `/api/content/repurpose` | Soumettre un contenu brut | `202` |
| `GET` | `/api/posts` | Lister ses posts générés | `200` |
| `GET` | `/api/posts/{id}` | Détail d'un post | `200` |
| `PATCH` | `/api/posts/{id}` | Changer le statut d'un post | `200` |

### Agent ghostwriter — `auth:sanctum` requis

| Méthode | Route | Description | Status |
|---------|-------|-------------|--------|
| `POST` | `/api/posts/{post}/chat` | Poser une question à l'agent | `200` |

---

## 🏗️ Architecture

```
app/
├── AI/
│   ├── Agents/
│   │   └── PostGenerator.php         # Agent structured output
│   ├── GhostwriterAgent.php          # Agent conversationnel
│   ├── Schemas/
│   │   └── PostGenerationSchema.php  # Contrat JSON garanti
│   └── Tools/
│       ├── GetCampaignRulesTool.php  # Tool → règles du blueprint
│       └── GetPostHistoryTool.php    # Tool → historique du post
├── Enums/
│   └── PostStatusEnum.php            # draft | archived | posted
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── BlueprintController.php
│   │   ├── ChatController.php
│   │   ├── PostController.php
│   │   └── RawContentController.php
│   ├── Requests/                     # Form Requests (validation)
│   └── Resources/                    # API Resources (formatage sortie)
├── Jobs/
│   └── GeneratePostJob.php           # Job async → appel IA
└── Models/
    ├── Blueprint.php
    ├── Post.php
    ├── RawContent.php
    └── User.php
```

### Modèle de données (MLD)

```
users
  id | name | email | password | timestamps

blueprints
  id | name | tone | max_hashtag | max_characters
     | banned_word | extra_rules
     | user_id (FK → users.id)
     | timestamps

raw_contents
  id | body | status (en_attente | traite)
     | blueprint_id (FK → blueprints.id)
     | user_id (FK → users.id)
     | timestamps

posts
  id | hook | body_points (JSON) | technical_readability_score
     | suggested_hashtags (JSON) | tone_compliance_justification
     | payload_brut (JSON) | statut_publication (draft|archived|posted)
     | raw_content_id (FK → raw_contents.id)
     | timestamps
```

---

## 🧠 Concepts clés

### Structured Output
Au lieu de demander du JSON dans le prompt (non fiable), on **impose un schéma** au SDK `laravel/ai`. Le modèle est contraint de respecter exactement cette forme. Zéro `json_decode` manuel, zéro champ manquant.

```json
{
  "hook_propose": "string",
  "body_points": ["string"],
  "technical_readability_score": "integer (0-100)",
  "suggested_hashtags": ["string"],
  "tone_compliance_justification": "string"
}
```

### Eloquent Casts
Les colonnes JSON (`body_points`, `suggested_hashtags`, `payload_brut`) sont déclarées avec le cast `'array'` — Laravel sérialise/désérialise automatiquement. Zéro `json_encode`/`json_decode` dans le code applicatif.

### Jobs & Queues
L'appel à l'IA prend 6-8 secondes. Il ne reste **jamais** dans la requête HTTP. Le controller dispatch un Job et répond `202 Accepted` immédiatement. Le worker (`queue:work`) traite en arrière-plan.

### Agent + Tools
Le `GhostwriterAgent` dispose de deux Tools PHP réels :
- `getCampaignRules` → lit les règles du blueprint en base
- `getPostHistory` → lit le contenu du post en base

L'agent **ne peut pas inventer** ces données — il appelle toujours le Tool.

### Conversation Memory
Le SDK stocke l'historique par `conversation_id` stable (`post_{id}_user_{id}`). Les questions de suivi gardent le contexte sans renvoyer tout le contenu.

---

## 📅 Programme de la semaine

| Séance | Thème | Labs |
|--------|-------|------|
| Séance 1 | API REST propre — routes, controller, Form Request, Resource | LAB 1 · LAB 2 |
| Séance 2 | Jobs & Queues — async, 202, worker | LAB 3 · LAB 4 |
| Séance 3 | AI SDK — Structured Output, schema, Casts | LAB 5 |
| Séance 4 | Tests Pest — feature tests, fakes, 401/422 | LAB 6 · LAB 7 · LAB 8 |
| Séance 5 | Consolidation — audit, N+1, documentation finale | — |

---

## 🧪 Compléter les labs

> Chaque lab a un livrable `.md` à créer dans ton repo (`lab1-notes.md`, `lab2-notes.md`…) avec les captures demandées.

---

### 📝 LAB 1 — Routes API + Controller + réponse JSON
**Séance 1 | Durée : 45 min**

**Objectif :** Reconstruire le squelette REST de la ressource `blueprints` : routes dans `api.php`, controller d'API, réponses JSON propres.

**Étape 1 — Vérifier `routes/api.php`**

Ouvre le fichier. Toutes les routes déclarées ici sont automatiquement préfixées par `/api`.

**Étape 2 — Créer le controller**

```bash
php artisan make:controller Api/BlueprintController --api
```

Le flag `--api` génère un controller sans les méthodes `create` et `edit` (inutiles en API — pas de formulaire HTML).

**Étape 3 — Déclarer les routes dans `routes/api.php`**

```php
use App\Http\Controllers\Api\BlueprintController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/blueprints', [BlueprintController::class, 'index']);
    Route::get('/blueprints/{blueprint}', [BlueprintController::class, 'show']);
});
```

**Étape 4 — Implémenter `index` dans `BlueprintController`**

```php
public function index()
{
    $blueprints = auth()->user()->blueprints()->latest()->get();
    return response()->json($blueprints);
}
```

**Étape 5 — Implémenter `show`**

```php
public function show(Blueprint $blueprint)
{
    return response()->json($blueprint);
}
```

**Étape 6 — Tester dans Postman**

Créer d'abord un token Sanctum en Tinker :
```bash
sail artisan tinker
>>> $user = App\Models\User::first();
>>> $user->createToken('test')->plainTextToken;
```

Puis dans Postman, ajouter le header `Authorization: Bearer <token>` et `Accept: application/json`.

- `GET http://localhost/api/blueprints` → doit renvoyer `200` + tableau JSON
- `GET http://localhost/api/blueprints/1` → doit renvoyer `200` + un blueprint

**Livrable → créer `lab1-notes.md` avec :**
- Capture `GET /api/blueprints` (code 200 visible)
- Capture `GET /api/blueprints/{id}`

---

### 📝 LAB 2 — Form Request + status codes + API Resource
**Séance 1 | Durée : 40 min**

**Objectif :** Valider les entrées avec une Form Request (`422` si invalide), renvoyer un `201` à la création, formater la sortie avec une API Resource.

**Étape 1 — Créer la Form Request**

```bash
php artisan make:request StoreBlueprintRequest
```

Dans `app/Http/Requests/StoreBlueprintRequest.php` :

```php
public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    return [
        'nom'            => ['required', 'string', 'max:100'],
        'ton'            => ['required', 'string', 'max:255'],
        'max_hashtags'   => ['required', 'integer', 'min:0', 'max:10'],
        'max_caracteres' => ['required', 'integer', 'min:50', 'max:280'],
    ];
}
```

**Étape 2 — Créer l'API Resource**

```bash
php artisan make:resource BlueprintResource
```

Dans `app/Http/Resources/BlueprintResource.php`, définir **explicitement** les champs qui sortent :

```php
public function toArray($request): array
{
    return [
        'id'             => $this->id,
        'nom'            => $this->nom,
        'ton'            => $this->ton,
        'max_hashtags'   => $this->max_hashtags,
        'max_caracteres' => $this->max_caracteres,
        // ← pas de user_id, pas de timestamps internes
    ];
}
```

**Étape 3 — Ajouter la route `store` dans `routes/api.php`**

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/blueprints', [BlueprintController::class, 'index']);
    Route::get('/blueprints/{blueprint}', [BlueprintController::class, 'show']);
    Route::post('/blueprints', [BlueprintController::class, 'store']); // ← nouveau
});
```

**Étape 4 — Implémenter `store` dans `BlueprintController`**

```php
public function store(StoreBlueprintRequest $request)
{
    $blueprint = auth()->user()->blueprints()->create($request->validated());

    return (new BlueprintResource($blueprint))
        ->response()
        ->setStatusCode(201);
}
```

**Étape 5 — Remplacer les `json()` du LAB 1 par la Resource**

```php
// index — remplacer response()->json($blueprints)
public function index()
{
    return BlueprintResource::collection(
        auth()->user()->blueprints()->latest()->get()
    );
}

// show — remplacer response()->json($blueprint)
public function show(Blueprint $blueprint)
{
    return new BlueprintResource($blueprint);
}
```

**Étape 6 — Tester les 3 cas dans Postman**

`POST http://localhost/api/blueprints` avec body JSON valide :
```json
{
  "nom": "Tech éducatif",
  "ton": "professionnel",
  "max_hashtags": 3,
  "max_caracteres": 280
}
```
→ doit renvoyer `201` + la Resource (sans `user_id`)

`POST http://localhost/api/blueprints` avec `nom` vide :
```json
{ "nom": "", "ton": "pro", "max_hashtags": 2, "max_caracteres": 280 }
```
→ doit renvoyer `422` + erreurs JSON

`GET http://localhost/api/blueprints` → vérifier qu'aucun champ interne n'apparaît

**Livrable → créer `lab2-notes.md` avec :**
- Capture `POST` valide → `201` avec la Resource
- Capture `POST` invalide → `422` avec les erreurs
- Une phrase : quel champ `Model::all()` aurait exposé que la Resource bloque

---

### 📝 LAB 3 — Le cycle d'un Job (version simple)
**Séance 2 | Durée : 45 min**

**Objectif :** Comprendre le cycle complet queue → dispatch → worker sur un cas minimal.

**Étape 1 — Configurer la queue dans `.env`**

```env
QUEUE_CONNECTION=database
```

Puis vider le cache de config :
```bash
php artisan config:clear
```

**Étape 2 — Migrer (la table `jobs` est fournie par Laravel 13)**

```bash
php artisan migrate
```

**Étape 3 — Créer le Job**

```bash
php artisan make:job TraiterRawContentJob
```

Dans `app/Jobs/TraiterRawContentJob.php` :

```php
<?php

namespace App\Jobs;

use App\Models\RawContent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TraiterRawContentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public RawContent $rawContent) {}

    public function handle(): void
    {
        sleep(5); // simule un traitement lent
        $this->rawContent->update(['statut' => 'traite']);
    }
}
```

**Étape 4 — Dispatcher le Job (dans un controller de test ou Tinker)**

```bash
sail artisan tinker
>>> $user = App\Models\User::first();
>>> $rc = $user->rawContents()->create([
...     'blueprint_id' => 1,
...     'contenu_brut' => 'Test notes de dev',
...     'statut' => 'en_attente',
... ]);
>>> App\Jobs\TraiterRawContentJob::dispatch($rc);
```

**Étape 5 — Observer SANS worker**

Ouvrir phpMyAdmin (`http://localhost:8081`) → table `jobs` → une ligne est apparue. La table `raw_contents` → statut toujours `en_attente`. **Rien ne se passe sans worker.**

**Étape 6 — Lancer le worker dans un terminal séparé**

```bash
php artisan queue:work
```

Après ~5 secondes : la ligne dans `jobs` disparaît, le statut dans `raw_contents` passe à `traite`.

> En développement, préférer `queue:listen` qui recharge le code après chaque modification du Job.

**Livrable → créer `lab3-notes.md` avec :**
- Capture table `jobs` avec le Job en attente (avant worker)
- Capture statut `traite` en base (après worker)

---

### 📝 LAB 4 — Rendre la génération asynchrone
**Séance 2 | Durée : 45 min**

**Objectif :** Brancher la vraie génération IA sur le Job. L'endpoint répond `202` immédiatement.

**Étape 1 — Transformer `handle()` pour appeler l'IA**

Dans `TraiterRawContentJob` :

```php
public function handle(): void
{
    $response = (new \App\Ai\Agents\PostGenerator)->prompt(
        $this->rawContent->contenu_brut
    );

    $this->rawContent->generatedPost()->create([
        'hook_propose'                  => $response['hook_propose'],
        'body_points'                   => $response['body_points'],
        'technical_readability_score'   => $response['technical_readability_score'],
        'suggested_hashtags'            => $response['suggested_hashtags'],
        'tone_compliance_justification' => $response['tone_compliance_justification'],
        'statut'                        => 'draft',
    ]);

    $this->rawContent->update(['statut' => 'traite']);
}
```

**Étape 2 — Créer le controller et la Form Request**

```bash
php artisan make:controller Api/ContentController
php artisan make:request StoreContentRequest
```

Dans `StoreContentRequest` :

```php
public function authorize(): bool { return true; }

public function rules(): array
{
    return [
        'blueprint_id' => ['required', 'integer', 'exists:blueprints,id'],
        'contenu_brut' => ['required', 'string'],
    ];
}
```

Dans `ContentController` :

```php
public function repurpose(StoreContentRequest $request)
{
    $rawContent = auth()->user()->rawContents()->create([
        'blueprint_id' => $request->validated('blueprint_id'),
        'contenu_brut' => $request->validated('contenu_brut'),
        'statut'       => 'en_attente',
    ]);

    TraiterRawContentJob::dispatch($rawContent);

    return response()->json([
        'message'        => 'Contenu reçu, génération en cours.',
        'raw_content_id' => $rawContent->id,
    ], 202);
}
```

**Étape 3 — Déclarer la route dans `routes/api.php`**

```php
Route::middleware('auth:sanctum')->post(
    '/content/repurpose',
    [ContentController::class, 'repurpose']
);
```

**Étape 4 — Tester le flux complet**

S'assurer que le worker tourne (`php artisan queue:work`), puis dans Postman :

```
POST http://localhost/api/content/repurpose
Body JSON : { "blueprint_id": 1, "contenu_brut": "Mes notes de dev du jour..." }
```

→ réponse `202` **immédiate** (quelques ms, pas 8 secondes)
→ quelques secondes après : un `generated_post` apparaît en base

**Livrable → créer `lab4-notes.md` avec :**
- Capture réponse `202` (temps de réponse visible dans Postman)
- Capture `generated_post` créé en base après passage du worker

---

### 📝 LAB 5 — Agent + Structured Output + Casts
**Séance 3 | Durée : 90 min**

**Objectif :** Garantir la forme de la réponse IA avec un schéma imposé. Stocker proprement avec les Casts Eloquent.

**Étape 1 — Créer l'agent**

```bash
php artisan make:agent PostGenerator --structured
```

L'agent est créé dans `App\Ai\Agents\PostGenerator.php`.

**Étape 2 — Définir les instructions (system prompt)**

```php
public function instructions(): string
{
    return "Tu es un ghostwriter pour la tech community sur X. À partir d'un contenu technique brut (notes de dev, README, article), tu produis un post optimisé : un hook accrocheur, des points clés courts, un score de lisibilité technique (0-100), des hashtags pertinents, et une justification du respect du ton.";
}
```

**Étape 3 — Définir le `schema()` — le contrat JSON garanti**

```php
use Illuminate\Contracts\JsonSchema\JsonSchema;

public function schema(JsonSchema $schema): array
{
    return [
        'hook_propose'                  => $schema->string()->required(),
        'body_points'                   => $schema->array()->items($schema->string())->required(),
        'technical_readability_score'   => $schema->integer()->required(),
        'suggested_hashtags'            => $schema->array()->items($schema->string())->required(),
        'tone_compliance_justification' => $schema->string()->required(),
    ];
}
```

**Étape 4 — Créer l'enum `StatutPost`**

Créer `app/Enums/StatutPost.php` :

```php
<?php

namespace App\Enums;

enum StatutPost: string
{
    case Draft    = 'draft';
    case Archived = 'archived';
    case Posted   = 'posted';
}
```

**Étape 5 — Ajouter les Casts sur le model `GeneratedPost`**

```php
use App\Enums\StatutPost;

protected function casts(): array
{
    return [
        'statut'             => StatutPost::class,
        'body_points'        => 'array',
        'suggested_hashtags' => 'array',
        'payload_brut'       => 'array',
    ];
}
```

**Étape 6 — Tester l'agent seul en Tinker**

```bash
sail artisan tinker
>>> $r = (new App\Ai\Agents\PostGenerator)->prompt("Notes du jour : j'ai appris les queues Laravel...");
>>> $r['hook_propose'];           // une string
>>> $r['body_points'];            // un tableau
>>> $r['technical_readability_score']; // un integer
```

**Étape 7 — Vérifier le stockage via le Job**

Relancer `POST /api/content/repurpose` + worker actif → vérifier dans phpMyAdmin que `body_points` est stocké comme JSON et récupéré comme tableau PHP (grâce au Cast).

**Livrable → créer `lab5-notes.md` avec :**
- Capture appel Tinker → réponse structurée avec les 5 champs
- Capture `generated_post` en base avec `body_points` en tableau
- Une phrase : pourquoi le structured output est plus sûr que `json_decode` sur un prompt libre

---

### 📝 LAB 6 — Premier feature test sur un endpoint
**Séance 4 | Durée : 35 min**

**Objectif :** Installer Pest et écrire un test `200` + structure JSON sur `GET /api/blueprints`.

**Étape 1 — Installer Pest (si pas déjà présent)**

```bash
composer require pestphp/pest pestphp/pest-plugin-laravel --dev --with-all-dependencies
./vendor/bin/pest --init
```

> Dans les nouveaux projets Laravel 13, Pest est déjà installé. Vérifier avec `./vendor/bin/pest --version`.

**Étape 2 — Créer le fichier de test**

```bash
php artisan make:test BlueprintTest --pest
```

Le fichier apparaît dans `tests/Feature/BlueprintTest.php`.

**Étape 3 — Écrire le test**

```php
<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('liste les blueprints d\'un utilisateur authentifié', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $user->blueprints()->create([
        'nom'            => 'Tech éducatif',
        'ton'            => 'professionnel',
        'max_hashtags'   => 2,
        'max_caracteres' => 280,
    ]);

    $response = $this->getJson('/api/blueprints');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                ['id', 'nom', 'ton', 'max_hashtags', 'max_caracteres'],
            ],
        ]);
});
```

**Étape 4 — Lancer les tests**

```bash
php artisan test
```

→ doit afficher une ligne verte ✓

**Livrable → créer `lab6-notes.md` avec :**
- Capture du terminal avec le test vert

---

### 📝 LAB 7 — Tester une route protégée (401) et une validation (422)
**Séance 4 | Durée : 35 min**

**Objectif :** Vérifier automatiquement que l'API rejette proprement les requêtes sans token et les données invalides.

**Étape 1 — Ajouter le test `401` dans `BlueprintTest.php`**

```php
it('rejette une requête sans token', function () {
    $response = $this->getJson('/api/blueprints');

    $response->assertStatus(401);
});
```

**Étape 2 — Ajouter le test `422`**

```php
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('refuse un blueprint invalide', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/blueprints', [
        'nom' => '', // champ requis vide
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['nom']);
});
```

**Étape 3 — Lancer les tests**

```bash
php artisan test
```

→ 3 lignes vertes au total (LAB 6 + les 2 nouveaux)

**Livrable → créer `lab7-notes.md` avec :**
- Capture du terminal montrant les 2 nouveaux tests verts

---

### 📝 LAB 8 — Tester un Job dispatché (sans appeler l'IA)
**Séance 4 | Durée : 25 min**

**Objectif :** Vérifier que la soumission d'un contenu dispatche bien le Job, sans jamais appeler la vraie IA.

**Étape 1 — Créer le fichier de test**

```bash
php artisan make:test ContentGenerationTest --pest
```

**Étape 2 — Écrire le test avec `Queue::fake()`**

```php
<?php

use App\Jobs\TraiterRawContentJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

it('dispatch le job de génération à la soumission', function () {
    Queue::fake(); // ← intercepte les dispatches, n'exécute PAS le Job

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $blueprint = $user->blueprints()->create([
        'nom' => 'Test', 'ton' => 'pro',
        'max_hashtags' => 2, 'max_caracteres' => 280,
    ]);

    $response = $this->postJson('/api/content/repurpose', [
        'blueprint_id' => $blueprint->id,
        'contenu_brut' => 'Mes notes de dev du jour.',
    ]);

    $response->assertStatus(202);
    Queue::assertPushed(TraiterRawContentJob::class);
});
```

> `Queue::fake()` intercepte tous les `dispatch()` — le Job est enregistré mais **pas exécuté**. La vraie IA n'est jamais appelée. C'est exactement ce qu'on veut dans un test.

**Étape 3 — Lancer les tests**

```bash
php artisan test
```

→ tous les tests passent, y compris ce nouveau

**Livrable → créer `lab8-notes.md` avec :**
- Capture du terminal avec tous les tests verts
- Une phrase : pourquoi on fake la queue au lieu de l'exécuter vraiment

---

## 📦 Livrables

- [ ] Repo `enhanced-api_labs` avec minimum 20 commits atomiques
- [ ] Branches : `feature/auth`, `feature/blueprints`, `feature/content-generation`, `feature/agent-conversationnel`
- [ ] `AGENTS.md` à la racine
- [ ] `lab1-notes.md` à `lab8-notes.md` avec captures
- [ ] Documentation Scribe complète (`public/docs`)
- [ ] MCD + MLD
- [ ] Tests Pest verts (`php artisan test`)
- [ ] N+1 corrigé (Debugbar)
- [ ] `.env` jamais commité

---

*Sprint Final | enhanced-api_labs*