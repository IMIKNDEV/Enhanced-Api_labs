Step 1 — Get a token

In your terminal:

    ./vendor/bin/sail artisan tinker
    >>> $user = App\Models\User::first();
    >>> $user->createToken('postman')->plainTextToken;

Copy the output (e.g. `2|abc123...`).

Step 2 — Create test data (if none exists)

Same Tinker session:

    >>> $user->blueprints()->create(['name' => 'Tech Twitter', 'tone' => 'professional', 'max_hashtag' => 3, 'max_characters' => 280]);
    >>> $user->blueprints()->create(['name' => 'Dev Humor', 'tone' => 'funny', 'max_hashtag' => 2, 'max_characters' => 200]);

Step 3 — Test in Postman

Request A — list all blueprints

| Field   | Value                                 |
|---------|---------------------------------------|
| Method  | GET                                   |
| URL     | http://localhost:8000/api/blueprints  |
| Headers | Authorization: Bearer \<paste token\> |
| Headers | Accept: application/json              |

Expected: Status 200, JSON array of blueprints.

<details>
<summary>Click to see expected response</summary>

```json
[
  {
    "id": 1,
    "user_id": 1,
    "name": "Tech Twitter",
    "tone": "professional",
    "max_hashtag": 3,
    "max_characters": 280,
    "banned_word": null,
    "extra_rules": null,
    "created_at": "2026-06-29T15:24:38.000000Z",
    "updated_at": "2026-06-29T15:24:38.000000Z"
  },
  {
    "id": 2,
    "user_id": 1,
    "name": "Dev Humor",
    "tone": "funny",
    "max_hashtag": 2,
    "max_characters": 200,
    "banned_word": null,
    "extra_rules": null,
    "created_at": "2026-06-29T15:24:38.000000Z",
    "updated_at": "2026-06-29T15:24:38.000000Z"
  }
]
```

</details>

Request B — get one blueprint

| Field   | Value                                   |
|---------|-----------------------------------------|
| Method  | GET                                     |
| URL     | http://localhost:8000/api/blueprints/1  |
| Headers | Authorization: Bearer \<paste token\>   |
| Headers | Accept: application/json                |

Expected: Status 200, single blueprint object.

<details>
<summary>Click to see expected response</summary>

```json
{
  "id": 1,
  "user_id": 1,
  "name": "Tech Twitter",
  "tone": "professional",
  "max_hashtag": 3,
  "max_characters": 280,
  "banned_word": null,
  "extra_rules": null,
  "created_at": "2026-06-29T15:24:38.000000Z",
  "updated_at": "2026-06-29T15:24:38.000000Z"
}
```

</details>
