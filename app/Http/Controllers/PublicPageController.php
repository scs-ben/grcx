<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Page;
use App\Models\RaceResult;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function home(): Response
    {
        $nextEvent = Event::where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date', 'asc')
            ->first();

        $events = Event::orderBy('event_date', 'asc')->get();
        $pages = Page::where('is_published', true)->select('slug', 'title')->get();

        return Inertia::render('Home', [
            'nextEvent' => $nextEvent,
            'events' => $events,
            'pages' => $pages,
        ]);
    }

    public function showPage(string $slug): Response
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $pages = Page::where('is_published', true)->select('slug', 'title')->get();

        return Inertia::render('Page/Show', [
            'page' => $page,
            'pages' => $pages,
        ]);
    }

    public function events(): Response
    {
        $events = Event::withCount('registrations')->orderBy('event_date', 'asc')->get();
        $categories = Category::orderBy('wave', 'asc')->orderBy('start_order_seconds', 'asc')->get();
        $pages = Page::where('is_published', true)->select('slug', 'title')->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'categories' => $categories,
            'pages' => $pages,
        ]);
    }

    public function results(Request $request): Response
    {
        $events = Event::orderBy('event_date', 'asc')->get();
        $categories = Category::orderBy('podium_order', 'asc')->get();
        $pages = Page::where('is_published', true)->select('slug', 'title')->get();

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
            $activeEvent->load(['results.racer.registrations', 'results.racer.team', 'results.category']);
            $resultsByCategory = $activeEvent->results->groupBy('category_id');
        }

        return Inertia::render('Results/Index', [
            'events' => $events,
            'selectedEventId' => (int) $selectedEventId,
            'activeEvent' => $activeEvent,
            'categories' => $categories,
            'resultsByCategory' => $resultsByCategory,
            'pages' => $pages,
        ]);
    }

    public function showEvent(Event $event): Response
    {
        $event->load(['results.racer.registrations', 'results.racer.team', 'results.category']);
        $categories = Category::orderBy('podium_order', 'asc')->get();
        $pages = Page::where('is_published', true)->select('slug', 'title')->get();

        $resultsByCategory = $event->results
            ->groupBy('category_id');

        return Inertia::render('Events/Show', [
            'event' => $event,
            'categories' => $categories,
            'resultsByCategory' => $resultsByCategory,
            'pages' => $pages,
        ]);
    }

    public function standings(): Response
    {
        $categories = Category::where('is_scoring', true)
            ->orderBy('podium_order', 'asc')
            ->get();

        $events = Event::orderBy('event_date', 'asc')->get();
        $pages = Page::where('is_published', true)->select('slug', 'title')->get();

        // Calculate accrued points per racer per category
        $standingsByCategory = [];

        foreach ($categories as $category) {
            $results = RaceResult::where('category_id', $category->id)
                ->with(['racer.team'])
                ->get()
                ->groupBy('racer_id');

            $categoryStandings = [];

            foreach ($results as $racerId => $racerResults) {
                $racer = $racerResults->first()->racer;
                $totalPoints = $racerResults->sum('points_awarded');
                $eventBreakdown = [];

                foreach ($events as $evt) {
                    $evtRes = $racerResults->firstWhere('event_id', $evt->id);
                    $eventBreakdown[$evt->id] = $evtRes ? [
                        'position' => $evtRes->finish_position,
                        'points' => $evtRes->points_awarded,
                    ] : null;
                }

                $categoryStandings[] = [
                    'racer' => $racer,
                    'total_points' => $totalPoints,
                    'events' => $eventBreakdown,
                ];
            }

            // Sort by total points descending
            usort($categoryStandings, fn ($a, $b) => $b['total_points'] <=> $a['total_points']);

            $standingsByCategory[$category->id] = $categoryStandings;
        }

        // Calculate Team Standings across all events
        $allResults = RaceResult::with(['racer.team'])->get();
        $teamStandingsMap = [];

        foreach ($allResults as $res) {
            if (! $res->racer || ! $res->racer->team) {
                continue;
            }
            $team = $res->racer->team;
            if (! isset($teamStandingsMap[$team->id])) {
                $teamStandingsMap[$team->id] = [
                    'team' => $team,
                    'total_points' => 0,
                    'racer_count' => 0,
                    'racers' => [],
                ];
            }

            $teamStandingsMap[$team->id]['total_points'] += $res->points_awarded;
            if (! in_array($res->racer->full_name, $teamStandingsMap[$team->id]['racers'])) {
                $teamStandingsMap[$team->id]['racers'][] = $res->racer->full_name;
            }
        }

        $teamStandings = array_values($teamStandingsMap);
        usort($teamStandings, fn ($a, $b) => $b['total_points'] <=> $a['total_points']);

        return Inertia::render('Standings/Index', [
            'categories' => $categories,
            'events' => $events,
            'standingsByCategory' => $standingsByCategory,
            'teamStandings' => $teamStandings,
            'pages' => $pages,
        ]);
    }
}
