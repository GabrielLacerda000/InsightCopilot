<?php

namespace App\Actions\Analytics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RunQueryAction
{
    private const FORBIDDEN_PATTERN = '/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|REPLACE|ATTACH|DETACH)\b/i';

    private const ROW_LIMIT = 500;

    public static function run(string $sql): array
    {
        if (preg_match(self::FORBIDDEN_PATTERN, $sql)) {
            throw new \RuntimeException('Forbidden SQL statement');
        }

        if (! preg_match('/\bLIMIT\b/i', $sql)) {
            $sql = rtrim($sql, '; ').' LIMIT '.self::ROW_LIMIT;
        }

        Log::info('analytics.query', ['sql' => $sql]);

        $rows = DB::select($sql);

        return array_map(fn ($row) => (array) $row, $rows);
    }
}
