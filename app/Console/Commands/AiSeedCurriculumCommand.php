<?php

namespace App\Console\Commands;

use App\AI\Contracts\AiKnowledgeIngestionInterface;
use Illuminate\Console\Command;

class AiSeedCurriculumCommand extends Command
{
    protected $signature = 'ai:seed-curriculum';

    protected $description = 'Seed or synchronize the foundational 18-domain e-commerce knowledge curriculum';

    public function handle(AiKnowledgeIngestionInterface $ingestionService): int
    {
        $this->info('Starting e-commerce domain curriculum ingestion across 18 master domains...');

        $stats = $ingestionService->seedFoundationalCurriculum();

        $this->info("✓ Ingested {$stats['domains']} E-commerce Domains");
        $this->info("✓ Ingested {$stats['topics']} Topics & Subtopics");
        $this->info("✓ Ingested {$stats['items']} Knowledge Items with Rules, Workflows & Implementation Patterns");
        $this->info("✓ Ingested {$stats['sources']} Authoritative Sources");

        return Command::SUCCESS;
    }
}
