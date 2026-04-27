<?php

namespace App\Actions\Analytics;

class AskAnalyticsAction
{
    public static function run(string $question): array
    {
        $sql = GenerateSqlAction::run($question);
        $data = RunQueryAction::run($sql);

        return compact('sql', 'data');
    }
}
