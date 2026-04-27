<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class RunQueryTool implements Tool
{
    private const FORBIDDEN_PATTERN = '/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|REPLACE|ATTACH|DETACH)\b/i';

    private const ROW_LIMIT = 500;

    public ?string $lastSql = null;

    public array $lastData = [];

    public function description(): Stringable|string
    {
        return 'Executes a read-only SQL SELECT query against the SaaS analytics SQLite database and returns the results as a JSON array of rows.';
    }

    public function handle(Request $request): Stringable|string
    {
        $sql = $request['sql'];

        if (preg_match(self::FORBIDDEN_PATTERN, $sql)) {
            return json_encode(['error' => 'Forbidden SQL statement']);
        }

        if (! preg_match('/\bLIMIT\b/i', $sql)) {
            $sql = rtrim($sql, '; ').' LIMIT '.self::ROW_LIMIT;
        }

        Log::info('analytics.query', ['sql' => $sql]);

        $rows = DB::select($sql);
        $data = array_map(fn ($row) => (array) $row, $rows);

        $this->lastSql = $sql;
        $this->lastData = $data;

        return json_encode($data);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'sql' => $schema->string()->required(),
        ];
    }
}
