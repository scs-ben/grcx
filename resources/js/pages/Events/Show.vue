<script setup lang="ts">
import { Calendar, MapPin, Trophy, Users, CheckCircle2 } from '@lucide/vue';
import { ref, computed } from 'vue';
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

interface RaceRegistration {
    id: number;
    racer_id: number;
    clothespin_number?: string;
    is_checked_in: boolean;
    is_season_pass: boolean;
    racer: Racer;
    categories?: Category[];
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
    startListByCategory: Record<number, RaceRegistration[]>;
}>();

const hasResults = computed(() => {
    return (
        Object.keys(props.resultsByCategory).length > 0 &&
        Object.values(props.resultsByCategory).some((res) => res.length > 0)
    );
});

// Default to 'results' if results exist, otherwise default to 'startlist'
const activeTab = ref<'startlist' | 'results'>(
    hasResults.value ? 'results' : 'startlist',
);
</script>

<template>
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Event Banner -->
            <div
                class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                >
                    <div>
                        <span
                            class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-black tracking-wider text-amber-600 uppercase dark:text-amber-400"
                        >
                            {{
                                activeTab === 'results'
                                    ? 'Official Race Results'
                                    : 'Pre-Race Start List'
                            }}
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
                                {{
                                    event.formatted_date || event.event_date
                                }}</span
                            >
                            <span class="flex items-center gap-1.5"
                                ><MapPin class="h-4 w-4 text-amber-500" />
                                {{ event.location }}</span
                            >
                        </div>
                    </div>

                    <!-- Mode Switcher Tabs -->
                    <div
                        class="flex items-center self-start rounded-xl border border-slate-200 bg-slate-100 p-1 sm:self-center dark:border-slate-700 dark:bg-slate-800/80"
                    >
                        <button
                            @click="activeTab = 'startlist'"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all"
                            :class="
                                activeTab === 'startlist'
                                    ? 'bg-amber-500 text-slate-950 shadow-sm'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                            "
                        >
                            <Users class="h-4 w-4" />
                            <span>Pre-Race Start List</span>
                        </button>
                        <button
                            @click="activeTab = 'results'"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all"
                            :class="
                                activeTab === 'results'
                                    ? 'bg-amber-500 text-slate-950 shadow-sm'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                            "
                        >
                            <Trophy class="h-4 w-4" />
                            <span>Official Results</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Start List Section -->
            <div v-if="activeTab === 'startlist'" class="space-y-8">
                <div
                    v-for="cat in categories"
                    :key="'startlist-' + cat.id"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/80"
                    >
                        <div class="flex items-center gap-3">
                            <Users class="h-5 w-5 text-amber-500" />
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
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                        >
                            {{ startListByCategory[cat.id]?.length || 0 }}
                            Registered Racers
                        </span>
                    </div>

                    <div
                        v-if="
                            startListByCategory[cat.id] &&
                            startListByCategory[cat.id].length > 0
                        "
                        class="overflow-x-auto"
                    >
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400"
                            >
                                <tr>
                                    <th class="px-6 py-3">Racer Name</th>
                                    <th class="px-6 py-3">Bib #</th>
                                    <th class="px-6 py-3">Team / Club</th>
                                    <th class="px-6 py-3">Clothespin Tag #</th>
                                    <th class="px-6 py-3">Pass Type</th>
                                    <th class="px-6 py-3 text-right">
                                        Check-In Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/50 dark:text-slate-200"
                            >
                                <tr
                                    v-for="reg in startListByCategory[cat.id]"
                                    :key="reg.id"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-800/30"
                                >
                                    <td
                                        class="px-6 py-3.5 font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ reg.racer?.first_name }}
                                        {{ reg.racer?.last_name }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-mono font-bold text-amber-600 dark:text-amber-400"
                                    >
                                        #{{ reg.racer?.bib_number || '—' }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-semibold text-slate-700 dark:text-slate-300"
                                    >
                                        {{
                                            reg.racer?.team?.name ||
                                            'Independent'
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-mono font-black text-amber-500"
                                    >
                                        {{
                                            reg.clothespin_number
                                                ? 'Pin #' +
                                                  reg.clothespin_number
                                                : '—'
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-medium text-slate-600 dark:text-slate-400"
                                    >
                                        <span
                                            v-if="reg.is_season_pass"
                                            class="rounded border border-purple-500/20 bg-purple-500/10 px-2 py-0.5 text-[11px] font-bold text-purple-600 dark:text-purple-400"
                                        >
                                            Season Pass
                                        </span>
                                        <span
                                            v-else
                                            class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            Single Race
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-3.5 text-right font-semibold"
                                    >
                                        <span
                                            v-if="reg.is_checked_in"
                                            class="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[11px] text-emerald-600 dark:text-emerald-400"
                                        >
                                            <CheckCircle2 class="h-3.5 w-3.5" />
                                            Checked In
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                                        >
                                            Registered
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else
                        class="p-6 text-center text-xs text-slate-500 italic dark:text-slate-400"
                    >
                        No racers pre-registered for this category yet.
                    </div>
                </div>
            </div>

            <!-- Category Results Accordion/Tabs -->
            <div v-else-if="activeTab === 'results'" class="space-y-8">
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
