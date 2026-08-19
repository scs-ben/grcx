<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_registration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['registration_id', 'category_id']);
        });

        // Migrate existing category_id references if any registrations exist
        if (Schema::hasColumn('registrations', 'category_id')) {
            $existingRegistrations = DB::table('registrations')->get();

            // Group by racer_id + event_id + season_year + is_season_pass
            $grouped = $existingRegistrations->groupBy(function ($reg) {
                return $reg->racer_id.'-'.($reg->event_id ?? 'null').'-'.$reg->season_year.'-'.$reg->is_season_pass;
            });

            foreach ($grouped as $group) {
                $primary = $group->first();
                $totalAmount = $group->sum('amount_paid');
                $checkedIn = $group->contains('is_checked_in', true) || $group->contains('is_checked_in', 1);
                $clothespin = $group->pluck('clothespin_number')->filter()->first();

                // Update primary registration record
                DB::table('registrations')->where('id', $primary->id)->update([
                    'amount_paid' => $totalAmount,
                    'is_checked_in' => $checkedIn,
                    'clothespin_number' => $clothespin,
                ]);

                // Attach all categories in this group
                $catIds = $group->pluck('category_id')->filter()->unique();
                $now = now();
                foreach ($catIds as $catId) {
                    DB::table('category_registration')->insertOrIgnore([
                        'registration_id' => $primary->id,
                        'category_id' => $catId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                // Delete duplicate secondary registration rows
                $duplicateIds = $group->slice(1)->pluck('id')->all();
                if (! empty($duplicateIds)) {
                    DB::table('registrations')->whereIn('id', $duplicateIds)->delete();
                }
            }

            Schema::table('registrations', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
        });

        // Restore first category_id from pivot table
        $pivots = DB::table('category_registration')->get();
        foreach ($pivots as $pivot) {
            DB::table('registrations')->where('id', $pivot->registration_id)->update([
                'category_id' => $pivot->category_id,
            ]);
        }

        Schema::dropIfExists('category_registration');
    }
};
