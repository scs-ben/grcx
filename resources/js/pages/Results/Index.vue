<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Calendar, MapPin, Trophy, Search, Filter } from '@lucide/vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
}

const props = defineProps<{
    events: Event[];
    selectedEventId: number;
    activeEvent?: Event;
    categories: Category[];
    resultsByCategory: Record<number, RaceResult[]>;
}>();

const selectedEvent = ref(props.selectedEventId);
const racerSearch = ref('');
const selectedWave = ref<string>('ALL');
const selectedCategoryFilter = ref<number | 'ALL'>('ALL');

const wavesList = ['ALL', 'C', 'A', 'B', 'Kids'];

const onEventChange = () => {
    router.get('/results', { event_id: selectedEvent.value }, { preserveState: false });
};

// Filter categories available under selected wave filter
const availableCategoryFilters = computed(() => {
    if (selectedWave.value === 'ALL') return props.categories;
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
        if (!searchQ) return true;
        const name = `${res.racer?.first_name} ${res.racer?.last_name}`.toLowerCase();
        const bib = res.racer?.bib_number || '';
        const pin = res.racer?.registrations?.[0]?.clothespin_number || '';
        const team = res.racer?.team?.name?.toLowerCase() || '';
        const catName = res.category?.name?.toLowerCase() || '';
        return name.includes(searchQ) || bib.includes(searchQ) || pin.includes(searchQ) || team.includes(searchQ) || catName.includes(searchQ);
    });

    // Apply Category Filter if specific category selected
    const filteredResults = searchedResults.filter((res) => {
        if (selectedCategoryFilter.value === 'ALL') return true;
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
        singleWaveMap[selectedWave.value] = waveGroups[selectedWave.value] || [];
        return singleWaveMap;
    }

    return waveGroups;
});
</script>

<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
            <!-- Header Banner -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="text-xs font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 px-3 py-1 rounded-full">
                        Live Official Race Results
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-3 mb-2">
                        {{ activeEvent?.name || 'Grand Rapids Cyclocross Results' }}
                    </h1>
                    <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-600 dark:text-slate-400">
                        <span class="flex items-center gap-1.5"><Calendar class="w-4 h-4 text-amber-500" /> {{ activeEvent?.formatted_date || activeEvent?.event_date }}</span>
                        <span class="flex items-center gap-1.5"><MapPin class="w-4 h-4 text-amber-500" /> {{ activeEvent?.location }}</span>
                    </div>
                </div>

                <!-- Event Dropdown Switcher -->
                <div class="flex flex-col gap-1.5 self-start md:self-auto">
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Select Race Event:</label>
                    <select v-model="selectedEvent" @change="onEventChange" class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 shadow-sm">
                        <option v-for="evt in events" :key="evt.id" :value="evt.id">
                            {{ evt.name }} ({{ evt.formatted_date || evt.event_date }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Filters Bar: Wave Tabs, Category Filter & Search -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Wave Selector Tabs -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 mr-1 hidden sm:inline">Waves:</span>
                        <button
                            v-for="w in wavesList"
                            :key="w"
                            type="button"
                            @click="selectedWave = w; selectedCategoryFilter = 'ALL'"
                            class="px-4 py-2 rounded-xl text-xs font-black transition-all"
                            :class="selectedWave === w ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800'"
                        >
                            {{ w === 'ALL' ? 'All Waves' : 'Wave ' + w }}
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-72">
                        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                        <input
                            v-model="racerSearch"
                            type="text"
                            placeholder="Search racer, bib #, pin #..."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl pl-10 pr-4 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 shadow-sm"
                        />
                    </div>
                </div>

                <!-- Category Filter Dropdown -->
                <div class="flex items-center gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <Filter class="w-4 h-4 text-amber-500 shrink-0" />
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">Filter by Category:</label>
                    <select
                        v-model="selectedCategoryFilter"
                        class="bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 max-w-xs shadow-xs"
                    >
                        <option value="ALL">All Categories {{ selectedWave !== 'ALL' ? 'in Wave ' + selectedWave : '' }}</option>
                        <option v-for="cat in availableCategoryFilters" :key="cat.id" :value="cat.id">
                            {{ cat.name }} (Wave {{ cat.wave }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Wave Results Tables -->
            <div class="space-y-10">
                <div v-for="(resultsList, waveName) in waveResults" :key="waveName" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-6 py-4 flex items-center justify-between border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <Trophy class="w-5 h-5 text-amber-500" />
                            <h2 class="text-lg font-black text-slate-900 dark:text-white">Wave {{ waveName }} Results</h2>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            {{ resultsList.length }} Finishers
                        </span>
                    </div>

                    <div v-if="resultsList.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950/50 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
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
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 text-slate-800 dark:text-slate-200">
                                <tr v-for="res in resultsList" :key="res.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                    <td class="px-6 py-3.5 font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                        <span v-if="res.finish_position === 1" class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-black flex items-center justify-center text-[10px]">1</span>
                                        <span v-else-if="res.finish_position === 2" class="w-5 h-5 rounded-full bg-slate-300 text-slate-950 font-black flex items-center justify-center text-[10px]">2</span>
                                        <span v-else-if="res.finish_position === 3" class="w-5 h-5 rounded-full bg-amber-700 text-slate-100 font-black flex items-center justify-center text-[10px]">3</span>
                                        <span v-else class="text-slate-500 dark:text-slate-400 ml-1">{{ res.finish_position }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 font-bold text-amber-600 dark:text-amber-400">
                                        {{ res.category?.name }}
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-slate-700 dark:text-slate-300 font-bold">#{{ res.racer?.bib_number || '—' }}</td>
                                    <td class="px-6 py-3.5 font-mono text-amber-500 font-black">
                                        {{ res.racer?.registrations?.[0]?.clothespin_number ? 'Pin #' + res.racer.registrations[0].clothespin_number : '—' }}
                                    </td>
                                    <td class="px-6 py-3.5 font-medium text-slate-900 dark:text-white">{{ res.racer?.first_name }} {{ res.racer?.last_name }}</td>
                                    <td class="px-6 py-3.5 font-semibold text-slate-700 dark:text-slate-300">
                                        {{ res.racer?.team?.name || 'Independent' }}
                                    </td>
                                    <td class="px-6 py-3.5 text-slate-600 dark:text-slate-400">{{ res.laps_completed }}</td>
                                    <td class="px-6 py-3.5 font-mono text-slate-700 dark:text-slate-300">{{ res.finish_time || '—' }}</td>
                                    <td class="px-6 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">+{{ res.points_awarded }} pts</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-6 text-center text-xs text-slate-500 dark:text-slate-400 italic">
                        No Wave {{ waveName }} results recorded for this event.
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
