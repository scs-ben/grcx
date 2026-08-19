<script setup lang="ts">
import { Calendar, MapPin, Trophy } from '@lucide/vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

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

defineProps<{
    event: Event;
    categories: Category[];
    resultsByCategory: Record<number, RaceResult[]>;
}>();
</script>

<template>
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Event Banner -->
            <div
                class="mb-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
            >
                <span
                    class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-black tracking-wider text-amber-600 uppercase dark:text-amber-400"
                >
                    Official Race Results
                </span>
                <h1
                    class="mt-3 mb-2 text-3xl font-black text-slate-900 sm:text-4xl dark:text-white"
                >
                    {{ event.name }}
                </h1>
                <div
                    class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-600 dark:text-slate-400"
                >
                    <span class="flex items-center gap-1.5"
                        ><Calendar class="h-4 w-4 text-amber-500" />
                        {{ event.formatted_date || event.event_date }}</span
                    >
                    <span class="flex items-center gap-1.5"
                        ><MapPin class="h-4 w-4 text-amber-500" />
                        {{ event.location }}</span
                    >
                </div>
            </div>

            <!-- Category Results Accordion/Tabs -->
            <div class="space-y-8">
                <div
                    v-for="cat in categories"
                    :key="cat.id"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/80"
                    >
                        <div class="flex items-center gap-3">
                            <Trophy class="h-5 w-5 text-amber-500" />
                            <h2
                                class="text-lg font-bold text-slate-900 dark:text-white"
                            >
                                {{ cat.name }}
                            </h2>
                            <span
                                class="rounded-md border border-slate-200 bg-white px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                                >Wave {{ cat.wave }}</span
                            >
                        </div>
                        <span
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{ resultsByCategory[cat.id]?.length || 0 }}
                            Finishers
                        </span>
                    </div>

                    <div
                        v-if="
                            resultsByCategory[cat.id] &&
                            resultsByCategory[cat.id].length > 0
                        "
                        class="overflow-x-auto"
                    >
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400"
                            >
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
                            <tbody
                                class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/50 dark:text-slate-200"
                            >
                                <tr
                                    v-for="res in resultsByCategory[cat.id]"
                                    :key="res.id"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-800/30"
                                >
                                    <td
                                        class="flex items-center gap-2 px-6 py-3.5 font-black text-slate-900 dark:text-slate-100"
                                    >
                                        <span
                                            v-if="res.finish_position === 1"
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-black text-slate-950"
                                            >1</span
                                        >
                                        <span
                                            v-else-if="
                                                res.finish_position === 2
                                            "
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-300 text-[10px] font-black text-slate-950"
                                            >2</span
                                        >
                                        <span
                                            v-else-if="
                                                res.finish_position === 3
                                            "
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-700 text-[10px] font-black text-slate-100"
                                            >3</span
                                        >
                                        <span
                                            v-else
                                            class="ml-1 text-slate-500 dark:text-slate-400"
                                            >{{ res.finish_position }}</span
                                        >
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-mono font-bold text-amber-600 dark:text-amber-400"
                                    >
                                        #{{ res.racer?.bib_number || '—' }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-mono font-black text-amber-500"
                                    >
                                        {{
                                            res.racer?.registrations?.[0]
                                                ?.clothespin_number
                                                ? 'Pin #' +
                                                  res.racer.registrations[0]
                                                      .clothespin_number
                                                : '—'
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-medium text-slate-900 dark:text-white"
                                    >
                                        {{ res.racer?.first_name }}
                                        {{ res.racer?.last_name }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{
                                            res.racer?.team?.name ||
                                            'Independent'
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 text-slate-600 dark:text-slate-400"
                                    >
                                        {{ res.laps_completed }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-mono text-slate-700 dark:text-slate-300"
                                    >
                                        {{ res.finish_time || '—' }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-bold text-emerald-600 dark:text-emerald-400"
                                    >
                                        +{{ res.points_awarded }} pts
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else
                        class="p-6 text-center text-xs text-slate-500 italic dark:text-slate-400"
                    >
                        No official results posted for this category yet.
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
