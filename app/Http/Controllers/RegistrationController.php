<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Page;
use App\Models\Racer;
use App\Models\Registration;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function create(): Response
    {
        $events = Event::orderBy('event_date', 'asc')->get();
        $categories = Category::orderBy('wave', 'asc')->orderBy('start_order_seconds', 'asc')->get();
        $teams = Team::orderBy('name', 'asc')->get();
        $pages = Page::where('is_published', true)->select('slug', 'title')->get();

        return Inertia::render('Registration/Create', [
            'events' => $events,
            'categories' => $categories,
            'teams' => $teams,
            'pages' => $pages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'bib_number' => ['nullable', 'string', 'max:50'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'new_team_name' => ['nullable', 'string', 'max:255'],
            'is_season_pass' => ['required', 'boolean'],
            'event_id' => ['nullable', 'required_if:is_season_pass,false', 'exists:events,id'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['exists:categories,id'],
            'fee_type' => ['required', 'string'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        $paymentMethod = $validated['payment_method'] ?? 'card';

        // Resolve Team ID
        $teamId = $validated['team_id'] ?? null;
        if (! empty($validated['new_team_name'])) {
            $team = Team::firstOrCreate(['name' => trim($validated['new_team_name'])]);
            $teamId = $team->id;
        }

        // Find or create racer
        $racer = Racer::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'bib_number' => $validated['bib_number'] ?? null,
                'team_id' => $teamId,
            ]
        );

        // Assign bib if given
        if (! empty($validated['bib_number'])) {
            $racer->update(['bib_number' => $validated['bib_number']]);
        }

        // Calculate amount paid
        $amountPaid = 0.00;
        if ($validated['is_season_pass']) {
            $amountPaid = 70.00;
        } else {
            if ($validated['fee_type'] === 'youth') {
                $amountPaid = 20.00;
            } elseif ($validated['fee_type'] === 'race') {
                $amountPaid = 35.00;
            } else {
                $amountPaid = 0.00; // BC / Kids / Costume
            }
        }

        $seasonYear = 2026;
        if (! $validated['is_season_pass'] && $validated['event_id']) {
            $evt = Event::find($validated['event_id']);
            if ($evt) {
                $seasonYear = $evt->season_year ?? 2026;
            }
        }

        // Create single registration and sync categories
        $registration = Registration::create([
            'racer_id' => $racer->id,
            'event_id' => $validated['is_season_pass'] ? null : $validated['event_id'],
            'season_year' => $seasonYear,
            'fee_type' => $validated['fee_type'],
            'payment_method' => $paymentMethod,
            'amount_paid' => $amountPaid,
            'is_season_pass' => $validated['is_season_pass'],
            'status' => 'pending',
        ]);

        $registration->categories()->sync($validated['category_ids']);

        return redirect()->route('register.confirmation', $racer)->with('success', 'Registration submitted successfully! See you at the races.');
    }

    public function show(Racer $racer): Response
    {
        $racer->load('team');

        $registrations = Registration::where('racer_id', $racer->id)
            ->with(['categories', 'event'])
            ->latest()
            ->get();

        return Inertia::render('Registration/Show', [
            'racer' => $racer,
            'registrations' => $registrations,
        ]);
    }
}
