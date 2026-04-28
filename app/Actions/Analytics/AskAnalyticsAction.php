<?php

namespace App\Actions\Analytics;

use App\Ai\Agents\AnalyticsInsightAgent;
use App\Ai\Agents\SqlGeneratorAgent;
use App\Ai\Tools\RunQueryTool;
use App\Services\SchemaContextService;

class AskAnalyticsAction
{
    public static function run(string $question): array
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

        return [
            'sql' => $sql,
            'data' => $data,
            'text' => $insightResponse->text,
        ];
    }
}
