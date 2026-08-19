<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    FileText,
    Trophy,
    UserCheck,
    ArrowRight,
    Calendar,
    MapPin,
    Filter,
    Users,
    CheckCircle2,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

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

interface Category {
    id: number;
    name: string;
    wave: string;
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
}

const props = defineProps<{
    events: Event[];
    selectedEventId: number;
    activeEvent?: Event;
    categories: Category[];
    resultsByCategory: Record<number, RaceResult[]>;
    startListByCategory?: Record<number, RaceRegistration[]>;
}>();

const selectedEvent = ref(props.selectedEventId);
const activeTab = ref<'results' | 'startlist'>('results');
const selectedWave = ref<string>('ALL');
const selectedCategoryFilter = ref<number | 'ALL'>('ALL');

const wavesList = ['ALL', 'C', 'A', 'B', 'Kids'];

const onEventChange = () => {
    router.get(
        '/dashboard',
        { event_id: selectedEvent.value },
        { preserveState: true },
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
    const allResults: RaceResult[] = [];

    for (const catId in props.resultsByCategory) {
        allResults.push(...props.resultsByCategory[catId]);
    }

    // Apply Category Filter if specific category selected
    const filteredResults = allResults.filter((res) => {
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

// Group all registrations by Wave for Start List view
const waveStartList = computed(() => {
    const waveGroups: Record<
        string,
        Array<{ reg: RaceRegistration; category: Category }>
    > = {
        C: [],
        A: [],
        B: [],
        Kids: [],
    };

    if (!props.startListByCategory) {
        return waveGroups;
    }

    const startListMap = props.startListByCategory || {};

    props.categories.forEach((cat) => {
        if (
            selectedCategoryFilter.value !== 'ALL' &&
            cat.id !== selectedCategoryFilter.value
        ) {
            return;
        }

        const regList = startListMap[cat.id] || [];
        regList.forEach((reg) => {
            const wave = cat.wave || 'C';

            if (!waveGroups[wave]) {
                waveGroups[wave] = [];
            }

            waveGroups[wave].push({ reg, category: cat });
        });
    });

    // Sort alphabetically by racer last name within each wave
    for (const w in waveGroups) {
        waveGroups[w].sort((a, b) =>
            (a.reg.racer?.last_name || '').localeCompare(
                b.reg.racer?.last_name || '',
            ),
        );
    }

    if (selectedWave.value !== 'ALL') {
        const singleWaveMap: Record<
            string,
            Array<{ reg: RaceRegistration; category: Category }>
        > = {};
        singleWaveMap[selectedWave.value] =
            waveGroups[selectedWave.value] || [];

        return singleWaveMap;
    }

    return waveGroups;
});
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <Head title="Admin Dashboard - Grand Rapids Cyclocross" />

        <div class="w-full space-y-8 p-6">
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-black text-slate-900 dark:text-slate-100"
                    >
                        Grand Rapids Cyclocross Admin
                    </h1>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                        Manage public landing pages, timing & clothes-pin
                        results, and racer registrations.
                    </p>
                </div>
            </div>

            <!-- Recent/Active Event Standings Display Widget (Grouped by Wave with Category Filter) -->
            <div
                class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- Header Banner -->
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center dark:border-slate-800"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500/10 font-bold text-amber-600 dark:text-amber-400"
                        >
                            <Trophy class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2
                                    class="text-lg font-black text-slate-900 dark:text-white"
                                >
                                    Event Results & Wave Standings
                                </h2>
                                <span
                                    v-if="activeEvent"
                                    class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-black tracking-wider text-amber-600 uppercase dark:text-amber-400"
                                >
                                    {{ activeEvent.name }}
                                </span>
                            </div>
                            <p
                                class="mt-0.5 flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400"
                            >
                                <span
                                    ><Calendar
                                        class="mr-1 inline h-3.5 w-3.5 text-amber-500"
                                    />
                                    {{
                                        activeEvent?.formatted_date ||
                                        activeEvent?.event_date
                                    }}</span
                                >
                                <span>•</span>
                                <span
                                    ><MapPin
                                        class="mr-1 inline h-3.5 w-3.5 text-amber-500"
                                    />
                                    {{ activeEvent?.location }}</span
                                >
                            </p>
                        </div>
                    </div>

                    <!-- Mode Switcher Tabs & Event Dropdown -->
                    <div
                        class="flex flex-wrap items-center gap-3 self-start sm:self-auto"
                    >
                        <div
                            class="flex items-center rounded-xl border border-slate-200 bg-slate-100 p-1 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <button
                                type="button"
                                @click="activeTab = 'results'"
                                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
                                :class="
                                    activeTab === 'results'
                                        ? 'bg-amber-500 text-slate-950 shadow-xs'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                "
                            >
                                <Trophy class="h-3.5 w-3.5" />
                                <span>Results</span>
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'startlist'"
                                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
                                :class="
                                    activeTab === 'startlist'
                                        ? 'bg-amber-500 text-slate-950 shadow-xs'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                "
                            >
                                <Users class="h-3.5 w-3.5" />
                                <span>Pre-Race Start List</span>
                            </button>
                        </div>

                        <!-- Event Select Dropdown -->
                        <div class="flex items-center gap-2">
                            <label
                                class="text-xs font-bold text-slate-700 dark:text-slate-300"
                                >Event:</label
                            >
                            <select
                                v-model="selectedEvent"
                                @change="onEventChange"
                                class="rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2 text-xs font-bold text-slate-900 shadow-xs focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
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
                </div>

                <!-- Filters Bar: Wave Tabs & Category Dropdown -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                >
                    <div
                        class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                    >
                        <!-- Wave Tabs -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="mr-1 text-xs font-bold text-slate-500 dark:text-slate-400"
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
                                class="rounded-xl px-3.5 py-1.5 text-xs font-black transition-all"
                                :class="
                                    selectedWave === w
                                        ? 'bg-amber-500 text-slate-950 shadow-xs'
                                        : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'
                                "
                            >
                                {{ w === 'ALL' ? 'All Waves' : 'Wave ' + w }}
                            </button>
                        </div>

                        <!-- Category Filter Dropdown -->
                        <div class="flex items-center gap-2">
                            <Filter class="h-4 w-4 shrink-0 text-amber-500" />
                            <select
                                v-model="selectedCategoryFilter"
                                class="rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-900 shadow-xs focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
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
                </div>

                <!-- Wave Results Tables -->
                <div v-if="activeTab === 'results'" class="space-y-6">
                    <div
                        v-for="(resultsList, waveName) in waveResults"
                        :key="'wave-res-' + waveName"
                        class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-3 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div class="flex items-center gap-2">
                                <Trophy class="h-4 w-4 text-amber-500" />
                                <span
                                    class="text-sm font-black text-slate-900 dark:text-white"
                                    >Wave {{ waveName }} Heat Results</span
                                >
                            </div>
                            <span
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                {{ resultsList.length }} Finishers
                            </span>
                        </div>

                        <div
                            v-if="resultsList.length > 0"
                            class="overflow-x-auto"
                        >
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="border-b border-slate-200 bg-white font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                                >
                                    <tr>
                                        <th class="px-5 py-3">Finish Pos</th>
                                        <th class="px-5 py-3">Category</th>
                                        <th class="px-5 py-3">Bib #</th>
                                        <th class="px-5 py-3">Racer Name</th>
                                        <th class="px-5 py-3">Team / Club</th>
                                        <th class="px-5 py-3">Laps</th>
                                        <th class="px-5 py-3">Time</th>
                                        <th class="px-5 py-3 text-right">
                                            Points
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/60 dark:text-slate-200"
                                >
                                    <tr
                                        v-for="res in resultsList"
                                        :key="res.id"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-800/40"
                                    >
                                        <td
                                            class="flex items-center gap-2 px-5 py-3 font-black text-slate-900 dark:text-slate-100"
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
                                            class="px-5 py-3 font-bold text-amber-600 dark:text-amber-400"
                                        >
                                            {{ res.category?.name }}
                                        </td>
                                        <td
                                            class="px-5 py-3 font-mono font-bold text-slate-700 dark:text-slate-300"
                                        >
                                            #{{ res.racer?.bib_number || '—' }}
                                        </td>
                                        <td
                                            class="px-5 py-3 font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ res.racer?.first_name }}
                                            {{ res.racer?.last_name }}
                                        </td>
                                        <td
                                            class="px-5 py-3 font-medium text-slate-600 dark:text-slate-400"
                                        >
                                            {{
                                                res.racer?.team?.name ||
                                                'Independent'
                                            }}
                                        </td>
                                        <td
                                            class="px-5 py-3 text-slate-600 dark:text-slate-400"
                                        >
                                            {{ res.laps_completed }}
                                        </td>
                                        <td
                                            class="px-5 py-3 font-mono text-slate-700 dark:text-slate-300"
                                        >
                                            {{ res.finish_time || '—' }}
                                        </td>
                                        <td
                                            class="px-5 py-3 text-right font-black text-emerald-600 dark:text-emerald-400"
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
                            No Wave {{ waveName }} results recorded for this
                            event.
                        </div>
                    </div>
                </div>

                <!-- Pre-Race Start List Tables (Grouped by Wave with Category Filter) -->
                <div v-else class="space-y-6">
                    <div
                        v-for="(startEntries, waveName) in waveStartList"
                        :key="'wave-start-' + waveName"
                        class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-3 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div class="flex items-center gap-2">
                                <Users class="h-4 w-4 text-amber-500" />
                                <span
                                    class="text-sm font-black text-slate-900 dark:text-white"
                                    >Wave {{ waveName }} Start List</span
                                >
                            </div>
                            <span
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                {{ startEntries.length }} Registered Racers
                            </span>
                        </div>

                        <div
                            v-if="startEntries.length > 0"
                            class="overflow-x-auto"
                        >
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="border-b border-slate-200 bg-white font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                                >
                                    <tr>
                                        <th class="px-5 py-3">Racer Name</th>
                                        <th class="px-5 py-3">Category</th>
                                        <th class="px-5 py-3">Bib #</th>
                                        <th class="px-5 py-3">Team / Club</th>
                                        <th class="px-5 py-3">Clothespin #</th>
                                        <th class="px-5 py-3">Pass Type</th>
                                        <th class="px-5 py-3 text-right">
                                            Check-In Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/60 dark:text-slate-200"
                                >
                                    <tr
                                        v-for="item in startEntries"
                                        :key="
                                            item.reg.id + '-' + item.category.id
                                        "
                                        class="hover:bg-slate-50 dark:hover:bg-slate-800/40"
                                    >
                                        <td
                                            class="px-5 py-3 font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ item.reg.racer?.first_name }}
                                            {{ item.reg.racer?.last_name }}
                                        </td>
                                        <td
                                            class="px-5 py-3 font-bold text-amber-600 dark:text-amber-400"
                                        >
                                            {{ item.category.name }}
                                        </td>
                                        <td
                                            class="px-5 py-3 font-mono font-bold text-slate-700 dark:text-slate-300"
                                        >
                                            #{{
                                                item.reg.racer?.bib_number ||
                                                '—'
                                            }}
                                        </td>
                                        <td
                                            class="px-5 py-3 font-medium text-slate-600 dark:text-slate-400"
                                        >
                                            {{
                                                item.reg.racer?.team?.name ||
                                                'Independent'
                                            }}
                                        </td>
                                        <td
                                            class="px-5 py-3 font-mono font-black text-amber-500"
                                        >
                                            {{
                                                item.reg.clothespin_number
                                                    ? 'Pin #' +
                                                      item.reg.clothespin_number
                                                    : '—'
                                            }}
                                        </td>
                                        <td
                                            class="px-5 py-3 font-medium text-slate-600 dark:text-slate-400"
                                        >
                                            <span
                                                v-if="item.reg.is_season_pass"
                                                class="rounded border border-purple-500/20 bg-purple-500/10 px-2 py-0.5 text-[10px] font-bold text-purple-600 dark:text-purple-400"
                                            >
                                                Season Pass
                                            </span>
                                            <span
                                                v-else
                                                class="text-[11px] text-slate-500"
                                            >
                                                Single Race
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <span
                                                v-if="item.reg.is_checked_in"
                                                class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400"
                                            >
                                                <CheckCircle2
                                                    class="h-3.5 w-3.5 text-emerald-500"
                                                />
                                                Checked In
                                            </span>
                                            <span
                                                v-else
                                                class="rounded-full border border-slate-500/20 bg-slate-500/10 px-2 py-0.5 text-[10px] font-medium text-slate-500"
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
                            No Wave {{ waveName }} pre-race registrants found
                            for this event.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Cards Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <!-- Day-Of Check-In -->
                <div
                    class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div>
                        <div
                            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400"
                        >
                            <UserCheck class="h-5 w-5" />
                        </div>
                        <h2
                            class="mb-1 text-base font-bold text-slate-900 dark:text-white"
                        >
                            Race Day Check-In
                        </h2>
                        <p
                            class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            Check in racers, assign clothespin numbers for
                            timing, or add new day-of entries.
                        </p>
                    </div>
                    <Link
                        href="/admin/check-in"
                        class="mt-6 flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <span>Check-In Station</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Race Events & Categories -->
                <div
                    class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div>
                        <div
                            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400"
                        >
                            <Calendar class="h-5 w-5" />
                        </div>
                        <h2
                            class="mb-1 text-base font-bold text-slate-900 dark:text-white"
                        >
                            Events & Categories
                        </h2>
                        <p
                            class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            Create and edit series race events, dates, venues,
                            wave heats, and categories.
                        </p>
                    </div>
                    <Link
                        href="/admin/events"
                        class="mt-6 flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <span>Manage Events</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Manage CMS Pages -->
                <div
                    class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div>
                        <div
                            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400"
                        >
                            <FileText class="h-5 w-5" />
                        </div>
                        <h2
                            class="mb-1 text-base font-bold text-slate-900 dark:text-white"
                        >
                            Content Pages
                        </h2>
                        <p
                            class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            Update editable content pages like Schedule & Rules,
                            Pricing, and Policies.
                        </p>
                    </div>
                    <Link
                        href="/admin/pages"
                        class="mt-6 flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <span>Manage Pages</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Timing & Results Entry -->
                <div
                    class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div>
                        <div
                            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400"
                        >
                            <Trophy class="h-5 w-5" />
                        </div>
                        <h2
                            class="mb-1 text-base font-bold text-slate-900 dark:text-white"
                        >
                            Timing & Results
                        </h2>
                        <p
                            class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            Enter clothes-pin finish sequence order by wave and
                            assign category points.
                        </p>
                    </div>
                    <Link
                        href="/admin/results/entry"
                        class="mt-6 flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <span>Enter Results</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Registrations -->
                <div
                    class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div>
                        <div
                            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400"
                        >
                            <UserCheck class="h-5 w-5" />
                        </div>
                        <h2
                            class="mb-1 text-base font-bold text-slate-900 dark:text-white"
                        >
                            Registrations
                        </h2>
                        <p
                            class="text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            View registered racers, bib assignments, season
                            passes, and team affiliations.
                        </p>
                    </div>
                    <Link
                        href="/admin/registrations"
                        class="mt-6 flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <span>View Registrations</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
