<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table: countries (Master Data Negara)
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->char('iso2', 2)->unique();
            $table->char('iso3', 3)->unique();
            $table->string('name')->index();
            $table->string('region')->index();
            $table->bigInteger('population')->nullable();
            $table->bigInteger('gdp')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });

        // 2. Table: ports (Master Data Pelabuhan/Hub Logistik)
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('unlocode', 5)->unique();
            $table->string('name')->index();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['active', 'congested', 'closed'])->default('active')->index();
            $table->timestamps();
        });

        // 3. Table: risk_scores (Tabel Polimorfik agar bisa menyimpan skor risiko untuk Negara ATAU Pelabuhan)
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->morphs('riskable'); 
            $table->decimal('overall_score', 5, 2)->index();
            $table->decimal('political_score', 5, 2)->nullable();
            $table->decimal('economic_score', 5, 2)->nullable();
            $table->decimal('environmental_score', 5, 2)->nullable();
            $table->decimal('operational_score', 5, 2)->nullable();
            $table->timestamp('calculated_at')->useCurrent()->index();
            $table->timestamps();
            $table->unique(['riskable_type', 'riskable_id', 'calculated_at'], 'risk_score_unique_idx');
        });

        // 4. Table: news_cache (Optimasi Performa API Eksternal)
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->string('query_hash', 64)->unique();
            $table->string('source_api')->index();
            $table->json('request_params')->nullable();
            $table->json('response_payload');
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });

        // 5. Table: articles (Penyimpanan Berita Tersentralisasi)
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('port_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('url')->unique();
            $table->string('source_name')->index();
            $table->timestamp('published_at')->index();
            $table->decimal('sentiment_score', 4, 2)->nullable();
            $table->enum('sentiment_label', ['positive', 'neutral', 'negative'])->nullable()->index();
            $table->timestamps();
        });

        // 6. Table: watchlists
        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('watchable');
            $table->boolean('notify_on_high_risk')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'watchable_type', 'watchable_id']);
        });

        // 7. Table: positive_words
        Schema::create('positive_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->decimal('weight', 4, 2)->default(1.00);
            $table->timestamps();
        });

        // 8. Table: negative_words
        Schema::create('negative_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->decimal('weight', 4, 2)->default(1.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negative_words');
        Schema::dropIfExists('positive_words');
        Schema::dropIfExists('watchlists');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('news_cache');
        Schema::dropIfExists('risk_scores');
        Schema::dropIfExists('ports');
        Schema::dropIfExists('countries');
    }
};
