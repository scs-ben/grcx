<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\Page;
use App\Models\Racer;
use App\Models\Registration;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public user can view home, schedule, event details with start list, and standings pages', function () {
    $this->seed();

    $event = Event::first();

    $this->get('/')->assertOk();
    $this->get('/events')->assertOk();
    $this->get("/events/{$event->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Events/Show')
            ->has('startListByCategory')
            ->has('resultsByCategory')
        );
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

    $response->assertRedirect();
    $racer = Racer::where('email', 'jane@example.com')->first();
    $response->assertRedirect("/register-race/{$racer->id}/confirmation");
    $this->assertDatabaseHas('racers', ['email' => 'jane@example.com', 'bib_number' => '201']);
    $this->assertDatabaseCount('registrations', 3); // 2 seeded + 1 new registration record
    $this->assertDatabaseCount('category_registration', 4); // 2 seeded categories + 2 new categories
});

test('racer sees confirmation page with registration details after registering', function () {
    $this->seed();

    $event = Event::first();
    $categories = Category::take(2)->get();

    $this->post('/register-race', [
        'first_name' => 'Confirmation',
        'last_name' => 'Tester',
        'email' => 'confirm@example.com',
        'phone' => '555-000-1111',
        'gender' => 'Open',
        'bib_number' => '300',
        'is_season_pass' => false,
        'event_id' => $event->id,
        'category_ids' => $categories->pluck('id')->toArray(),
        'fee_type' => 'race',
    ]);

    $racer = Racer::where('email', 'confirm@example.com')->first();

    $this->get("/register-race/{$racer->id}/confirmation")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Registration/Show')
            ->has('racer')
            ->has('registrations', 1)
            ->where('racer.first_name', 'Confirmation')
            ->where('racer.last_name', 'Tester')
        );
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

test('authenticated user can create backend racer registrations with wave and category selection', function () {
    $this->seed();

    $user = User::first();
    $event = Event::first();
    $categories = Category::take(2)->get();

    $response = $this->actingAs($user)->post('/admin/registrations', [
        'racer_option' => 'new',
        'first_name' => 'Alice',
        'last_name' => 'Speedster',
        'email' => 'alice@example.com',
        'phone' => '555-999-8888',
        'bib_number' => '707',
        'category_ids' => $categories->pluck('id')->toArray(),
        'is_season_pass' => false,
        'event_id' => $event->id,
        'season_year' => 2026,
        'fee_type' => 'race',
        'payment_method' => 'cash',
        'amount_paid' => 35.00,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('racers', ['first_name' => 'Alice', 'last_name' => 'Speedster', 'bib_number' => '707']);
    $this->assertDatabaseCount('registrations', 3); // 2 seeded + 1 new registration record
});

test('publicly registered racers are pending by default and require backend admin approval to appear on start list', function () {
    $this->seed();

    $user = User::first();
    $event = Event::first();
    $category = Category::first();

    // 1. Submit public registration
    $response = $this->post('/register-race', [
        'first_name' => 'Unapproved',
        'last_name' => 'Racer',
        'email' => 'unapproved@example.com',
        'is_season_pass' => false,
        'event_id' => $event->id,
        'category_ids' => [$category->id],
        'fee_type' => 'race',
    ]);

    $response->assertRedirect();

    $reg = Registration::where('status', 'pending')->first();
    expect($reg)->not->toBeNull();

    // 2. Verify pending racer does NOT appear on public event start list
    $startListResp = $this->get("/events/{$event->id}");
    $startListResp->assertOk();
    $startListResp->assertDontSee('Unapproved Racer');

    // 3. Admin approves registration
    $this->actingAs($user)
        ->post("/admin/registrations/{$reg->id}/approve")
        ->assertRedirect();

    $this->assertDatabaseHas('registrations', [
        'id' => $reg->id,
        'status' => 'approved',
    ]);

    // 4. Now approved racer appears on public start list (when switching to start list mode)
    $approvedStartListResp = $this->get("/events/{$event->id}");
    $approvedStartListResp->assertOk();
    $this->assertDatabaseHas('racers', ['first_name' => 'Unapproved', 'last_name' => 'Racer']);
});

test('authenticated user can update and delete registrations with multi-year support', function () {
    $this->seed();

    $user = User::first();
    $registration = Registration::first();
    $category = Category::latest('id')->first();

    $this->actingAs($user)
        ->put("/admin/registrations/{$registration->id}", [
            'category_ids' => [$category->id],
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
        'fee_type' => 'season',
    ]);
    $this->assertDatabaseHas('category_registration', [
        'registration_id' => $registration->id,
        'category_id' => $category->id,
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

    // 2. Check in pre-registered racer with clothespin #77 and confirmed category
    $this->actingAs($user)
        ->post("/admin/check-in/{$registration->id}", [
            'clothespin_number' => '77',
            'bib_number' => '108',
            'category_ids' => [$category->id],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('registrations', [
        'id' => $registration->id,
        'clothespin_number' => '77',
        'is_checked_in' => true,
    ]);
    expect($registration->fresh()->categories->pluck('id')->all())->toContain($category->id);

    // 3. Add day-of walk-up racer with clothespin #88 and new team name
    $this->actingAs($user)
        ->post('/admin/check-in/day-of', [
            'event_id' => $event->id,
            'racer_option' => 'new',
            'first_name' => 'Speedy',
            'last_name' => 'Gonzales',
            'email' => 'speedy@example.com',
            'bib_number' => '505',
            'new_team_name' => 'Speed Demons CX',
            'clothespin_number' => '88',
            'category_ids' => [$category->id],
            'fee_type' => 'race',
            'payment_method' => 'cash',
            'amount_paid' => 35.00,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('teams', ['name' => 'Speed Demons CX']);
    $this->assertDatabaseHas('racers', ['first_name' => 'Speedy', 'last_name' => 'Gonzales']);
    $this->assertDatabaseHas('registrations', [
        'clothespin_number' => '88',
        'is_checked_in' => true,
        'payment_method' => 'cash',
    ]);
});

test('authenticated user can add day-of registration for an existing racer', function () {
    $this->seed();

    $user = User::first();
    $event = Event::first();
    $category = Category::first();
    $existingRacer = Racer::first();

    $this->actingAs($user)
        ->post('/admin/check-in/day-of', [
            'event_id' => $event->id,
            'racer_option' => 'existing',
            'racer_id' => $existingRacer->id,
            'clothespin_number' => '99',
            'category_ids' => [$category->id],
            'fee_type' => 'race',
            'payment_method' => 'venmo',
            'amount_paid' => 35.00,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('registrations', [
        'racer_id' => $existingRacer->id,
        'clothespin_number' => '99',
        'is_checked_in' => true,
        'payment_method' => 'venmo',
    ]);
});

test('authenticated user can enter results for a racer in multiple categories for the same event', function () {
    $this->seed();

    $user = User::first();
    $event = Event::first();
    $racer = Racer::first();
    $categories = Category::take(2)->get();

    $response = $this->actingAs($user)->post('/admin/results', [
        'event_id' => $event->id,
        'results' => [
            [
                'racer_id' => $racer->id,
                'category_id' => $categories[0]->id,
                'finish_position' => 1,
                'laps_completed' => 5,
                'finish_time' => '42:15',
            ],
            [
                'racer_id' => $racer->id,
                'category_id' => $categories[1]->id,
                'finish_position' => 1,
                'laps_completed' => 5,
                'finish_time' => '42:15',
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('race_results', [
        'event_id' => $event->id,
        'racer_id' => $racer->id,
        'category_id' => $categories[0]->id,
        'finish_position' => 1,
        'finish_time' => '42:15',
    ]);
    $this->assertDatabaseHas('race_results', [
        'event_id' => $event->id,
        'racer_id' => $racer->id,
        'category_id' => $categories[1]->id,
        'finish_position' => 1,
        'finish_time' => '42:15',
    ]);
});

test('authenticated user can create, update, and delete teams', function () {
    $this->seed();

    $user = User::first();

    // 1. Create team
    $this->actingAs($user)
        ->post('/admin/teams', [
            'name' => 'Apex Cyclocross',
        ])
        ->assertRedirect();

    $team = Team::where('name', 'Apex Cyclocross')->first();
    expect($team)->not->toBeNull();

    // 2. Update team
    $this->actingAs($user)
        ->put("/admin/teams/{$team->id}", [
            'name' => 'Apex CX Racing Team',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => 'Apex CX Racing Team']);

    // 3. Delete team
    $this->actingAs($user)
        ->delete("/admin/teams/{$team->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

test('authenticated user can reorder categories and manage podium order', function () {
    $this->seed();

    $user = User::first();
    $categories = Category::where('wave', 'C')->take(2)->get();

    $this->actingAs($user)
        ->post('/admin/categories/reorder', [
            'categories' => [
                ['id' => $categories[0]->id, 'podium_order' => 2],
                ['id' => $categories[1]->id, 'podium_order' => 1],
            ],
        ])
        ->assertRedirect();

    expect($categories[0]->fresh()->podium_order)->toBe(2);
    expect($categories[1]->fresh()->podium_order)->toBe(1);
});
