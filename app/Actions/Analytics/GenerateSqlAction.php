<?php

namespace App\Actions\Analytics;

use App\Ai\Agents\SqlGeneratorAgent;
use App\Services\SchemaContextService;

class GenerateSqlAction
{
    public static function run(string $question): string
    {
        $agent = new SqlGeneratorAgent(app(SchemaContextService::class));

        return trim($agent->prompt($question)->text);
    }
}
