<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Racer;
use App\Models\RaceResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('race result helper converts between time string and seconds correctly', function () {
    expect(RaceResult::timeToSeconds('31:45'))->toBe(1905)
        ->and(RaceResult::timeToSeconds('1:16:05'))->toBe(4565)
        ->and(RaceResult::timeToSeconds(null))->toBeNull()
        ->and(RaceResult::timeToSeconds('—'))->toBeNull()
        ->and(RaceResult::secondsToTime(1905))->toBe('31:45')
        ->and(RaceResult::secondsToTime(4565))->toBe('1:16:05')
        ->and(RaceResult::secondsToTime(null))->toBeNull();
});

test('calculateBcCombinedForEvent correctly combines Wave C and Wave B times and ranks racers', function () {
    $event = Event::create([
        'name' => 'Highland CX',
        'location' => 'Highland Park',
        'event_date' => '2026-10-10',
        'season_year' => 2026,
    ]);

    $catWaveC = Category::create([
        'name' => 'C BC',
        'wave' => 'C',
        'is_scoring' => true,
        'podium_order' => 1,
    ]);

    $catWaveB = Category::create([
        'name' => 'B BC',
        'wave' => 'B',
        'is_scoring' => true,
        'podium_order' => 2,
    ]);

    // Racer 1: 4 laps in C (31:00) + 6 laps in B (44:00) = 10 laps, 1:15:00 (Winner)
    $racer1 = Racer::create([
        'first_name' => 'Speedy',
        'last_name' => 'Racer',
        'email' => 'speedy@example.com',
        'phone' => '555-1111',
        'date_of_birth' => '1990-01-01',
        'gender' => 'Open',
        'bib_number' => '101',
    ]);
    RaceResult::create([
        'event_id' => $event->id,
        'category_id' => $catWaveC->id,
        'racer_id' => $racer1->id,
        'finish_position' => 1,
        'laps_completed' => 4,
        'finish_time' => '31:00',
        'points_awarded' => 25,
    ]);
    RaceResult::create([
        'event_id' => $event->id,
        'category_id' => $catWaveB->id,
        'racer_id' => $racer1->id,
        'finish_position' => 2,
        'laps_completed' => 6,
        'finish_time' => '44:00',
        'points_awarded' => 22,
    ]);

    // Racer 2: 4 laps in C (32:30) + 6 laps in B (45:00) = 10 laps, 1:17:30 (2nd Place)
    $racer2 = Racer::create([
        'first_name' => 'Steady',
        'last_name' => 'Racer',
        'email' => 'steady@example.com',
        'phone' => '555-2222',
        'date_of_birth' => '1992-02-02',
        'gender' => 'Open',
        'bib_number' => '102',
    ]);
    RaceResult::create([
        'event_id' => $event->id,
        'category_id' => $catWaveC->id,
        'racer_id' => $racer2->id,
        'finish_position' => 2,
        'laps_completed' => 4,
        'finish_time' => '32:30',
        'points_awarded' => 22,
    ]);
    RaceResult::create([
        'event_id' => $event->id,
        'category_id' => $catWaveB->id,
        'racer_id' => $racer2->id,
        'finish_position' => 3,
        'laps_completed' => 6,
        'finish_time' => '45:00',
        'points_awarded' => 20,
    ]);

    // Racer 3: only completed C race so far (4 laps, 30:00)
    $racer3 = Racer::create([
        'first_name' => 'Partial',
        'last_name' => 'Racer',
        'email' => 'partial@example.com',
        'phone' => '555-3333',
        'date_of_birth' => '1994-03-03',
        'gender' => 'Open',
        'bib_number' => '103',
    ]);
    RaceResult::create([
        'event_id' => $event->id,
        'category_id' => $catWaveC->id,
        'racer_id' => $racer3->id,
        'finish_position' => 3,
        'laps_completed' => 4,
        'finish_time' => '30:00',
        'points_awarded' => 20,
    ]);

    $bcResults = RaceResult::calculateBcCombinedForEvent($event->id);

    expect($bcResults)->toHaveCount(3)
        ->and($bcResults[0]['racer']['id'])->toBe($racer1->id)
        ->and($bcResults[0]['finish_position'])->toBe(1)
        ->and($bcResults[0]['total_laps'])->toBe(10)
        ->and($bcResults[0]['combined_time'])->toBe('1:15:00')
        ->and($bcResults[0]['is_complete'])->toBeTrue()
        ->and($bcResults[1]['racer']['id'])->toBe($racer2->id)
        ->and($bcResults[1]['finish_position'])->toBe(2)
        ->and($bcResults[1]['total_laps'])->toBe(10)
        ->and($bcResults[1]['combined_time'])->toBe('1:17:30')
        ->and($bcResults[1]['is_complete'])->toBeTrue()
        ->and($bcResults[2]['racer']['id'])->toBe($racer3->id)
        ->and($bcResults[2]['finish_position'])->toBe(3)
        ->and($bcResults[2]['is_complete'])->toBeFalse();
});

test('dashboard and results endpoints pass bcCombinedResults prop', function () {
    $this->seed();
    $event = Event::first();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard', ['event_id' => $event->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('bcCombinedResults')
        );

    $this->get("/results?event_id={$event->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Results/Index')
            ->has('bcCombinedResults')
        );

    $this->get("/events/{$event->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/Show')
            ->has('bcCombinedResults')
        );
});
