<script setup lang="ts">
import { Trophy, Award, Shield } from '@lucide/vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface Category {
    id: number;
    name: string;
    wave: string;
}

interface Event {
    id: number;
    name: string;
    location: string;
}

interface Team {
    id: number;
    name: string;
}

interface Racer {
    id: number;
    first_name: string;
    last_name: string;
    bib_number: string;
    team?: Team;
}

interface StandingEntry {
    racer: Racer;
    total_points: number;
    events: Record<number, { position: number; points: number } | null>;
}

interface TeamStandingEntry {
    team: Team;
    total_points: number;
    racers: string[];
}

defineProps<{
    categories: Category[];
    events: Event[];
    standingsByCategory: Record<number, StandingEntry[]>;
    teamStandings?: TeamStandingEntry[];
}>();
</script>

<template>
    <PublicLayout>
        <div class="mx-auto max-w-7xl space-y-12 px-4 py-12 sm:px-6 lg:px-8">
            <!-- Title Header -->
            <div class="border-b border-slate-200 pb-8 dark:border-slate-800">
                <div
                    class="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold tracking-wider text-amber-600 uppercase dark:text-amber-400"
                >
                    <Trophy class="h-4 w-4" /> 2026 Series Championship
                </div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white">
                    Season Leaderboard & Standings
                </h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Accrued individual category points and overall team
                    standings across all 3 Grand Rapids Cyclocross events.
                </p>
            </div>

            <!-- Team Standings Section -->
            <div
                v-if="teamStandings && teamStandings.length > 0"
                class="overflow-hidden rounded-2xl border border-amber-500/30 bg-white shadow-xl dark:bg-slate-900"
            >
                <div
                    class="flex items-center justify-between border-b border-amber-500/20 bg-amber-500/10 px-6 py-4 dark:border-slate-800 dark:bg-gradient-to-r dark:from-amber-500/20 dark:to-slate-900"
                >
                    <div class="flex items-center gap-3">
                        <Shield
                            class="h-5 w-5 text-amber-600 dark:text-amber-500"
                        />
                        <h2
                            class="text-lg font-black text-slate-900 dark:text-white"
                        >
                            Team Championship Standings
                        </h2>
                    </div>
                    <span
                        class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-600 dark:text-amber-400"
                    >
                        Overall Team Leaderboard
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-400"
                        >
                            <tr>
                                <th class="px-6 py-3">Rank</th>
                                <th class="px-6 py-3">Team Name</th>
                                <th class="px-6 py-3">Team Roster</th>
                                <th class="px-6 py-3 text-right">
                                    Total Accrued Team Points
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/50 dark:text-slate-200"
                        >
                            <tr
                                v-for="(ts, idx) in teamStandings"
                                :key="ts.team.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/30"
                            >
                                <td
                                    class="flex items-center gap-2 px-6 py-4 font-black text-slate-900 dark:text-slate-100"
                                >
                                    <span
                                        v-if="idx === 0"
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs font-black text-slate-950"
                                        >1</span
                                    >
                                    <span
                                        v-else-if="idx === 1"
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-300 text-xs font-black text-slate-950 dark:bg-slate-300"
                                        >2</span
                                    >
                                    <span
                                        v-else-if="idx === 2"
                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-700 text-xs font-black text-slate-100"
                                        >3</span
                                    >
                                    <span
                                        v-else
                                        class="ml-1.5 text-slate-500 dark:text-slate-400"
                                        >{{ idx + 1 }}</span
                                    >
                                </td>
                                <td
                                    class="px-6 py-4 text-sm font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ ts.team.name }}
                                </td>
                                <td
                                    class="px-6 py-4 font-medium text-slate-600 dark:text-slate-400"
                                >
                                    {{ ts.racers.join(', ') }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right text-lg font-black text-emerald-600 dark:text-emerald-400"
                                >
                                    {{ ts.total_points }} pts
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Categories Leaderboard Grid -->
            <div class="space-y-10">
                <div
                    v-for="cat in categories"
                    :key="cat.id"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/80"
                    >
                        <div class="flex items-center gap-3">
                            <Award class="h-5 w-5 text-amber-500" />
                            <h2
                                class="text-lg font-bold text-slate-900 dark:text-white"
                            >
                                {{ cat.name }}
                            </h2>
                        </div>
                        <span
                            class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-amber-600 shadow-xs dark:border-amber-500/20 dark:bg-slate-950 dark:text-amber-400"
                        >
                            Category Standings
                        </span>
                    </div>

                    <div
                        v-if="
                            standingsByCategory[cat.id] &&
                            standingsByCategory[cat.id].length > 0
                        "
                        class="overflow-x-auto"
                    >
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400"
                            >
                                <tr>
                                    <th class="px-6 py-3">Rank</th>
                                    <th class="px-6 py-3">Racer</th>
                                    <th class="px-6 py-3">Bib #</th>
                                    <th class="px-6 py-3">Team / Club</th>
                                    <th
                                        v-for="evt in events"
                                        :key="evt.id"
                                        class="px-4 py-3 text-center"
                                    >
                                        {{ evt.name }}
                                    </th>
                                    <th class="px-6 py-3 text-right">
                                        Total Points
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/50 dark:text-slate-200"
                            >
                                <tr
                                    v-for="(
                                        entry, index
                                    ) in standingsByCategory[cat.id]"
                                    :key="entry.racer.id"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-800/30"
                                >
                                    <td
                                        class="flex items-center gap-2 px-6 py-3.5 font-black text-slate-900 dark:text-slate-100"
                                    >
                                        <span
                                            v-if="index === 0"
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs font-black text-slate-950"
                                            >1</span
                                        >
                                        <span
                                            v-else-if="index === 1"
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-300 text-xs font-black text-slate-950"
                                            >2</span
                                        >
                                        <span
                                            v-else-if="index === 2"
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-700 text-xs font-black text-slate-100"
                                            >3</span
                                        >
                                        <span
                                            v-else
                                            class="ml-1.5 text-slate-500 dark:text-slate-400"
                                            >{{ index + 1 }}</span
                                        >
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ entry.racer.first_name }}
                                        {{ entry.racer.last_name }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-mono font-bold text-amber-600 dark:text-amber-400"
                                    >
                                        #{{ entry.racer.bib_number || '—' }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{
                                            entry.racer.team?.name ||
                                            'Independent'
                                        }}
                                    </td>
                                    <td
                                        v-for="evt in events"
                                        :key="evt.id"
                                        class="px-4 py-3.5 text-center"
                                    >
                                        <template v-if="entry.events[evt.id]">
                                            <span
                                                class="font-bold text-amber-600 dark:text-amber-400"
                                                >{{
                                                    entry.events[evt.id]?.points
                                                }}
                                                pts</span
                                            >
                                            <span
                                                class="block text-[10px] text-slate-500 dark:text-slate-400"
                                                >({{
                                                    entry.events[evt.id]
                                                        ?.position
                                                }}th place)</span
                                            >
                                        </template>
                                        <template v-else>
                                            <span
                                                class="text-slate-400 dark:text-slate-600"
                                                >—</span
                                            >
                                        </template>
                                    </td>
                                    <td
                                        class="px-6 py-3.5 text-right text-base font-black text-emerald-600 dark:text-emerald-400"
                                    >
                                        {{ entry.total_points }} pts
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else
                        class="p-6 text-center text-xs text-slate-500 italic dark:text-slate-400"
                    >
                        No category points recorded yet.
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
