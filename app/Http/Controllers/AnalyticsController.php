<?php

namespace App\Http\Controllers;

use App\Actions\Analytics\AskAnalyticsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'conversation_id' => ['nullable', 'string', 'max:36'],
        ]);

        $result = AskAnalyticsAction::run(
            question: $validated['question'],
            conversationId: $validated['conversation_id'] ?? null,
            userId: $request->user()->id,
        );

        return response()->json($result);
    }

    public function conversations(Request $request): JsonResponse
    {
        $conversations = DB::table('agent_conversations')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get(['id', 'title', 'updated_at']);

        return response()->json($conversations);
    }

    public function conversation(Request $request, string $conversationId): JsonResponse
    {
        $conversation = DB::table('agent_conversations')
            ->where('id', $conversationId)
            ->where('user_id', $request->user()->id)
            ->first();

        abort_if(! $conversation, 404);

        $messages = DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->get(['id', 'role', 'content', 'meta']);

        return response()->json($messages);
    }
}
