<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Domains: 18 structured e-commerce domains
        Schema::create('ai_knowledge_domains', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description');
            $table->integer('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 2. Sources: Trusted documentation, standards & regulatory sources
        Schema::create('ai_knowledge_sources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('source_type')->default('reputable_industry'); // official_framework, official_provider, government_regulatory, reputable_industry, project_internal
            $table->string('url')->nullable();
            $table->decimal('authority_score', 3, 2)->default(0.90);
            $table->timestamp('last_fetched_at')->nullable();
            $table->string('content_hash')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Topics: Hierarchical domain topics & subtopics
        Schema::create('ai_knowledge_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained('ai_knowledge_domains')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('ai_knowledge_topics')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('level')->default('beginner'); // beginner, intermediate, advanced
            $table->string('verification_status')->default('unverified'); // verified, unverified, outdated, conflicting
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['domain_id', 'level']);
        });

        // 4. Knowledge Items: Granular rules, workflows, patterns & examples
        Schema::create('ai_knowledge_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('ai_knowledge_topics')->cascadeOnDelete();
            $table->foreignId('source_id')->nullable()->constrained('ai_knowledge_sources')->nullOnDelete();
            $table->string('memory_type')->default('general_ecommerce'); // general_ecommerce, store_business, technical_architecture, admin_decisions, implementation_history
            $table->string('title');
            $table->text('concept');
            $table->json('rules')->nullable();
            $table->json('workflows')->nullable();
            $table->text('implementation_patterns')->nullable();
            $table->text('examples')->nullable();
            $table->text('common_problems')->nullable();
            $table->string('source_citation')->nullable();
            $table->decimal('confidence', 3, 2)->default(0.95);
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['topic_id', 'memory_type', 'is_active']);
        });

        // 5. Knowledge Gaps: Track missing concepts & next learning steps
        Schema::create('ai_knowledge_gaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained('ai_knowledge_domains')->cascadeOnDelete();
            $table->string('query_or_topic');
            $table->text('description');
            $table->text('recommended_action');
            $table->string('status')->default('identified'); // identified, learning_in_progress, resolved
            $table->timestamp('identified_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['domain_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_knowledge_gaps');
        Schema::dropIfExists('ai_knowledge_items');
        Schema::dropIfExists('ai_knowledge_topics');
        Schema::dropIfExists('ai_knowledge_sources');
        Schema::dropIfExists('ai_knowledge_domains');
    }
};
