<?php

namespace App\Ai\Agents;

use App\Ai\Tools\RunQueryTool;
use App\Services\SchemaContextService;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Gemini)]
#[Model('gemini-2.5-flash')]
#[MaxSteps(5)]
class SqlGeneratorAgent implements Agent, HasTools
{
    use Promptable;

    public function __construct(
        private readonly SchemaContextService $schema,
        private readonly RunQueryTool $queryTool,
    ) {}

    public function instructions(): Stringable|string
    {
        return "You are a SQL expert for a SaaS analytics platform using SQLite.\n"
            ."Given the schema below, answer the user's analytics question by:\n"
            ."1. Writing a safe SELECT query\n"
            ."2. Executing it with the run_query tool\n"
            ."3. Confirming the query executed (do NOT write any analysis or insight)\n\n"
            ."Rules:\n"
            ."- Only use SELECT statements\n"
            ."- Always call run_query before responding\n"
            ."- Your text response is discarded — focus entirely on executing the correct query\n\n"
            .$this->schema->describe();
    }

    public function tools(): iterable
    {
        return [$this->queryTool];
    }
}
