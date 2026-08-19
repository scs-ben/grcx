<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\Page;
use App\Models\Racer;
use App\Models\RaceResult;
use App\Models\Registration;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin Users
        User::firstOrCreate(
            ['email' => 'admin@grcx.com'],
            [
                'name' => 'GR CX Admin',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'reeseman04@gmail.com'],
            [
                'name' => 'Ben Reese',
                'password' => Hash::make('1T*S`5/1Gki4'),
            ]
        );

        // 2. Initial CMS Pages from PDF
        $pages = [
            [
                'slug' => 'rules',
                'title' => 'Rules',
                'content' => <<<'MARKDOWN'
## Season Schedule 2026
- **9/26** - Highland Park
- **10/10** - Richmond Park
- **10/25** - Manhattan Park

## Race Day Schedule
- **0800** Registration Open
- **1000** C preride/staging
- **1030** C race (30min + 1 lap)
- **1115** A preride/staging
- **1200** A race (45min + 1 lap)
- **1315** Kids race / Costume Parade! (1 lap)
- **1345** B preride/staging
- **1415** B race (45min + 1 lap, Opposite direction)
- **1515** Recognition & Podium Pictures

## Rules
- **Bike**: Race whatever bike you would like! Street cred will be awarded for traditional cyclocross bikes.
- **Race length**: After the designated time has expired the leader will complete one more lap, during this time racers will be notified that it is their last lap. After the leader finishes, all racers will be pulled off the track.
- **Wave C**: Shortened race intended for novice riders and youth. Also welcome to A/B racers as a 2nd or 3rd race.
- **Wave A**: Full length race intended for experienced adult racers.
- **Wave B**: Full length race in opposite direction intended primarily for single speed racers and youth. Also welcome to C/A racers as 2nd or 3rd race.
- **Staging & Start Order**: Each category will have their own start line to allow all racers to be staged at once. Starts happen in 30 second intervals.
- **Results & Clothes Pins**: Clothes pins will be given to each racer to be returned at the finish line in the order you finish.
MARKDOWN,
            ],
            [
                'slug' => 'pricing-and-categories',
                'title' => 'Pricing & Categories',
                'content' => <<<'MARKDOWN'
## Registration Pricing
- **Season Pass**: $70
- **Single Race**: $35
- **Race Youth (<18)**: $20
- **BC Category**: Free entry!
- **Kids Race**: Free (registration required)
- **Costume Parade**: Free (registration required)

## Categories Overview
- **Single Speed**: Category for bikes with no shifting capabilities.
- **Open**: Category for anyone.
- **Womens/girls**: Category for anyone who identifies as a woman or girl.
- **Age Categories**: Race age is according to age on date of registration.
- **BC Category**: An open category for anyone racing their second or third race, results may not be recorded. When racing in the BC category, please yield to primary racers.
- **Kids / Costume Parade**: Single lap for kids to enjoy or costume fun. Results not recorded.
MARKDOWN,
            ],
            [
                'slug' => 'policies',
                'title' => 'Policies',
                'content' => <<<'MARKDOWN'
## Weather Policy
Weather will be monitored throughout the day by staff.
- **Gusts exceeding 35mph**: activities will be paused and tents/banners lowered.
- **Lightning within 10 miles**: all activities suspended until 30 minutes after last thunder clap.
- **Temperatures above 80°F**: free water will be made available.
- **Severe Weather (tornado/hurricane/earthquake)**: guests asked to take shelter.

## Cancellation Policy
In the event of inclement weather we reserve the right to delay, shorten, or cancel. Refunds will not be available.

## Contact
- **Facebook**: Grand Rapids Cyclocross
- **Email**: GrandRapidsCyclocross@gmail.com
MARKDOWN,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }

        // 3. Events
        $events = [
            ['name' => 'Highland Park CX', 'location' => 'Highland Park', 'event_date' => '2026-09-26', 'description' => 'Race 1 of the 2026 Grand Rapids Cyclocross Series.'],
            ['name' => 'Richmond Park CX', 'location' => 'Richmond Park', 'event_date' => '2026-10-10', 'description' => 'Race 2 of the 2026 Grand Rapids Cyclocross Series.'],
            ['name' => 'Manhattan Park CX', 'location' => 'Manhattan Park', 'event_date' => '2026-10-25', 'description' => 'Season Finale & Championship at Manhattan Park.'],
        ];

        $eventModels = [];
        foreach ($events as $evt) {
            $eventModels[] = Event::updateOrCreate(['name' => $evt['name']], $evt);
        }

        // 4. Categories (12 scoring + BC + Kids + Costume)
        $categoriesData = [
            // Wave C
            ['name' => 'Girls 9-14', 'wave' => 'C', 'duration_description' => '30min+1 lap', 'start_order_seconds' => 0, 'is_scoring' => true, 'podium_order' => 1],
            ['name' => 'Open 9-14', 'wave' => 'C', 'duration_description' => '30min+1 lap', 'start_order_seconds' => 30, 'is_scoring' => true, 'podium_order' => 2],
            ['name' => 'C Womens', 'wave' => 'C', 'duration_description' => '30min+1 lap', 'start_order_seconds' => 60, 'is_scoring' => true, 'podium_order' => 3],
            ['name' => 'C Open', 'wave' => 'C', 'duration_description' => '30min+1 lap', 'start_order_seconds' => 90, 'is_scoring' => true, 'podium_order' => 4],
            ['name' => 'C BC', 'wave' => 'C', 'duration_description' => '30min+1 lap', 'start_order_seconds' => 120, 'is_scoring' => false, 'podium_order' => 99],

            // Wave A
            ['name' => 'A Womens 50+', 'wave' => 'A', 'duration_description' => '45hr+1 lap', 'start_order_seconds' => 0, 'is_scoring' => true, 'podium_order' => 9],
            ['name' => 'A Open 50+', 'wave' => 'A', 'duration_description' => '45hr+1 lap', 'start_order_seconds' => 30, 'is_scoring' => true, 'podium_order' => 10],
            ['name' => 'A Womens', 'wave' => 'A', 'duration_description' => '45hr+1 lap', 'start_order_seconds' => 60, 'is_scoring' => true, 'podium_order' => 11],
            ['name' => 'A Open', 'wave' => 'A', 'duration_description' => '45hr+1 lap', 'start_order_seconds' => 90, 'is_scoring' => true, 'podium_order' => 12],

            // Wave B
            ['name' => 'Girls 15-18', 'wave' => 'B', 'duration_description' => '45min+1 lap', 'start_order_seconds' => 0, 'is_scoring' => true, 'podium_order' => 5],
            ['name' => 'Open 15-18', 'wave' => 'B', 'duration_description' => '45min+1 lap', 'start_order_seconds' => 30, 'is_scoring' => true, 'podium_order' => 6],
            ['name' => 'Single Speed Womens', 'wave' => 'B', 'duration_description' => '45min+1 lap', 'start_order_seconds' => 60, 'is_scoring' => true, 'podium_order' => 7],
            ['name' => 'Single Speed Open', 'wave' => 'B', 'duration_description' => '45min+1 lap', 'start_order_seconds' => 90, 'is_scoring' => true, 'podium_order' => 8],
            ['name' => 'B BC', 'wave' => 'B', 'duration_description' => '45min+1 lap', 'start_order_seconds' => 120, 'is_scoring' => false, 'podium_order' => 99],

            // Kids / Costume
            ['name' => 'Kids Race', 'wave' => 'Kids', 'duration_description' => '1 lap', 'start_order_seconds' => 0, 'is_scoring' => false, 'podium_order' => 99],
            ['name' => 'Costume Parade', 'wave' => 'Kids', 'duration_description' => '1 lap', 'start_order_seconds' => 30, 'is_scoring' => false, 'podium_order' => 99],
        ];

        $categoryModels = [];
        foreach ($categoriesData as $cat) {
            $categoryModels[$cat['name']] = Category::updateOrCreate(['name' => $cat['name']], $cat);
        }

        // 5. Seed sample teams & racers
        $team1 = Team::updateOrCreate(['name' => 'Speedy Spokes CX']);
        $team2 = Team::updateOrCreate(['name' => 'Grand Rapids Mud Rollers']);

        $sampleRacers = [
            ['first_name' => 'Alex', 'last_name' => 'Vanderberg', 'email' => 'alex@example.com', 'bib_number' => '101', 'gender' => 'Open', 'team_id' => $team1->id],
            ['first_name' => 'Samantha', 'last_name' => 'Miller', 'email' => 'sam@example.com', 'bib_number' => '102', 'gender' => 'Womens', 'team_id' => $team1->id],
            ['first_name' => 'Chris', 'last_name' => 'Taylor', 'email' => 'chris@example.com', 'bib_number' => '103', 'gender' => 'Open', 'team_id' => $team2->id],
            ['first_name' => 'Jordan', 'last_name' => 'Lee', 'email' => 'jordan@example.com', 'bib_number' => '104', 'gender' => 'Open', 'team_id' => $team2->id],
        ];

        $racerModels = [];
        foreach ($sampleRacers as $r) {
            $racerModels[] = Racer::updateOrCreate(['bib_number' => $r['bib_number']], $r);
        }

        // Register Alex & Samantha in A Open & A Womens
        $reg1 = Registration::firstOrCreate([
            'racer_id' => $racerModels[0]->id,
            'event_id' => $eventModels[0]->id,
        ], [
            'fee_type' => 'race',
            'amount_paid' => 35.00,
            'status' => 'approved',
        ]);
        $reg1->categories()->syncWithoutDetaching([$categoryModels['A Open']->id]);

        $reg2 = Registration::firstOrCreate([
            'racer_id' => $racerModels[1]->id,
            'event_id' => $eventModels[0]->id,
        ], [
            'fee_type' => 'race',
            'amount_paid' => 35.00,
            'status' => 'approved',
        ]);
        $reg2->categories()->syncWithoutDetaching([$categoryModels['A Womens']->id]);

        // Seed race results for Event 1
        RaceResult::firstOrCreate([
            'event_id' => $eventModels[0]->id,
            'category_id' => $categoryModels['A Open']->id,
            'racer_id' => $racerModels[0]->id,
        ], [
            'finish_position' => 1,
            'laps_completed' => 5,
            'finish_time' => '44:12',
            'points_awarded' => RaceResult::pointsForPosition(1),
        ]);

        RaceResult::firstOrCreate([
            'event_id' => $eventModels[0]->id,
            'category_id' => $categoryModels['A Womens']->id,
            'racer_id' => $racerModels[1]->id,
        ], [
            'finish_position' => 1,
            'laps_completed' => 4,
            'finish_time' => '46:05',
            'points_awarded' => RaceResult::pointsForPosition(1),
        ]);
    }
}
