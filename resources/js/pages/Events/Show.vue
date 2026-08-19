<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Calendar, MapPin, Trophy, Award } from '@lucide/vue';

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
    registrations?: Array<{
        clothespin_number?: string;
    }>;
}

interface Category {
    id: number;
    name: string;
    wave: string;
    is_scoring: boolean;
}

interface RaceResult {
    id: number;
    finish_position: number;
    laps_completed: number;
    finish_time?: string;
    points_awarded: number;
    category_id: number;
    racer: Racer;
    category: Category;
}

interface Event {
    id: number;
    name: string;
    location: string;
    event_date: string;
    formatted_date?: string;
    description: string;
    results: RaceResult[];
}

const props = defineProps<{
    event: Event;
    categories: Category[];
    resultsByCategory: Record<number, RaceResult[]>;
}>();
</script>

<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Event Banner -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 mb-10 shadow-sm">
                <span class="text-xs font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 px-3 py-1 rounded-full">
                    Official Race Results
                </span>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-3 mb-2">{{ event.name }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-600 dark:text-slate-400">
                    <span class="flex items-center gap-1.5"><Calendar class="w-4 h-4 text-amber-500" /> {{ event.formatted_date || event.event_date }}</span>
                    <span class="flex items-center gap-1.5"><MapPin class="w-4 h-4 text-amber-500" /> {{ event.location }}</span>
                </div>
            </div>

            <!-- Category Results Accordion/Tabs -->
            <div class="space-y-8">
                <div v-for="cat in categories" :key="cat.id" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 flex items-center justify-between border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <Trophy class="w-5 h-5 text-amber-500" />
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ cat.name }}</h2>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900 px-2.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-800">Wave {{ cat.wave }}</span>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            {{ resultsByCategory[cat.id]?.length || 0 }} Finishers
                        </span>
                    </div>

                    <div v-if="resultsByCategory[cat.id] && resultsByCategory[cat.id].length > 0" class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="px-6 py-3">Place</th>
                                    <th class="px-6 py-3">Bib #</th>
                                    <th class="px-6 py-3">Pin #</th>
                                    <th class="px-6 py-3">Racer Name</th>
                                    <th class="px-6 py-3">Team / Club</th>
                                    <th class="px-6 py-3">Laps</th>
                                    <th class="px-6 py-3">Time</th>
                                    <th class="px-6 py-3">Points</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-slate-800 dark:text-slate-200">
                                <tr v-for="res in resultsByCategory[cat.id]" :key="res.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                    <td class="px-6 py-3.5 font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                        <span v-if="res.finish_position === 1" class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-black flex items-center justify-center text-[10px]">1</span>
                                        <span v-else-if="res.finish_position === 2" class="w-5 h-5 rounded-full bg-slate-300 text-slate-950 font-black flex items-center justify-center text-[10px]">2</span>
                                        <span v-else-if="res.finish_position === 3" class="w-5 h-5 rounded-full bg-amber-700 text-slate-100 font-black flex items-center justify-center text-[10px]">3</span>
                                        <span v-else class="text-slate-500 dark:text-slate-400 ml-1">{{ res.finish_position }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-amber-600 dark:text-amber-400 font-bold">#{{ res.racer?.bib_number || '—' }}</td>
                                    <td class="px-6 py-3.5 font-mono text-amber-500 font-black">
                                        {{ res.racer?.registrations?.[0]?.clothespin_number ? 'Pin #' + res.racer.registrations[0].clothespin_number : '—' }}
                                    </td>
                                    <td class="px-6 py-3.5 font-medium text-slate-900 dark:text-white">{{ res.racer?.first_name }} {{ res.racer?.last_name }}</td>
                                    <td class="px-6 py-3.5 font-semibold text-slate-700 dark:text-slate-300">
                                        {{ res.racer?.team?.name || 'Independent' }}
                                    </td>
                                    <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400">{{ res.laps_completed }}</td>
                                    <td class="px-6 py-3.5 font-mono text-slate-700 dark:text-slate-300">{{ res.finish_time || '—' }}</td>
                                    <td class="px-6 py-3.5 font-bold text-emerald-600 dark:text-emerald-400">+{{ res.points_awarded }} pts</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-6 text-center text-xs text-slate-500 dark:text-slate-400 italic">
                        No official results posted for this category yet.
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
