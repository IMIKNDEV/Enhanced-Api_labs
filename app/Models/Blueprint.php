<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blueprint extends Model
{
    /** @use HasFactory<\Database\Factories\BlueprintFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'tone', 'max_hashtag', 'max_characters', 'banned_word', 'extra_rules'];

    protected function casts(): array
    {
        return [
            'max_hashtag' => 'integer',
            'max_characters' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
