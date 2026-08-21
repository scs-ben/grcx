<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Calendar, MapPin, Trophy, Search, Filter } from '@lucide/vue';
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

interface BcCombinedResult {
    racer: Racer;
    wave_c_time: string | null;
    wave_c_laps: number;
    wave_c_position: number | null;
    wave_c_category: string | null;
    wave_b_time: string | null;
    wave_b_laps: number;
    wave_b_position: number | null;
    wave_b_category: string | null;
    total_laps: number;
    total_seconds: number | null;
    combined_time: string | null;
    is_complete: boolean;
    finish_position: number;
}

interface Event {
    id: number;
    name: string;
    location: string;
    event_date: string;
    formatted_date?: string;
    description: string;
}

const props = defineProps<{
    events: Event[];
    selectedEventId: number;
    activeEvent?: Event;
    categories: Category[];
    resultsByCategory: Record<number, RaceResult[]>;
    bcCombinedResults?: BcCombinedResult[];
}>();

const selectedEvent = ref(props.selectedEventId);
const racerSearch = ref('');
const selectedWave = ref<string>('ALL');
const selectedCategoryFilter = ref<number | 'ALL'>('ALL');

const wavesList = ['ALL', 'C', 'A', 'B', 'BC', 'Kids'];

const onEventChange = () => {
    router.get(
        '/results',
        { event_id: selectedEvent.value },
        { preserveState: false },
    );
};

// Filter categories available under selected wave filter
const availableCategoryFilters = computed(() => {
    if (selectedWave.value === 'ALL') {
        return props.categories;
    }

    return props.categories.filter((c) => c.wave === selectedWave.value);
});

// Group all race results by Wave instead of category alone
const waveResults = computed(() => {
    // Flatten all results across all categories
    const allResults: RaceResult[] = [];

    for (const catId in props.resultsByCategory) {
        allResults.push(...props.resultsByCategory[catId]);
    }

    // Apply Racer Search Filter
    const searchQ = racerSearch.value.toLowerCase().trim();
    const searchedResults = allResults.filter((res) => {
        if (!searchQ) {
            return true;
        }

        const name =
            `${res.racer?.first_name} ${res.racer?.last_name}`.toLowerCase();
        const bib = res.racer?.bib_number || '';
        const pin = res.racer?.registrations?.[0]?.clothespin_number || '';
        const team = res.racer?.team?.name?.toLowerCase() || '';
        const catName = res.category?.name?.toLowerCase() || '';

        return (
            name.includes(searchQ) ||
            bib.includes(searchQ) ||
            pin.includes(searchQ) ||
            team.includes(searchQ) ||
            catName.includes(searchQ)
        );
    });

    // Apply Category Filter if specific category selected
    const filteredResults = searchedResults.filter((res) => {
        if (selectedCategoryFilter.value === 'ALL') {
            return true;
        }

        return res.category_id === selectedCategoryFilter.value;
    });

    // Group by wave
    const waveGroups: Record<string, RaceResult[]> = {
        C: [],
        A: [],
        B: [],
        Kids: [],
    };

    filteredResults.forEach((res) => {
        const wave = res.category?.wave || 'C';

        if (!waveGroups[wave]) {
            waveGroups[wave] = [];
        }

        waveGroups[wave].push(res);
    });

    // Sort finish positions within each wave
    for (const w in waveGroups) {
        waveGroups[w].sort((a, b) => a.finish_position - b.finish_position);
    }

    // Filter displayed waves if a specific wave is selected
    if (selectedWave.value !== 'ALL') {
        const singleWaveMap: Record<string, RaceResult[]> = {};
        singleWaveMap[selectedWave.value] =
            waveGroups[selectedWave.value] || [];

        return singleWaveMap;
    }

    return waveGroups;
});
</script>

<template>
    <PublicLayout>
        <div class="mx-auto max-w-7xl space-y-10 px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header Banner -->
            <div
                class="flex flex-col justify-between gap-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 md:flex-row md:items-center dark:border-slate-800 dark:bg-slate-900"
            >
                <div>
                    <span
                        class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-black tracking-wider text-amber-600 uppercase dark:text-amber-400"
                    >
                        Live Official Race Results
                    </span>
                    <h1
                        class="mt-3 mb-2 text-3xl font-black text-slate-900 sm:text-4xl dark:text-white"
                    >
                        {{
                            activeEvent?.name ||
                            'Grand Rapids Cyclocross Results'
                        }}
                    </h1>
                    <div
                        class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-600 dark:text-slate-400"
                    >
                        <span class="flex items-center gap-1.5"
                            ><Calendar class="h-4 w-4 text-amber-500" />
                            {{
                                activeEvent?.formatted_date ||
                                activeEvent?.event_date
                            }}</span
                        >
                        <span class="flex items-center gap-1.5"
                            ><MapPin class="h-4 w-4 text-amber-500" />
                            {{ activeEvent?.location }}</span
                        >
                    </div>
                </div>

                <!-- Event Dropdown Switcher -->
                <div class="flex flex-col gap-1.5 self-start md:self-auto">
                    <label
                        class="text-xs font-bold text-slate-700 dark:text-slate-300"
                        >Select Race Event:</label
                    >
                    <select
                        v-model="selectedEvent"
                        @change="onEventChange"
                        class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                    >
                        <option
                            v-for="evt in events"
                            :key="evt.id"
                            :value="evt.id"
                        >
                            {{ evt.name }} ({{
                                evt.formatted_date || evt.event_date
                            }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Filters Bar: Wave Tabs, Category Filter & Search -->
            <div
                class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
                >
                    <!-- Wave Selector Tabs -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="mr-1 hidden text-xs font-bold text-slate-500 sm:inline dark:text-slate-400"
                            >Waves:</span
                        >
                        <button
                            v-for="w in wavesList"
                            :key="w"
                            type="button"
                            @click="
                                selectedWave = w;
                                selectedCategoryFilter = 'ALL';
                            "
                            class="rounded-xl px-4 py-2 text-xs font-black transition-all"
                            :class="
                                selectedWave === w
                                    ? 'bg-amber-500 text-slate-950 shadow-sm'
                                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                        >
                            {{ w === 'ALL' ? 'All Waves' : 'Wave ' + w }}
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-72">
                        <Search
                            class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                        />
                        <input
                            v-model="racerSearch"
                            type="text"
                            placeholder="Search racer, bib #, pin #..."
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2 pr-4 pl-10 text-xs text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Category Filter Dropdown -->
                <div
                    class="flex items-center gap-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                >
                    <Filter class="h-4 w-4 shrink-0 text-amber-500" />
                    <label
                        class="text-xs font-bold whitespace-nowrap text-slate-700 dark:text-slate-300"
                        >Filter by Category:</label
                    >
                    <select
                        v-model="selectedCategoryFilter"
                        class="max-w-xs rounded-xl border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-900 shadow-xs focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                    >
                        <option value="ALL">
                            All Categories
                            {{
                                selectedWave !== 'ALL'
                                    ? 'in Wave ' + selectedWave
                                    : ''
                            }}
                        </option>
                        <option
                            v-for="cat in availableCategoryFilters"
                            :key="cat.id"
                            :value="cat.id"
                        >
                            {{ cat.name }} (Wave {{ cat.wave }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Wave Results Tables -->
            <div class="space-y-10">
                <div
                    v-for="(resultsList, waveName) in waveResults"
                    :key="waveName"
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/80"
                    >
                        <div class="flex items-center gap-3">
                            <Trophy class="h-5 w-5 text-amber-500" />
                            <h2
                                class="text-lg font-black text-slate-900 dark:text-white"
                            >
                                Wave {{ waveName }} Results
                            </h2>
                        </div>
                        <span
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{ resultsList.length }} Finishers
                        </span>
                    </div>

                    <div v-if="resultsList.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-400"
                            >
                                <tr>
                                    <th class="px-6 py-3">Finish Pos</th>
                                    <th class="px-6 py-3">Category</th>
                                    <th class="px-6 py-3">Bib #</th>
                                    <th class="px-6 py-3">Pin #</th>
                                    <th class="px-6 py-3">Racer Name</th>
                                    <th class="px-6 py-3">Team / Club</th>
                                    <th class="px-6 py-3">Laps</th>
                                    <th class="px-6 py-3">Time</th>
                                    <th class="px-6 py-3 text-right">Points</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/50 dark:text-slate-200"
                            >
                                <tr
                                    v-for="res in resultsList"
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
                                        class="px-6 py-3.5 font-bold text-amber-600 dark:text-amber-400"
                                    >
                                        {{ res.category?.name }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-mono font-bold text-slate-700 dark:text-slate-300"
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
                                        class="px-6 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400"
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
                        No Wave {{ waveName }} results recorded for this event.
                    </div>
                </div>

                <!-- BC Combined Omnium Table (Wave B + Wave C Combined) -->
                <div
                    v-if="selectedWave === 'ALL' || selectedWave === 'BC'"
                    class="overflow-hidden rounded-2xl border-2 border-amber-500/30 bg-white shadow-xs dark:border-amber-500/20 dark:bg-slate-900"
                >
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-amber-500/20 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent px-6 py-4 dark:border-amber-500/10"
                    >
                        <div class="flex items-center gap-3">
                            <Trophy
                                class="h-5 w-5 text-amber-600 dark:text-amber-400"
                            />
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2
                                        class="text-lg font-bold text-slate-900 dark:text-white"
                                    >
                                        BC Combined Omnium Standings
                                    </h2>
                                    <span
                                        class="rounded-full bg-amber-500 px-2.5 py-0.5 text-[10px] font-black text-slate-950 uppercase"
                                        >Combined B & C Times</span
                                    >
                                </div>
                                <p
                                    class="text-xs text-slate-600 dark:text-slate-400"
                                >
                                    Combined elapsed times & laps from Wave C
                                    (30m) and Wave B (45m) heats.
                                </p>
                            </div>
                        </div>
                        <span
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{ (bcCombinedResults || []).length }} BC Racers
                        </span>
                    </div>

                    <div
                        v-if="(bcCombinedResults || []).length > 0"
                        class="overflow-x-auto"
                    >
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-200 bg-amber-500/5 font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300"
                            >
                                <tr>
                                    <th class="px-6 py-3">Combined Pos</th>
                                    <th class="px-6 py-3">Bib #</th>
                                    <th class="px-6 py-3">Racer Name</th>
                                    <th class="px-6 py-3">Team / Club</th>
                                    <th class="px-6 py-3">Wave C (30m)</th>
                                    <th class="px-6 py-3">Wave B (45m)</th>
                                    <th class="px-6 py-3">Total Laps</th>
                                    <th class="px-6 py-3">Combined Time</th>
                                    <th class="px-6 py-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/60 dark:text-slate-200"
                            >
                                <tr
                                    v-for="bc in bcCombinedResults"
                                    :key="bc.racer.id"
                                    class="hover:bg-amber-500/5 dark:hover:bg-slate-800/40"
                                >
                                    <td
                                        class="flex items-center gap-2 px-6 py-3.5 font-black text-slate-900 dark:text-slate-100"
                                    >
                                        <span
                                            v-if="bc.finish_position === 1"
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-[10px] font-black text-slate-950"
                                            >1</span
                                        >
                                        <span
                                            v-else-if="bc.finish_position === 2"
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-300 text-[10px] font-black text-slate-950"
                                            >2</span
                                        >
                                        <span
                                            v-else-if="bc.finish_position === 3"
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-700 text-[10px] font-black text-slate-100"
                                            >3</span
                                        >
                                        <span
                                            v-else
                                            class="ml-1 text-slate-500 dark:text-slate-400"
                                            >{{ bc.finish_position }}</span
                                        >
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-mono font-bold text-slate-700 dark:text-slate-300"
                                    >
                                        #{{ bc.racer?.bib_number || '—' }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ bc.racer?.first_name }}
                                        {{ bc.racer?.last_name }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-medium text-slate-600 dark:text-slate-400"
                                    >
                                        {{
                                            bc.racer?.team?.name ||
                                            'Independent'
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 text-slate-700 dark:text-slate-300"
                                    >
                                        <span
                                            v-if="bc.wave_c_time"
                                            class="font-mono font-semibold"
                                            >{{ bc.wave_c_time }}</span
                                        >
                                        <span
                                            v-else
                                            class="text-slate-400 italic"
                                            >No time</span
                                        >
                                        <span
                                            class="ml-1 text-[11px] text-slate-500"
                                            >({{ bc.wave_c_laps }} laps)</span
                                        >
                                    </td>
                                    <td
                                        class="px-6 py-3.5 text-slate-700 dark:text-slate-300"
                                    >
                                        <span
                                            v-if="bc.wave_b_time"
                                            class="font-mono font-semibold"
                                            >{{ bc.wave_b_time }}</span
                                        >
                                        <span
                                            v-else
                                            class="text-slate-400 italic"
                                            >Pending Wave B</span
                                        >
                                        <span
                                            class="ml-1 text-[11px] text-slate-500"
                                            >({{ bc.wave_b_laps }} laps)</span
                                        >
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-bold text-slate-900 dark:text-slate-100"
                                    >
                                        {{ bc.total_laps }} laps
                                    </td>
                                    <td
                                        class="px-6 py-3.5 font-mono font-black text-amber-600 dark:text-amber-400"
                                    >
                                        {{ bc.combined_time || '—' }}
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <span
                                            v-if="bc.is_complete"
                                            class="inline-flex items-center rounded-full bg-emerald-500/10 px-2.5 py-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400"
                                        >
                                            Complete
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center rounded-full bg-amber-500/10 px-2.5 py-1 text-[11px] font-bold text-amber-600 dark:text-amber-400"
                                        >
                                            In Progress
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-else
                        class="p-8 text-center text-xs text-slate-500 italic dark:text-slate-400"
                    >
                        No BC racers with recorded results in Wave B or Wave C
                        for this event yet.
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
