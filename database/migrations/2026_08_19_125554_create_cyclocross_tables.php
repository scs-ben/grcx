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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('content');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->date('event_date');
            $table->integer('season_year')->default(2026);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('wave'); // C, A, B, Kids
            $table->string('duration_description')->nullable(); // e.g. 30min+1 lap
            $table->integer('start_order_seconds')->default(0);
            $table->boolean('is_scoring')->default(true);
            $table->integer('podium_order')->default(0);
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('racers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('bib_number')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('racer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete(); // NULL if season pass
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->integer('season_year')->default(2026);
            $table->string('fee_type'); // season, race, youth, bc, kids, costume
            $table->string('payment_method')->default('cash'); // cash, venmo, check, card, free
            $table->decimal('amount_paid', 8, 2)->default(0.00);
            $table->boolean('is_season_pass')->default(false);
            $table->string('clothespin_number')->nullable();
            $table->boolean('is_checked_in')->default(false);
            $table->timestamps();
        });

        Schema::create('race_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('racer_id')->constrained()->cascadeOnDelete();
            $table->integer('finish_position');
            $table->integer('laps_completed')->default(1);
            $table->string('finish_time')->nullable();
            $table->integer('points_awarded')->default(0);
            $table->timestamps();

            $table->unique(['event_id', 'category_id', 'racer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_results');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('racers');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('events');
        Schema::dropIfExists('pages');
    }
};
