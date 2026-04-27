<?php

namespace App\Http\Controllers;

use App\Actions\Analytics\AskAnalyticsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Analytics/Index');
    }

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:500'],
        ]);

        $result = AskAnalyticsAction::run($validated['question']);

        return response()->json($result);
    }
}
