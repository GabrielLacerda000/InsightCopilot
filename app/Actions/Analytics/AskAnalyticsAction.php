<?php

namespace App\Actions\Analytics;

use App\Ai\Agents\AnalyticsInsightAgent;
use App\Ai\Agents\SqlGeneratorAgent;
use App\Ai\Tools\RunQueryTool;
use App\Services\SchemaContextService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AskAnalyticsAction
{
    public static function run(string $question, ?string $conversationId, int $userId): array
    {
        $queryTool = new RunQueryTool;

        $sqlAgent = new SqlGeneratorAgent(
            app(SchemaContextService::class),
            $queryTool,
        );

        $sqlAgent->prompt($question);

        $sql = $queryTool->lastSql ?? '';
        $data = $queryTool->lastData;

        $insightAgent = new AnalyticsInsightAgent;

        $insightPrompt = implode("\n\n", [
            "User question: {$question}",
            "Executed SQL:\n{$sql}",
            'Query results (JSON):'."\n".json_encode(array_slice($data, 0, 50), JSON_PRETTY_PRINT),
        ]);

        $insightResponse = $insightAgent->prompt($insightPrompt);

        $conversationId = self::storeExchange(
            conversationId: $conversationId,
            userId: $userId,
            question: $question,
            answer: $insightResponse->text,
            sql: $sql,
            data: $data,
        );

        return [
            'sql' => $sql,
            'data' => $data,
            'text' => $insightResponse->text,
            'conversation_id' => $conversationId,
        ];
    }

    private static function storeExchange(
        ?string $conversationId,
        int $userId,
        string $question,
        string $answer,
        string $sql,
        array $data,
    ): string {
        $now = now();

        if (! $conversationId) {
            $conversationId = (string) Str::uuid7();

            DB::table('agent_conversations')->insert([
                'id' => $conversationId,
                'user_id' => $userId,
                'title' => Str::limit($question, 50, preserveWords: true),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('agent_conversations')
                ->where('id', $conversationId)
                ->update(['updated_at' => $now]);
        }

        DB::table('agent_conversation_messages')->insert([
            [
                'id' => (string) Str::uuid7(),
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'agent' => SqlGeneratorAgent::class,
                'role' => 'user',
                'content' => $question,
                'attachments' => '[]',
                'tool_calls' => '[]',
                'tool_results' => '[]',
                'usage' => '[]',
                'meta' => '[]',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid7(),
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'agent' => AnalyticsInsightAgent::class,
                'role' => 'assistant',
                'content' => $answer,
                'attachments' => '[]',
                'tool_calls' => '[]',
                'tool_results' => '[]',
                'usage' => '[]',
                'meta' => json_encode(['sql' => $sql, 'data' => array_slice($data, 0, 500)]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        return $conversationId;
    }
}
