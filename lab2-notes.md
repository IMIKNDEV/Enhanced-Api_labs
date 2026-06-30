Step 1 — Get a token

In your terminal:

    ./vendor/bin/sail artisan tinker
    >>> $user = App\Models\User::first();
    >>> $user->createToken('postman')->plainTextToken;

Copy the output (e.g. `2|abc123...`).

Step 2 — Test POST valide → 201

| Field     | Value                                     |
|-----------|-------------------------------------------|
| Method    | POST                                      |
| URL       | http://localhost:8000/api/blueprints      |
| Headers   | Authorization: Bearer \<paste token\>     |
| Headers   | Accept: application/json                  |
| Body      | raw / JSON                                |

```json
{
  "name": "Tech Twitter",
  "tone": "professional",
  "max_hashtag": 3,
  "max_characters": 280
}
```

Expected: Status **201**, JSON with only the Resource fields (no `user_id`, no timestamps).

<details>
<summary>Click to see expected response</summary>

```json
{
  "data": {
    "id": 1,
    "name": "Tech Twitter",
    "tone": "professional",
    "max_hashtag": 3,
    "max_characters": 280,
    "banned_word": null,
    "extra_rules": null
  }
}
```

</details>

Step 3 — Test POST invalide → 422

| Field     | Value                                     |
|-----------|-------------------------------------------|
| Method    | POST                                      |
| URL       | http://localhost:8000/api/blueprints      |
| Headers   | Authorization: Bearer \<paste token\>     |
| Headers   | Accept: application/json                  |
| Body      | raw / JSON                                |

```json
{
  "name": "",
  "tone": "",
  "max_hashtag": 15,
  "max_characters": 10
}
```

Expected: Status **422**, JSON with validation errors.

<details>
<summary>Click to see expected response</summary>

```json
{
  "message": "The name field is required. (and 3 more errors)",
  "errors": {
    "name": ["The name field is required."],
    "tone": ["The tone field is required."],
    "max_hashtag": ["The max hasthag field must not be greater than 10."],
    "max_characters": ["The max characters field must be at least 50."]
  }
}
```

</details>

Step 4 — Test GET list → Resource format (no internal fields)

| Field   | Value                                 |
|---------|---------------------------------------|
| Method  | GET                                   |
| URL     | http://localhost:8000/api/blueprints  |
| Headers | Authorization: Bearer \<paste token\> |
| Headers | Accept: application/json              |

Expected: Status **200**, each item wrapped in `data` array with only Resource fields.

<details>
<summary>Click to see expected response</summary>

```json
{
  "data": [
    {
      "id": 1,
      "name": "Tech Twitter",
      "tone": "professional",
      "max_hashtag": 3,
      "max_characters": 280,
      "banned_word": null,
      "extra_rules": null
    }
  ]
}
```

</details>

---

**Question : quel champ `Model::all()` aurait exposé que la Resource bloque ?**

**Champs exposés par `Model::all()` / `response()->json()` que la Resource bloque :**
- `user_id` — FK interne, le client n'a pas à connaître l'ID du propriétaire
- `created_at` / `updated_at` — métadonnées de base de données inutiles pour le consommateur API

**Pourquoi la Resource est plus sûre :**
- `toArray()` fait un **whitelisting explicite** des champs autorisés
- Aucun risque qu'une nouvelle colonne (`password`, `email`, etc.) fuie accidentellement si on oublie de l'ajouter aux `$hidden`
- Le payload JSON est plus léger et le contrat API est stable, indépendant du schéma DB
