<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Page;
use App\Models\Racer;
use App\Models\RaceResult;
use App\Models\Registration;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    // --- Admin Dashboard ---
    public function dashboard(Request $request): Response
    {
        $events = Event::orderBy('event_date', 'asc')->get();
        $categories = Category::orderBy('podium_order', 'asc')->get();

        $selectedEventId = $request->query('event_id');

        if (! $selectedEventId && $events->isNotEmpty()) {
            $today = now()->startOfDay();
            $closestEvent = $events->sortBy(function ($e) use ($today) {
                return abs($today->diffInDays($e->event_date, false));
            })->first();
            $selectedEventId = $closestEvent ? $closestEvent->id : $events->first()->id;
        }

        $activeEvent = Event::find($selectedEventId);
        $resultsByCategory = [];

        if ($activeEvent) {
            $activeEvent->load(['results.racer.team', 'results.category']);
            $resultsByCategory = $activeEvent->results->groupBy('category_id');
        }

        return Inertia::render('Dashboard', [
            'events' => $events,
            'selectedEventId' => (int) $selectedEventId,
            'activeEvent' => $activeEvent,
            'categories' => $categories,
            'resultsByCategory' => $resultsByCategory,
        ]);
    }

    // --- Dynamic CMS Pages Management ---
    public function pages(): Response
    {
        $pages = Page::orderBy('updated_at', 'desc')->get();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    public function createPage(): Response
    {
        return Inertia::render('Admin/Pages/Edit', [
            'page' => null,
        ]);
    }

    public function storePage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_published' => ['required', 'boolean'],
        ]);

        Page::create($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function editPage(Page $page): Response
    {
        return Inertia::render('Admin/Pages/Edit', [
            'page' => $page,
        ]);
    }

    public function updatePage(Request $request, Page $page): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug,'.$page->id],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_published' => ['required', 'boolean'],
        ]);

        $page->update($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function deletePage(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    // --- Timing & Results Entry ---
    public function resultsEntry(Request $request): Response
    {
        $events = Event::orderBy('event_date', 'asc')->get();
        $categories = Category::orderBy('wave', 'asc')->orderBy('start_order_seconds', 'asc')->get();
        $racers = Racer::with(['registrations', 'team'])->orderBy('last_name', 'asc')->get();

        $selectedEventId = $request->query('event_id');
        $selectedWave = $request->query('wave', 'C');

        if (! $selectedEventId && $events->isNotEmpty()) {
            $today = now()->startOfDay();
            $closestEvent = $events->sortBy(function ($e) use ($today) {
                return abs($today->diffInDays($e->event_date, false));
            })->first();
            $selectedEventId = $closestEvent ? $closestEvent->id : $events->first()->id;
        }

        // Get category IDs belonging to selected wave
        $waveCategoryIds = Category::where('wave', $selectedWave)->pluck('id');

        // Existing results for this event & wave
        $existingResults = RaceResult::where('event_id', $selectedEventId)
            ->whereIn('category_id', $waveCategoryIds)
            ->orderBy('finish_position', 'asc')
            ->get();

        $waves = ['C', 'A', 'B', 'Kids'];

        return Inertia::render('Admin/Results/Entry', [
            'events' => $events,
            'categories' => $categories,
            'waves' => $waves,
            'racers' => $racers,
            'selectedEventId' => (int) $selectedEventId,
            'selectedWave' => $selectedWave,
            'existingResults' => $existingResults,
        ]);
    }

    public function storeResults(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'results' => ['required', 'array'],
            'results.*.racer_id' => ['required', 'exists:racers,id'],
            'results.*.category_id' => ['required', 'exists:categories,id'],
            'results.*.finish_position' => ['required', 'integer', 'min:1'],
            'results.*.laps_completed' => ['nullable', 'integer', 'min:0'],
            'results.*.finish_time' => ['nullable', 'string'],
        ]);

        foreach ($validated['results'] as $res) {
            $pts = RaceResult::pointsForPosition($res['finish_position']);

            RaceResult::updateOrCreate(
                [
                    'event_id' => $validated['event_id'],
                    'racer_id' => $res['racer_id'],
                ],
                [
                    'category_id' => $res['category_id'],
                    'finish_position' => $res['finish_position'],
                    'laps_completed' => $res['laps_completed'] ?? 1,
                    'finish_time' => $res['finish_time'] ?? null,
                    'points_awarded' => $pts,
                ]
            );
        }

        return redirect()->back()->with('success', 'Wave results saved and category points calculated!');
    }

    // --- Registrations & Racer Management ---
    public function registrations(): Response
    {
        $registrations = Registration::with(['racer.team', 'event', 'category'])
            ->orderBy('season_year', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        $events = Event::orderBy('event_date', 'asc')->get();
        $categories = Category::orderBy('name', 'asc')->get();
        $racers = Racer::with('team')->orderBy('last_name', 'asc')->get();
        $teams = Team::orderBy('name', 'asc')->get();
        $availableYears = Registration::select('season_year')->distinct()->pluck('season_year')->toArray();
        if (empty($availableYears)) {
            $availableYears = [2026];
        }

        return Inertia::render('Admin/Registrations/Index', [
            'registrations' => $registrations,
            'events' => $events,
            'categories' => $categories,
            'racers' => $racers,
            'teams' => $teams,
            'availableYears' => array_values(array_unique(array_merge([2025, 2026, 2027], $availableYears))),
        ]);
    }

    public function updateRegistration(Request $request, Registration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'event_id' => ['nullable', 'exists:events,id'],
            'season_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'fee_type' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'is_season_pass' => ['required', 'boolean'],
            'bib_number' => ['nullable', 'string', 'max:50'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'new_team_name' => ['nullable', 'string', 'max:255'],
        ]);

        $registration->update([
            'category_id' => $validated['category_id'],
            'event_id' => $validated['is_season_pass'] ? null : $validated['event_id'],
            'season_year' => $validated['season_year'],
            'fee_type' => $validated['fee_type'],
            'payment_method' => $validated['payment_method'],
            'amount_paid' => $validated['amount_paid'],
            'is_season_pass' => $validated['is_season_pass'],
        ]);

        // Resolve Team ID update
        $teamId = $validated['team_id'] ?? null;
        if (! empty($validated['new_team_name'])) {
            $team = Team::firstOrCreate(['name' => trim($validated['new_team_name'])]);
            $teamId = $team->id;
        }

        $racerData = [];
        if (array_key_exists('bib_number', $validated)) {
            $racerData['bib_number'] = $validated['bib_number'];
        }
        $racerData['team_id'] = $teamId;

        $registration->racer->update($racerData);

        return redirect()->back()->with('success', 'Registration updated successfully.');
    }

    public function deleteRegistration(Registration $registration): RedirectResponse
    {
        $registration->delete();

        return redirect()->back()->with('success', 'Registration record deleted.');
    }

    // --- Race Day Check-In & Clothespin Assignment ---
    public function checkIn(Request $request): Response
    {
        $selectedEventId = $request->query('event_id');
        $events = Event::orderBy('event_date', 'asc')->get();

        if (! $selectedEventId && $events->isNotEmpty()) {
            // Find event closest to today
            $today = now()->startOfDay();
            $closestEvent = $events->sortBy(function ($e) use ($today) {
                return abs($today->diffInDays($e->event_date, false));
            })->first();

            $selectedEventId = $closestEvent ? $closestEvent->id : $events->first()->id;
        }

        $categories = Category::orderBy('wave', 'asc')->orderBy('start_order_seconds', 'asc')->get();
        $racers = Racer::orderBy('last_name', 'asc')->get();

        // Get registrations for selected event or season passes for that year
        $event = Event::find($selectedEventId);
        $seasonYear = $event ? $event->season_year : 2026;

        $registrations = Registration::with(['racer', 'category', 'event'])
            ->where(function ($query) use ($selectedEventId, $seasonYear) {
                $query->where('event_id', $selectedEventId)
                    ->orWhere(function ($q) use ($seasonYear) {
                        $q->where('is_season_pass', true)
                            ->where('season_year', $seasonYear);
                    });
            })
            ->orderBy('is_checked_in', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $waves = ['C', 'A', 'B', 'Kids'];

        return Inertia::render('Admin/CheckIn/Index', [
            'events' => $events,
            'selectedEventId' => (int) $selectedEventId,
            'registrations' => $registrations,
            'categories' => $categories,
            'racers' => $racers,
            'waves' => $waves,
        ]);
    }

    public function processCheckIn(Request $request, Registration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'clothespin_number' => ['required', 'string', 'max:50'],
            'bib_number' => ['nullable', 'string', 'max:50'],
        ]);

        $registration->update([
            'clothespin_number' => $validated['clothespin_number'],
            'is_checked_in' => true,
        ]);

        if (! empty($validated['bib_number'])) {
            $registration->racer->update(['bib_number' => $validated['bib_number']]);
        }

        return redirect()->back()->with('success', 'Racer checked in with Clothespin #'.$validated['clothespin_number']);
    }

    public function storeDayOfRegistration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'bib_number' => ['nullable', 'string', 'max:50'],
            'clothespin_number' => ['required', 'string', 'max:50'],
            'category_id' => ['required', 'exists:categories,id'],
            'fee_type' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
        ]);

        $racer = Racer::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'bib_number' => $validated['bib_number'] ?? null,
        ]);

        $event = Event::find($validated['event_id']);

        Registration::create([
            'racer_id' => $racer->id,
            'event_id' => $validated['event_id'],
            'category_id' => $validated['category_id'],
            'season_year' => $event ? $event->season_year : 2026,
            'fee_type' => $validated['fee_type'],
            'payment_method' => $validated['payment_method'],
            'amount_paid' => $validated['amount_paid'],
            'is_season_pass' => false,
            'clothespin_number' => $validated['clothespin_number'],
            'is_checked_in' => true,
        ]);

        return redirect()->back()->with('success', 'Day-of racer created & checked in with Clothespin #'.$validated['clothespin_number']);
    }

    // --- Race Events & Categories Management ---
    public function events(): Response
    {
        $events = Event::orderBy('event_date', 'asc')->get();
        $categories = Category::orderBy('wave', 'asc')->orderBy('start_order_seconds', 'asc')->get();
        $waves = ['C', 'A', 'B', 'Kids'];

        return Inertia::render('Admin/Events/Index', [
            'events' => $events,
            'categories' => $categories,
            'waves' => $waves,
        ]);
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'season_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'description' => ['nullable', 'string'],
        ]);

        Event::create($validated);

        return redirect()->back()->with('success', 'Race event created successfully.');
    }

    public function updateEvent(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'season_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'description' => ['nullable', 'string'],
        ]);

        $event->update($validated);

        return redirect()->back()->with('success', 'Race event updated.');
    }

    public function deleteEvent(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->back()->with('success', 'Race event deleted.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'wave' => ['required', 'string', 'max:50'],
            'duration_description' => ['nullable', 'string', 'max:255'],
            'start_order_seconds' => ['required', 'integer', 'min:0'],
            'is_scoring' => ['required', 'boolean'],
            'podium_order' => ['required', 'integer'],
        ]);

        Category::create($validated);

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'wave' => ['required', 'string', 'max:50'],
            'duration_description' => ['nullable', 'string', 'max:255'],
            'start_order_seconds' => ['required', 'integer', 'min:0'],
            'is_scoring' => ['required', 'boolean'],
            'podium_order' => ['required', 'integer'],
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function deleteCategory(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->back()->with('success', 'Category deleted.');
    }

    public function users(): Response
    {
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at']);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Admin account created successfully.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Admin account updated successfully.');
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'You cannot delete your own admin account.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Admin account deleted.');
    }
}
