<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlueprintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'tone'           => $this->tone,
            'max_hashtag'    => $this->max_hashtag,
            'max_characters' => $this->max_characters,
            'banned_word'    => $this->banned_word,
            'extra_rules'    => $this->extra_rules,
        ];
    }
}
