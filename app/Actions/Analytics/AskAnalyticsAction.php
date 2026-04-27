<?php

namespace App\Actions\Analytics;

use App\Ai\Agents\SqlGeneratorAgent;
use App\Ai\Tools\RunQueryTool;
use App\Services\SchemaContextService;

class AskAnalyticsAction
{
    public static function run(string $question): array
    {
        $queryTool = new RunQueryTool;

        $agent = new SqlGeneratorAgent(
            app(SchemaContextService::class),
            $queryTool,
        );

        $response = $agent->prompt($question);

        return [
            'sql' => $queryTool->lastSql ?? '',
            'data' => $queryTool->lastData,
            'text' => $response->text,
        ];
    }
}
