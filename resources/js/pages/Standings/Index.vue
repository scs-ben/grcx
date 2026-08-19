<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Trophy, Award, Users, Shield } from '@lucide/vue';

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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
            <!-- Title Header -->
            <div class="pb-8 border-b border-slate-200 dark:border-slate-800">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-semibold uppercase tracking-wider mb-3">
                    <Trophy class="w-4 h-4" /> 2026 Series Championship
                </div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white">Season Leaderboard & Standings</h1>
                <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Accrued individual category points and overall team standings across all 3 Grand Rapids Cyclocross events.</p>
            </div>

            <!-- Team Standings Section -->
            <div v-if="teamStandings && teamStandings.length > 0" class="bg-white dark:bg-slate-900 border border-amber-500/30 rounded-2xl overflow-hidden shadow-xl">
                <div class="bg-amber-500/10 dark:bg-gradient-to-r dark:from-amber-500/20 dark:to-slate-900 px-6 py-4 flex items-center justify-between border-b border-amber-500/20 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <Shield class="w-5 h-5 text-amber-600 dark:text-amber-500" />
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Team Championship Standings</h2>
                    </div>
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-3 py-1 rounded-full border border-amber-500/20">
                        Overall Team Leaderboard
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-3">Rank</th>
                                <th class="px-6 py-3">Team Name</th>
                                <th class="px-6 py-3">Team Roster</th>
                                <th class="px-6 py-3 text-right">Total Accrued Team Points</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-slate-800 dark:text-slate-200">
                            <tr v-for="(ts, idx) in teamStandings" :key="ts.team.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-6 py-4 font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                    <span v-if="idx === 0" class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black flex items-center justify-center text-xs">1</span>
                                    <span v-else-if="idx === 1" class="w-6 h-6 rounded-full bg-slate-300 dark:bg-slate-300 text-slate-950 font-black flex items-center justify-center text-xs">2</span>
                                    <span v-else-if="idx === 2" class="w-6 h-6 rounded-full bg-amber-700 text-slate-100 font-black flex items-center justify-center text-xs">3</span>
                                    <span v-else class="text-slate-500 dark:text-slate-400 ml-1.5">{{ idx + 1 }}</span>
                                </td>
                                <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white text-sm">
                                    {{ ts.team.name }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400 font-medium">
                                    {{ ts.racers.join(', ') }}
                                </td>
                                <td class="px-6 py-4 text-right font-black text-lg text-emerald-600 dark:text-emerald-400">
                                    {{ ts.total_points }} pts
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Categories Leaderboard Grid -->
            <div class="space-y-10">
                <div v-for="cat in categories" :key="cat.id" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 flex items-center justify-between border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <Award class="w-5 h-5 text-amber-500" />
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ cat.name }}</h2>
                        </div>
                        <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 bg-white dark:bg-slate-950 px-3 py-1 rounded-full border border-slate-200 dark:border-amber-500/20 shadow-xs">
                            Category Standings
                        </span>
                    </div>

                    <div v-if="standingsByCategory[cat.id] && standingsByCategory[cat.id].length > 0" class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-3">Rank</th>
                                    <th class="px-6 py-3">Racer</th>
                                    <th class="px-6 py-3">Bib #</th>
                                    <th class="px-6 py-3">Team / Club</th>
                                    <th v-for="evt in events" :key="evt.id" class="px-4 py-3 text-center">
                                        {{ evt.name }}
                                    </th>
                                    <th class="px-6 py-3 text-right">Total Points</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-slate-800 dark:text-slate-200">
                                <tr v-for="(entry, index) in standingsByCategory[cat.id]" :key="entry.racer.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                    <td class="px-6 py-3.5 font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                        <span v-if="index === 0" class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black flex items-center justify-center text-xs">1</span>
                                        <span v-else-if="index === 1" class="w-6 h-6 rounded-full bg-slate-300 text-slate-950 font-black flex items-center justify-center text-xs">2</span>
                                        <span v-else-if="index === 2" class="w-6 h-6 rounded-full bg-amber-700 text-slate-100 font-black flex items-center justify-center text-xs">3</span>
                                        <span v-else class="text-slate-500 dark:text-slate-400 ml-1.5">{{ index + 1 }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 font-bold text-slate-900 dark:text-white">{{ entry.racer.first_name }} {{ entry.racer.last_name }}</td>
                                    <td class="px-6 py-3.5 font-mono text-amber-600 dark:text-amber-400 font-bold">#{{ entry.racer.bib_number || '—' }}</td>
                                    <td class="px-6 py-3.5 font-semibold text-slate-700 dark:text-slate-300">
                                        {{ entry.racer.team?.name || 'Independent' }}
                                    </td>
                                    <td v-for="evt in events" :key="evt.id" class="px-4 py-3.5 text-center">
                                        <template v-if="entry.events[evt.id]">
                                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ entry.events[evt.id]?.points }} pts</span>
                                            <span class="block text-[10px] text-slate-500 dark:text-slate-400">({{ entry.events[evt.id]?.position }}th place)</span>
                                        </template>
                                        <template v-else>
                                            <span class="text-slate-400 dark:text-slate-600">—</span>
                                        </template>
                                    </td>
                                    <td class="px-6 py-3.5 text-right font-black text-base text-emerald-600 dark:text-emerald-400">
                                        {{ entry.total_points }} pts
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-6 text-center text-xs text-slate-500 dark:text-slate-400 italic">
                        No category points recorded yet.
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
