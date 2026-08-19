<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Page;
use App\Models\Racer;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public user can view home, schedule, and standings pages', function () {
    $this->seed();

    $this->get('/')->assertOk();
    $this->get('/events')->assertOk();
    $this->get('/results')->assertOk();
    $this->get('/standings')->assertOk();
    $this->get('/page/rules')->assertOk();
});

test('racer can register for multiple categories', function () {
    $this->seed();

    $event = Event::first();
    $categories = Category::take(2)->get();

    $response = $this->post('/register-race', [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'phone' => '555-123-4567',
        'date_of_birth' => '1995-05-15',
        'gender' => 'Womens',
        'bib_number' => '201',
        'is_season_pass' => false,
        'event_id' => $event->id,
        'category_ids' => $categories->pluck('id')->toArray(),
        'fee_type' => 'race',
    ]);

    $response->assertRedirect('/events');
    $this->assertDatabaseHas('racers', ['email' => 'jane@example.com', 'bib_number' => '201']);
    $this->assertDatabaseCount('registrations', 4); // 2 seeded + 2 new
});

test('authenticated user can manage cms pages and enter race results', function () {
    $this->seed();

    $user = User::first();
    $event = Event::first();
    $category = Category::first();
    $racer = Racer::first();

    // Authenticated CMS page update
    $page = Page::first();
    $this->actingAs($user)
        ->put("/admin/pages/{$page->id}", [
            'slug' => $page->slug,
            'title' => 'Updated Title',
            'content' => 'Updated Content',
            'is_published' => true,
        ])
        ->assertRedirect('/admin/pages');

    $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Updated Title']);

    // Authenticated timing & clothes-pin results entry
    $this->actingAs($user)
        ->post('/admin/results', [
            'event_id' => $event->id,
            'results' => [
                [
                    'racer_id' => $racer->id,
                    'category_id' => $category->id,
                    'finish_position' => 1,
                    'laps_completed' => 5,
                    'finish_time' => '42:30',
                ],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('race_results', [
        'event_id' => $event->id,
        'category_id' => $category->id,
        'racer_id' => $racer->id,
        'finish_position' => 1,
        'points_awarded' => 25,
    ]);
});

test('authenticated user can update and delete registrations with multi-year support', function () {
    $this->seed();

    $user = User::first();
    $registration = Registration::first();
    $category = Category::latest('id')->first();

    $this->actingAs($user)
        ->put("/admin/registrations/{$registration->id}", [
            'category_id' => $category->id,
            'event_id' => $registration->event_id,
            'season_year' => 2027,
            'fee_type' => 'season',
            'payment_method' => 'venmo',
            'amount_paid' => 70.00,
            'is_season_pass' => true,
            'bib_number' => '999',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('registrations', [
        'id' => $registration->id,
        'season_year' => 2027,
        'category_id' => $category->id,
        'fee_type' => 'season',
    ]);
    $this->assertDatabaseHas('racers', [
        'id' => $registration->racer_id,
        'bib_number' => '999',
    ]);

    $this->actingAs($user)
        ->delete("/admin/registrations/{$registration->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('registrations', ['id' => $registration->id]);
});

test('authenticated user can check in racers and add day-of registrations with clothespins', function () {
    $this->seed();

    $user = User::first();
    $registration = Registration::first();
    $event = Event::first();
    $category = Category::first();

    // 1. View check-in station
    $this->actingAs($user)
        ->get('/admin/check-in?event_id='.$event->id)
        ->assertOk();

    // 2. Check in pre-registered racer with clothespin #77
    $this->actingAs($user)
        ->post("/admin/check-in/{$registration->id}", [
            'clothespin_number' => '77',
            'bib_number' => '108',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('registrations', [
        'id' => $registration->id,
        'clothespin_number' => '77',
        'is_checked_in' => true,
    ]);

    // 3. Add day-of walk-up racer with clothespin #88
    $this->actingAs($user)
        ->post('/admin/check-in/day-of', [
            'event_id' => $event->id,
            'first_name' => 'Speedy',
            'last_name' => 'Gonzales',
            'email' => 'speedy@example.com',
            'bib_number' => '505',
            'clothespin_number' => '88',
            'category_id' => $category->id,
            'fee_type' => 'race',
            'payment_method' => 'cash',
            'amount_paid' => 35.00,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('racers', ['first_name' => 'Speedy', 'last_name' => 'Gonzales']);
    $this->assertDatabaseHas('registrations', [
        'clothespin_number' => '88',
        'is_checked_in' => true,
        'payment_method' => 'cash',
    ]);
});
