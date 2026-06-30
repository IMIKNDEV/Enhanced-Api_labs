<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlueprintRequest;
use App\Http\Resources\BlueprintResource;
use App\Models\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlueprintController extends Controller
{
    public function index(Request $request)
    {
        return BlueprintResource::collection(
            $request->user()->blueprints()->latest()->get()
        );
    }

    public function store(StoreBlueprintRequest $request): JsonResponse
    {
        $blueprint = $request->user()->blueprints()->create($request->validated());

        return (new BlueprintResource($blueprint))->response()->setStatusCode(201);
    }

    public function show(Blueprint $blueprint)
    {
        return new BlueprintResource($blueprint);
    }
}
