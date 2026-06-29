# 🧵 ThreadForge — Sprint Final Taskboard

**Simplon Maghreb | Semaine de consolidation**
**Stack:** Laravel 13 | PHP 8.5 | MySQL 8.0 | Laravel Sail | `laravel/ai` SDK | Sanctum | Pest

> Pas de brief sur Simplonline. On travaille en parcours d'ateliers guidés.
> Objectif : passer enhanced-api_labs (basé sur ThreadForge) de « ça marche » → « propre, asynchrone et testé ».

---

## 📋 Légende des labels

| Label | Signification |
|-------|--------------|
| `REST` | API REST — routes, controller, JSON |
| `VALID` | Validation — Form Request, status codes |
| `RESOURCE` | API Resource — formatage sortie |
| `QUEUE` | Jobs & Queues — async |
| `AI` | SDK laravel/ai — structured output, agent |
| `TEST` | Tests Pest — feature tests, fakes |
| `QA` | Qualité — audit, N+1, sécurité |
| `DOC` | Documentation |

---

## 📅 Vue d'ensemble de la semaine

| Séance | Thème | Durée | Labs |
|--------|-------|-------|------|
| **Séance 1** | API REST propre | 2h | LAB 1 · LAB 2 |
| **Séance 2** | Jobs & Queues | 2h | LAB 3 · LAB 4 |
| **Séance 3** | AI SDK — Structured Output | 2h | LAB 5 |
| **Séance 4** | Tests avec Pest | 2h | LAB 6 · LAB 7 · LAB 8 |
| **Séance 5** | Consolidation & audit | 2h | — |

---

## 🏃 Séance 1 — API REST propre (le squelette propre)

**Objectif :** Distinguer une API qui « marche » d'une API propre. Reconstruire le squelette REST de ThreadForge.
**Durée :** 2h | **Repo :** branche `main` ou `feature/blueprints`

> **Rappel de l'histoire de Reda :** `return Post::all()` expose l'email, le mot de passe haché, et les métadonnées internes. Une API propre valide en entrée, formate en sortie, code correctement — « ça répond » ne suffit pas.

### Les 3 règles d'une API propre

| Règle | Mauvaise pratique | Bonne pratique |
|-------|-------------------|----------------|
| Validation en entrée | `$request->input(...)` direct | Form Request → `422` automatique |
| Status codes | Tout en `200` | `200` lecture · `201` création · `422` invalide |
| Formatage sortie | `return Model::all()` | API Resource → champs explicites uniquement |

---

### 📝 LAB 1 — Routes API + Controller + réponse JSON
**Durée : 45 min** | `REST`

| Done | # | Étape | Commande / Code |
|:----:|:--|-------|-----------------|
| [ ] | 1 | Vérifier `routes/api.php` | Toutes les routes API vont ici — préfixe `/api` automatique |
| [ ] | 2 | Créer le controller | `php artisan make:controller Api/BlueprintController --api` |
| [ ] | 3 | Déclarer les routes | Voir bloc de code ci-dessous |
| [ ] | 4 | Implémenter `index` | Retourner les blueprints de l'user connecté en JSON |
| [ ] | 5 | Implémenter `show` | Route model binding → retourner un blueprint |
| [ ] | 6 | Tester dans Postman | `GET /api/blueprints` → `200` · `GET /api/blueprints/1` → `200` |

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/blueprints', [BlueprintController::class, 'index']);
    Route::get('/blueprints/{blueprint}', [BlueprintController::class, 'show']);
});
```

```php
// BlueprintController — index
public function index()
{
    $blueprints = auth()->user()->blueprints()->latest()->get();
    return response()->json($blueprints);
}

// BlueprintController — show
public function show(Blueprint $blueprint)
{
    return response()->json($blueprint);
}
```

**Critères ✅**
- [ ] `routes/api.php` contient les deux routes
- [ ] `BlueprintController` créé avec `--api`
- [ ] `GET /api/blueprints` → JSON + code `200`
- [ ] `GET /api/blueprints/{id}` → bon blueprint

**Livrable → `lab1-notes.md`**
- Capture `GET /api/blueprints` (code 200 visible dans Postman)
- Capture `GET /api/blueprints/{id}`

**Blocages fréquents**

| Symptôme | Cause | Solution |
|----------|-------|----------|
| `Route [login] not defined` | Pas de token Sanctum | Créer un token en Tinker : `$user->createToken('test')->plainTextToken` |
| Réponse vide `[]` | Pas de blueprints en base | Créer via Tinker ou seeder |
| `404` sur `/api/blueprints` | Route dans `web.php` | Déplacer dans `api.php` |
| Relation `blueprints()` absente | Manque sur le model `User` | Ajouter `hasMany(Blueprint::class)` |

---

### 📝 LAB 2 — Form Request + status codes + API Resource
**Durée : 40 min** | `VALID` · `RESOURCE`

| Done | # | Étape | Commande / Code |
|:----:|:--|-------|-----------------|
| [ ] | 1 | Créer la Form Request | `php artisan make:request StoreBlueprintRequest` |
| [ ] | 2 | Créer l'API Resource | `php artisan make:resource BlueprintResource` |
| [ ] | 3 | Ajouter la route `store` | `Route::post('/blueprints', ...)` |
| [ ] | 4 | Implémenter `store` | Form Request en type-hint → `201` à la création |
| [ ] | 5 | Remplacer les `json()` du LAB 1 | `index` et `show` utilisent maintenant la Resource |
| [ ] | 6 | Tester 3 cas dans Postman | Valide → `201` · Invalide → `422` · Liste → pas de `user_id` |

```php
// StoreBlueprintRequest
public function authorize(): bool { return true; }

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

```php
// BlueprintResource — toArray()
return [
    'id'             => $this->id,
    'nom'            => $this->nom,
    'ton'            => $this->ton,
    'max_hashtags'   => $this->max_hashtags,
    'max_caracteres' => $this->max_caracteres,
    // ← pas de user_id, pas de timestamps internes
];
```

```php
// BlueprintController — store
public function store(StoreBlueprintRequest $request)
{
    $blueprint = auth()->user()->blueprints()->create($request->validated());
    return (new BlueprintResource($blueprint))->response()->setStatusCode(201);
}

// index → remplacer response()->json(...)
return BlueprintResource::collection(auth()->user()->blueprints()->latest()->get());

// show → remplacer response()->json(...)
return new BlueprintResource($blueprint);
```

**Critères ✅**
- [ ] `StoreBlueprintRequest` renvoie `422` si invalide
- [ ] `store` renvoie `201` à la création
- [ ] `BlueprintResource` utilisée dans `index`, `show` et `store`
- [ ] Aucun champ interne (`user_id`, mot de passe…) dans le JSON

**Livrable → `lab2-notes.md`**
- Capture `POST` valide → `201` avec la Resource
- Capture `POST` invalide → `422` avec les erreurs
- Une phrase : quel champ `Model::all()` aurait exposé que la Resource bloque

**Blocages fréquents**

| Symptôme | Cause | Solution |
|----------|-------|----------|
| `422` ne se déclenche pas | Validation dans le controller | Utiliser le type-hint `store(StoreBlueprintRequest $request)` |
| `403 unauthorized` | `authorize()` retourne `false` | Mettre `return true;` |
| Champs internes encore visibles | Modèle retourné directement | Vérifier les 3 méthodes utilisent la Resource |
| `422` non JSON | Header manquant | Ajouter `Accept: application/json` dans Postman |

---

## 🏃 Séance 2 — Jobs & Queues (ne plus figer l'API)

**Objectif :** Sortir les traitements lents du cycle de la requête. Réponse immédiate `202`.
**Durée :** 2h | **Repo :** branche `feature/content-generation`

> **Le problème :** L'IA met 6-8 secondes. La requête reste suspendue. Timeout.
> **La solution :** On dépose un Job dans la queue et on répond immédiatement. Un worker traite en arrière-plan.
>
> *Analogie : tu déposes ta pâte au four du quartier, tu repars vaquer à tes occupations. Tu ne restes pas planté devant le four.*

### Le cycle d'un Job en 4 étapes

```
1. QUEUE_CONNECTION=database  →  table jobs en base
2. make:job MonJob            →  logique dans handle()
3. MonJob::dispatch($data)    →  dépose dans la queue, rend la main
4. queue:work                 →  exécute les Jobs en attente
```

---

### 📝 LAB 3 — Le cycle d'un Job (version simple)
**Durée : 45 min** | `QUEUE`

| Done | # | Étape | Commande / Code |
|:----:|:--|-------|-----------------|
| [ ] | 1 | Configurer la queue | `QUEUE_CONNECTION=database` dans `.env` |
| [ ] | 2 | Migrer | `php artisan migrate` (table `jobs` fournie par Laravel 13) |
| [ ] | 3 | Créer le Job | `php artisan make:job TraiterRawContentJob` |
| [ ] | 4 | Implémenter `handle()` | `sleep(5)` + mise à jour du statut |
| [ ] | 5 | Dispatcher le Job | Créer un `raw_content` + `TraiterRawContentJob::dispatch($rawContent)` |
| [ ] | 6 | Observer SANS worker | Statut reste `en_attente`, ligne dans table `jobs` |
| [ ] | 7 | Lancer le worker | `php artisan queue:work` → statut passe à `traite` |

```php
// TraiterRawContentJob
class TraiterRawContentJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public RawContent $rawContent) {}

    public function handle(): void
    {
        sleep(5); // simule un traitement lent (l'IA viendra au LAB 4)
        $this->rawContent->update(['statut' => 'traite']);
    }
}
```

```php
// Dispatcher (controller ou Tinker)
$rawContent = auth()->user()->rawContents()->create([
    'blueprint_id' => $blueprintId,
    'contenu_brut' => $texte,
    'statut'       => 'en_attente',
]);
TraiterRawContentJob::dispatch($rawContent);
```

**Critères ✅**
- [ ] `QUEUE_CONNECTION=database` + table `jobs` présente
- [ ] `TraiterRawContentJob` créé et dispatché
- [ ] Sans worker : statut `en_attente`, Job dans la table
- [ ] Avec worker : statut `traite` après ~5s

**Livrable → `lab3-notes.md`**
- Capture table `jobs` avec un Job en attente (avant worker)
- Capture statut passé à `traite` (après worker)

**Blocages fréquents**

| Symptôme | Cause | Solution |
|----------|-------|----------|
| Rien ne se passe même avec le worker | `QUEUE_CONNECTION` pas sur `database` | `php artisan config:clear` après modif `.env` |
| `Class TraiterRawContentJob not found` | Namespace incorrect | Vérifier `App\Jobs` |
| Statut ne change pas | `statut` absent du `$fillable` | Ajouter `statut` dans `$fillable` de `RawContent` |

---

### 📝 LAB 4 — Rendre la génération asynchrone
**Durée : 45 min** | `QUEUE` · `AI`

| Done | # | Étape | Commande / Code |
|:----:|:--|-------|-----------------|
| [ ] | 1 | Transformer le Job | Remplacer `sleep(5)` par le vrai appel à `PostGenerator` |
| [ ] | 2 | Créer l'endpoint de soumission | `ContentController@repurpose` → dispatch + `202` immédiat |
| [ ] | 3 | Déclarer la route | `POST /api/content/repurpose` |
| [ ] | 4 | Tester le flux complet | Postman → `202` immédiat → worker → `generated_post` en base |

```php
// TraiterRawContentJob — handle() avec la vraie IA
public function handle(): void
{
    $response = (new PostGenerator)->prompt($this->rawContent->contenu_brut);

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

```php
// ContentController@repurpose
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

**Critères ✅**
- [ ] L'endpoint répond `202` immédiatement (pas de page figée)
- [ ] Le Job crée bien un `generated_post` lié au `raw_content`
- [ ] Le statut passe de `en_attente` à `traite`

**Livrable → `lab4-notes.md`**
- Capture réponse `202` (temps de réponse rapide visible dans Postman)
- Capture `generated_post` créé en base après passage du worker

**Blocages fréquents**

| Symptôme | Cause | Solution |
|----------|-------|----------|
| Réponse pas immédiate | Appel IA dans le controller | L'appel doit être **uniquement** dans le `handle()` du Job |
| `generated_post` pas créé | Relation `generatedPost()` absente | Vérifier `hasOne` sur `RawContent` + `$fillable` de `GeneratedPost` |
| Modif du Job ignorée | `queue:work` garde le code en mémoire | Redémarrer le worker (ou utiliser `queue:listen`) |

---

## 🏃 Séance 3 — AI SDK (Structured Output)

**Objectif :** Garantir la forme de la réponse IA avec un schéma imposé. Stocker proprement avec les Casts.
**Durée :** 2h | **Repo :** branche `feature/content-generation`

> **Le problème :** Demander du JSON dans le prompt, c'est jouer à la loterie. L'IA peut ajouter une phrase, oublier un champ, mettre un score en texte au lieu d'un integer.
> **La solution :** On **impose un schéma** au SDK. Le modèle n'a pas le choix — c'est ce format ou rien.

### Contrat JSON garanti

```json
{
  "hook_propose":                  "string",
  "body_points":                   ["string"],
  "technical_readability_score":   "integer (0-100)",
  "suggested_hashtags":            ["string"],
  "tone_compliance_justification": "string"
}
```

---

### 📝 LAB 5 — Agent + Structured Output + Casts
**Durée : 90 min** | `AI`

| Done | # | Étape | Commande / Code |
|:----:|:--|-------|-----------------|
| [ ] | 1 | Créer l'agent | `php artisan make:agent PostGenerator --structured` |
| [ ] | 2 | Définir les instructions | System prompt : ghostwriter tech community X |
| [ ] | 3 | Définir le `schema()` | Contrat JSON avec les 5 champs garantis |
| [ ] | 4 | Ajouter les Casts sur `GeneratedPost` | `'array'` pour les colonnes JSON + enum `StatutPost` |
| [ ] | 5 | Créer l'enum `StatutPost` | `draft` · `archived` · `posted` |
| [ ] | 6 | Tester en Tinker | `(new PostGenerator)->prompt("...")` → vérifier les champs |
| [ ] | 7 | Vérifier le stockage via le Job | `POST /api/content/repurpose` + worker → `body_points` en tableau |

```php
// PostGenerator — schema()
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

```php
// GeneratedPost — casts()
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

```php
// app/Enums/StatutPost.php
enum StatutPost: string
{
    case Draft    = 'draft';
    case Archived = 'archived';
    case Posted   = 'posted';
}
```

```bash
# Test en Tinker
sail artisan tinker
>>> $r = (new \App\Ai\Agents\PostGenerator)->prompt("Mes notes : aujourd'hui j'ai appris les queues...");
>>> $r['hook_propose'];   // string
>>> $r['body_points'];    // tableau
```

**Critères ✅**
- [ ] Agent `PostGenerator` avec `schema()` conforme au contrat JSON
- [ ] L'appel renvoie les 5 champs garantis
- [ ] Casts `array` et enum sur `GeneratedPost`
- [ ] `body_points` est un tableau PHP — pas une string JSON brute

**Livrable → `lab5-notes.md`**
- Capture appel Tinker → réponse structurée
- Capture `generated_post` en base avec `body_points` en tableau
- Une phrase : pourquoi le structured output est plus sûr que `json_decode` sur un prompt libre

**Blocages fréquents**

| Symptôme | Cause | Solution |
|----------|-------|----------|
| Erreur d'auth IA | Provider pas `groq` ou clé manquante | Vérifier `config/ai.php` + `GROQ_API_KEY` dans `.env` |
| Réponse mauvaise forme | System prompt trop vague | Réviser les instructions + vérifier `schema()` |
| `body_points` est une string JSON | Cast `array` absent | Ajouter dans `casts()` de `GeneratedPost` |
| `Class "PostGenerator" not found` | Namespace incorrect | Vérifier `App\Ai\Agents` |

---

## 🏃 Séance 4 — Tests avec Pest (le pont vers le DevOps)

**Objectif :** Automatiser la vérification. Écrire des tests qui remplacent les clics manuels dans Postman.
**Durée :** 2h | **Repo :** toutes branches

> **Pourquoi tester ?** Sans tests, tu rejoues à la main chaque endpoint à chaque modification. Un jour tu oublies — et le bug part en production. Un test s'écrit une fois et se relance en une commande.
>
> **Lancer les tests :** `php artisan test`

### Structure d'un test Pest

```php
it('fait quelque chose', function () {
    // 1. PRÉPARE — un user, des données
    // 2. AGIT   — appelle l'endpoint
    // 3. VÉRIFIE — bon code, bonne structure
});
```

---

### 📝 LAB 6 — Premier feature test sur un endpoint
**Durée : 35 min** | `TEST`

| Done | # | Étape | Commande / Code |
|:----:|:--|-------|-----------------|
| [ ] | 1 | Installer Pest | `composer require pestphp/pest pestphp/pest-plugin-laravel --dev` puis `./vendor/bin/pest --init` |
| [ ] | 2 | Créer le fichier de test | `php artisan make:test BlueprintTest --pest` |
| [ ] | 3 | Écrire le test `index` | User auth → `GET /api/blueprints` → `200` + structure JSON |
| [ ] | 4 | Lancer les tests | `php artisan test` → ligne verte ✓ |

```php
// tests/Feature/BlueprintTest.php
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('liste les blueprints d\'un utilisateur authentifié', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $user->blueprints()->create([
        'nom' => 'Tech éducatif', 'ton' => 'professionnel',
        'max_hashtags' => 2, 'max_caracteres' => 280,
    ]);

    $response = $this->getJson('/api/blueprints');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'nom', 'ton', 'max_hashtags', 'max_caracteres']],
        ]);
});
```

**Critères ✅**
- [ ] Pest installé, `php artisan test` fonctionne
- [ ] Test vérifie code `200` et structure JSON
- [ ] Test passe (vert)

**Livrable → `lab6-notes.md`** : capture du test vert dans le terminal

**Blocages fréquents**

| Symptôme | Cause | Solution |
|----------|-------|----------|
| `Sanctum::actingAs` introuvable | `use` manquant | Ajouter `use Laravel\Sanctum\Sanctum;` |
| `assertJsonStructure` échoue sur `data` | Resource enveloppe dans `data` | Retirer le niveau `data` si ta Resource ne l'a pas |
| Test touche la vraie base | `phpunit.xml` non configuré | Configurer SQLite en mémoire + `RefreshDatabase` |

---

### 📝 LAB 7 — Tester une route protégée (401) et une validation (422)
**Durée : 35 min** | `TEST` · `VALID`

| Done | # | Étape | Commande / Code |
|:----:|:--|-------|-----------------|
| [ ] | 1 | Test `401` sans token | `GET /api/blueprints` sans auth → `assertStatus(401)` |
| [ ] | 2 | Test `422` payload invalide | `POST /api/blueprints` avec `nom` vide → `assertJsonValidationErrors` |
| [ ] | 3 | Lancer les tests | `php artisan test` → 2 nouvelles lignes vertes |

```php
it('rejette une requête sans token', function () {
    $response = $this->getJson('/api/blueprints');
    $response->assertStatus(401);
});

it('refuse un blueprint invalide', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson('/api/blueprints', [
        'nom' => '', // champ requis vide
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['nom']);
});
```

**Critères ✅**
- [ ] Test `401` sans token passe
- [ ] Test `422` avec entrée invalide passe + cible le bon champ
- [ ] Les deux tests sont verts

**Livrable → `lab7-notes.md`** : capture des 2 tests verts

**Blocages fréquents**

| Symptôme | Cause | Solution |
|----------|-------|----------|
| Test `401` renvoie `200` | Route pas protégée par `auth:sanctum` | Vérifier `routes/api.php` |
| Test `422` renvoie `200` ou erreur SQL | Form Request pas branchée | Vérifier le type-hint dans `store(StoreBlueprintRequest $request)` |
| `assertJsonValidationErrors` échoue | Nom du champ incorrect | Le nom doit correspondre exactement à la règle de validation |

---

### 📝 LAB 8 — Tester un Job dispatché (sans appeler l'IA)
**Durée : 25 min** | `TEST` · `QUEUE`

| Done | # | Étape | Commande / Code |
|:----:|:--|-------|-----------------|
| [ ] | 1 | Écrire le test avec `Queue::fake()` | Fake la queue → soumettre → vérifier `202` + `assertPushed` |
| [ ] | 2 | Lancer les tests | `php artisan test` → vert |
| [ ] | 3 | Bonus | `Agent::fake()` pour tester le `handle()` sans appeler Groq |

```php
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
    Queue::assertPushed(TraiterRawContentJob::class); // ← vérifie le dispatch
});
```

> ⚠️ Avec `Queue::fake()`, le Job n'est **pas exécuté** — c'est le but. On vérifie juste qu'il a été dispatché, sans appeler la vraie IA.

**Critères ✅**
- [ ] Test vérifie `202`
- [ ] Test vérifie que le Job est dispatché (`assertPushed`)
- [ ] Test passe sans appeler la vraie IA

**Livrable → `lab8-notes.md`**
- Capture du test vert
- Une phrase : pourquoi on fake la queue/l'IA au lieu de les exécuter vraiment

**Blocages fréquents**

| Symptôme | Cause | Solution |
|----------|-------|----------|
| `assertPushed` échoue | `Queue::fake()` oublié → Job s'exécute vraiment | Ajouter `Queue::fake()` en tout début de test |
| Test appelle quand même l'IA | Même raison | `Queue::fake()` doit être la première ligne |
| `202` non reçu | Endpoint `repurpose` mal implémenté | Revoir le LAB 4 |

---

## 🏃 Séance 5 — Consolidation & Audit

**Objectif :** Vérifier que tout est propre, corriger les N+1, finaliser la documentation.
**Durée :** 2h

| Done | # | Vérification | Commande / Action |
|:----:|:--|--------------|-------------------|
| [ ] | A-01 | Zéro `return Model::all()` ou `response()->json($model)` brut | `grep -r "response()->json(\$" app/Http/Controllers/` |
| [ ] | A-02 | Toutes les routes privées protégées par `auth:sanctum` | Tester `GET /api/blueprints` sans token → doit donner `401` |
| [ ] | A-03 | Toutes les Form Requests branchées | Zéro `$request->validate()` inline dans les controllers |
| [ ] | A-04 | Casts `array` en place sur `GeneratedPost` | `body_points` manipulable comme tableau PHP |
| [ ] | A-05 | Zéro `json_encode`/`json_decode` manuel | Grep dans `app/` |
| [ ] | A-06 | Détection N+1 avec Debugbar | `GET /api/blueprints` → onglet SQL → max 2 requêtes |
| [ ] | A-07 | Correction N+1 | Ajouter `with(['rawContent.blueprint'])` où nécessaire |
| [ ] | A-08 | Tests tous verts | `php artisan test` → 0 rouge |
| [ ] | A-09 | Worker asynchrone confirmé | `POST /repurpose` → `202` < 100ms dans Postman |
| [ ] | A-10 | Documentation Scribe finale | `sail artisan scribe:generate` → vérifier tous les endpoints |
| [ ] | A-11 | Audit commits | `git log --oneline` → min 20 commits atomiques |
| [ ] | A-12 | `.env` jamais commité | `git log --all -- .env` → résultat vide |

---

## 📦 Checklist livrables finaux

| Livrable | Critère | Statut |
|----------|---------|:------:|
| GitHub Repo | Minimum 20 commits atomiques | ⬜ |
| GitHub Repo | Branches `feature/auth`, `feature/blueprints`, `feature/content-generation`, `feature/agent-conversationnel` | ⬜ |
| GitHub Repo | Zéro commit direct sur `main` | ⬜ |
| `AGENTS.md` | Présent à la racine, à jour | ⬜ |
| `README.md` | Install complète + credentials + table des routes | ⬜ |
| Scribe | Tous les endpoints documentés avec exemples | ⬜ |
| MCD | Entités, attributs, relations avec cardinalités | ⬜ |
| MLD | Tables, types, PK, FK | ⬜ |
| Migrations | Toutes les tables via migrations — zéro SQL manuel | ⬜ |
| Seeders | Users + blueprints + raw contents avec données réalistes | ⬜ |
| Structured Output | Contrat JSON respecté 100%, Casts en place | ⬜ |
| Jobs & Queues | Génération IA toujours asynchrone — jamais synchrone | ⬜ |
| Tests Pest | `php artisan test` → tous verts | ⬜ |
| N+1 | Détecté et corrigé via Debugbar | ⬜ |
| `.env` | Jamais commité | ⬜ |

---

## 🏆 Critères de performance

### Architecture API & Sécurité — 35%

| Critère | Statut |
|---------|:------:|
| Zéro mot de passe / timestamps bruts / clés internes dans les réponses JSON | ⬜ |
| Routes protégées → `401` instantané sans token valide | ⬜ |
| Toutes les Form Requests en place — l'API ne crashe jamais avec une erreur SQL | ⬜ |
| Colonnes JSON avec Eloquent Casts — zéro `json_encode`/`json_decode` | ⬜ |
| Données scopées à l'utilisateur connecté — zéro fuite cross-user | ⬜ |

### Intégration IA & Asynchronisme — 30%

| Critère | Statut |
|---------|:------:|
| `POST /api/content/repurpose` répond < 100ms avec `202 Accepted` | ⬜ |
| Le Job valide le contrat JSON avant insertion en base | ⬜ |
| `body_points` et `suggested_hashtags` manipulés comme tableaux PHP natifs | ⬜ |
| `technical_readability_score` toujours entre 0 et 100 | ⬜ |

### Tests automatisés — 20%

| Critère | Statut |
|---------|:------:|
| Test `200` + structure JSON sur `GET /api/blueprints` | ⬜ |
| Test `401` sans token | ⬜ |
| Test `422` avec payload invalide + `assertJsonValidationErrors` | ⬜ |
| Test `202` + `Queue::assertPushed` sans appeler la vraie IA | ⬜ |

### Qualité & Livraison — 15%

| Critère | Statut |
|---------|:------:|
| Zéro N+1 — Eager Loading confirmé avec Debugbar | ⬜ |
| Minimum 20 commits atomiques segmentés par feature | ⬜ |
| Documentation Scribe complète avec exemples pre-remplis | ⬜ |
| `.env` ignoré — zéro donnée sensible commitée | ⬜ |

---

## 📚 Ressources

| Sujet | Lien |
|-------|------|
| Laravel 13 — Queues | https://laravel.com/docs/13.x/queues |
| Laravel 13 — API Resources | https://laravel.com/docs/13.x/eloquent-resources |
| Laravel 13 — Form Request Validation | https://laravel.com/docs/13.x/validation#form-request-validation |
| Laravel 13 — AI SDK | https://laravel.com/docs/13.x/ai |
| Laravel 13 — Eloquent Casting | https://laravel.com/docs/13.x/eloquent-mutators#attribute-casting |
| Laravel 13 — Testing | https://laravel.com/docs/13.x/testing |
| Pest — Documentation | https://pestphp.com/docs |
| Pest 4 + Laravel 13 — Guide complet | https://hafiz.dev/blog/laravel-pest-4-testing-complete-guide |
| Scribe — Documentation | https://scribe.knuckles.wtf/laravel/ |
| Groq — Console | https://console.groq.com |

---

*Sprint Final | enhanced-api_labs*