<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlueprintController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->blueprints()->latest()->get()
        );
    }

    public function show(Blueprint $blueprint): JsonResponse
    {
        return response()->json($blueprint);
    }
}
