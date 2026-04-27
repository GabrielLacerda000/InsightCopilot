<?php

namespace App\Ai\Agents;

use App\Services\SchemaContextService;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Gemini)]
#[Model('gemini-2.5-flash')]
class SqlGeneratorAgent implements Agent
{
    use Promptable;

    public function __construct(private readonly SchemaContextService $schema) {}

    public function instructions(): Stringable|string
    {
        return "You are a SQL expert for a SaaS analytics platform using SQLite.\n"
            ."Given the schema below, generate a safe SELECT SQL query to answer the user's question.\n"
            ."Return ONLY the raw SQL query — no markdown, no backticks, no explanations.\n\n"
            .$this->schema->describe();
    }
}
