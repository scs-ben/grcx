<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { Trophy, Plus, Trash2, Save, GripVertical, Search } from '@lucide/vue';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Event {
    id: number;
    name: string;
    location: string;
    event_date: string;
}

interface Category {
    id: number;
    name: string;
    wave: string;
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
    registrations?: Array<{
        category_id?: number;
        clothespin_number?: string;
    }>;
}

interface ExistingResult {
    id: number;
    racer_id: number;
    category_id: number;
    finish_position: number;
    laps_completed: number;
    finish_time: string | null;
}

const props = defineProps<{
    events: Event[];
    categories: Category[];
    waves: string[];
    racers: Racer[];
    selectedEventId: number;
    selectedWave: string;
    existingResults: ExistingResult[];
}>();

const selectedEvent = ref(props.selectedEventId);
const selectedWave = ref(props.selectedWave);

// Filter categories available in the selected wave
const waveCategories = computed(() => {
    return props.categories.filter((c) => c.wave === selectedWave.value);
});

const defaultCategoryId = computed(() => {
    return waveCategories.value[0]?.id || props.categories[0]?.id || 1;
});

// Quick Add Form Controls
const selectedQuickAddRacer = ref<Racer | null>(null);
const quickAddCategoryId = ref<number | null>(null);
const defaultQuickAddLaps = ref<number | null>(null);
const defaultQuickAddTime = ref<string>('');
const racerSearchQuery = ref('');
const isDropdownOpen = ref(false);

// Map of racers for quick read-only lookup in the table
const racerMap = computed(() => {
    const map = new Map<number, Racer>();
    props.racers.forEach((r) => map.set(r.id, r));

    return map;
});

const initialResults = () => {
    if (props.existingResults && props.existingResults.length > 0) {
        return props.existingResults.map((r) => ({
            racer_id: r.racer_id,
            category_id: r.category_id,
            finish_position: r.finish_position,
            laps_completed: r.laps_completed ?? null,
            finish_time: r.finish_time || '',
        }));
    }

    return [];
};

const form = useForm({
    event_id: props.selectedEventId,
    results: initialResults(),
});

const loadSequence = () => {
    router.get(
        '/admin/results/entry',
        { event_id: selectedEvent.value, wave: selectedWave.value },
        { preserveState: false },
    );
};

const resequence = () => {
    form.results.forEach((row, idx) => {
        row.finish_position = idx + 1;
    });
};

const removeRow = (index: number) => {
    form.results.splice(index, 1);
    resequence();
};

const filteredRacers = computed(() => {
    if (!racerSearchQuery.value.trim()) {
        return props.racers;
    }

    const q = racerSearchQuery.value.toLowerCase().trim();

    return props.racers.filter((r) => {
        const fullName = `${r.first_name} ${r.last_name}`.toLowerCase();
        const bib = (r.bib_number || '').toLowerCase();
        const pin = (
            r.registrations?.[0]?.clothespin_number || ''
        ).toLowerCase();

        return fullName.includes(q) || bib.includes(q) || pin.includes(q);
    });
});

const selectRacerForQuickAdd = (racer: Racer) => {
    selectedQuickAddRacer.value = racer;
    racerSearchQuery.value = `${racer.first_name} ${racer.last_name}`;
    isDropdownOpen.value = false;

    // Set default category to racer's registered category if in this wave, otherwise wave default
    const registeredCat = racer.registrations?.[0]?.category_id;

    if (
        registeredCat &&
        waveCategories.value.some((c) => c.id === registeredCat)
    ) {
        quickAddCategoryId.value = registeredCat;
    } else {
        quickAddCategoryId.value = defaultCategoryId.value;
    }
};

const handleQuickAdd = () => {
    let racerToAdd = selectedQuickAddRacer.value;

    if (!racerToAdd) {
        if (filteredRacers.value.length > 0) {
            racerToAdd = filteredRacers.value[0];
        } else {
            alert(
                'No matching racer found. Please search for a valid clothespin #, bib #, or name.',
            );

            return;
        }
    }

    const exists = form.results.some((row) => row.racer_id === racerToAdd!.id);

    if (exists) {
        alert(
            `${racerToAdd.first_name} ${racerToAdd.last_name} is already in the finish sequence.`,
        );

        return;
    }

    const catId =
        quickAddCategoryId.value ||
        racerToAdd.registrations?.[0]?.category_id ||
        defaultCategoryId.value;

    const nextPos = form.results.length + 1;
    form.results.push({
        racer_id: racerToAdd.id,
        category_id: catId,
        finish_position: nextPos,
        laps_completed: defaultQuickAddLaps.value,
        finish_time: defaultQuickAddTime.value,
    });

    resequence();

    // Reset quick add search inputs
    selectedQuickAddRacer.value = null;
    racerSearchQuery.value = '';
    isDropdownOpen.value = false;
};

// HTML5 Drag and Drop reordering logic
const draggedIdx = ref<number | null>(null);

const onDragStart = (idx: number, e: DragEvent) => {
    draggedIdx.value = idx;

    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
    }
};

const onDragOver = (idx: number, e: DragEvent) => {
    e.preventDefault();

    if (draggedIdx.value === null || draggedIdx.value === idx) {
        return;
    }

    const itemToMove = form.results[draggedIdx.value];
    form.results.splice(draggedIdx.value, 1);
    form.results.splice(idx, 0, itemToMove);
    draggedIdx.value = idx;
    resequence();
};

const onDragEnd = () => {
    draggedIdx.value = null;
    resequence();
};

const submit = () => {
    form.event_id = selectedEvent.value;
    resequence();
    form.post('/admin/results');
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Timing & Results Entry', href: '/admin/results/entry' },
        ]"
    >
        <div class="w-full space-y-6 p-6">
            <div>
                <h1
                    class="flex items-center gap-2 text-2xl font-bold text-slate-900 dark:text-slate-100"
                >
                    <Trophy class="h-6 w-6 text-amber-500" />
                    Clothes-Pin Finish Sequence & Results
                </h1>
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                    Select race event and Wave (C, A, B, Kids). Finish sequence
                    order is entered per wave, and racers can be assigned their
                    category!
                </p>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- Select Event & Wave -->
                <div
                    class="grid grid-cols-1 gap-4 border-b border-slate-200 pb-6 sm:grid-cols-2 dark:border-slate-800"
                >
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                            >Race Event</label
                        >
                        <select
                            v-model="selectedEvent"
                            @change="loadSequence"
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-xs font-bold text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                        >
                            <option
                                v-for="evt in events"
                                :key="evt.id"
                                :value="evt.id"
                            >
                                {{ evt.name }} ({{ evt.event_date }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                            >Race Wave (Heat)</label
                        >
                        <select
                            v-model="selectedWave"
                            @change="loadSequence"
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-xs font-bold text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                        >
                            <option v-for="w in waves" :key="w" :value="w">
                                Wave {{ w }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Quick Add Bar -->
                <div
                    class="space-y-3 rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4"
                >
                    <label
                        class="block text-xs font-bold text-amber-600 dark:text-amber-400"
                    >
                        ⚡ Quick Add Wave {{ selectedWave }} Finisher by
                        Clothespin #, Name, or Bib #
                    </label>

                    <div
                        class="grid grid-cols-1 items-center gap-3 sm:grid-cols-12"
                    >
                        <!-- Search Bar Input -->
                        <div class="relative sm:col-span-4">
                            <Search
                                class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                            />
                            <input
                                v-model="racerSearchQuery"
                                @focus="isDropdownOpen = true"
                                @input="selectedQuickAddRacer = null"
                                type="text"
                                placeholder="Search pin #, bib #, or name..."
                                class="w-full rounded-xl border border-slate-300 bg-white py-2 pr-4 pl-10 text-xs font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                            />

                            <!-- Search Dropdown Results -->
                            <div
                                v-if="
                                    isDropdownOpen && filteredRacers.length > 0
                                "
                                class="absolute top-11 right-0 left-0 z-20 max-h-60 divide-y divide-slate-100 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900"
                            >
                                <button
                                    v-for="r in filteredRacers"
                                    :key="r.id"
                                    type="button"
                                    @click="selectRacerForQuickAdd(r)"
                                    class="flex w-full items-center justify-between px-4 py-2.5 text-left text-xs transition-colors hover:bg-amber-500/10"
                                >
                                    <span
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ r.first_name }} {{ r.last_name }}
                                    </span>
                                    <div
                                        class="flex items-center gap-3 font-mono text-[11px]"
                                    >
                                        <span
                                            v-if="r.team"
                                            class="font-medium text-slate-500 dark:text-slate-400"
                                        >
                                            {{ r.team.name }}
                                        </span>
                                        <span
                                            v-if="
                                                r.registrations?.[0]
                                                    ?.clothespin_number
                                            "
                                            class="rounded bg-amber-500 px-2 py-0.5 font-black text-slate-950"
                                        >
                                            Pin #{{
                                                r.registrations[0]
                                                    .clothespin_number
                                            }}
                                        </span>
                                        <span
                                            class="font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            Bib #{{ r.bib_number || 'N/A' }}
                                        </span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Category Select -->
                        <div class="sm:col-span-3">
                            <select
                                v-model="quickAddCategoryId"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                            >
                                <option :value="null">
                                    Category (Default)
                                </option>
                                <option
                                    v-for="cat in waveCategories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Optional Default Laps Input -->
                        <div class="sm:col-span-1">
                            <input
                                v-model.number="defaultQuickAddLaps"
                                type="number"
                                placeholder="Laps"
                                class="w-full rounded-xl border border-slate-300 bg-white px-2.5 py-2 text-xs font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <!-- Optional Default Finish Time Input -->
                        <div class="sm:col-span-2">
                            <input
                                v-model="defaultQuickAddTime"
                                type="text"
                                placeholder="Time (45:12)"
                                class="w-full rounded-xl border border-slate-300 bg-white px-2.5 py-2 font-mono text-xs font-semibold text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <!-- Explicit Add Finisher Button -->
                        <div class="sm:col-span-2">
                            <button
                                type="button"
                                @click="handleQuickAdd"
                                class="flex w-full shrink-0 items-center justify-center gap-1 rounded-xl bg-amber-500 px-3 py-2 text-xs font-black text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                            >
                                <Plus class="h-4 w-4" />
                                <span>Add Finisher</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Finisher order sequence table -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Wave {{ selectedWave }} Finisher Sequence ({{
                                form.results.length
                            }}
                            Racers)
                        </h2>
                    </div>

                    <!-- Column Header Labels -->
                    <div
                        v-if="form.results.length > 0"
                        class="hidden items-center gap-3 px-3 py-1 text-[11px] font-bold tracking-wider text-slate-500 uppercase sm:flex dark:text-slate-400"
                    >
                        <div class="w-6"></div>
                        <!-- Drag handle space -->
                        <div class="w-8 text-center">Pos</div>
                        <div class="w-[180px]">Racer Name</div>
                        <div class="w-[200px]">Bib # | Pin # | Team</div>
                        <div class="flex-1">Assigned Category</div>
                        <div class="w-24 text-center">Laps</div>
                        <div class="w-28 text-center">Finish Time</div>
                        <div class="w-8"></div>
                        <!-- Delete space -->
                    </div>

                    <div
                        v-if="form.results.length === 0"
                        class="rounded-xl border-2 border-dashed border-slate-200 py-8 text-center text-xs text-slate-500 dark:border-slate-800"
                    >
                        No Wave {{ selectedWave }} finishers added yet. Use the
                        search bar above to add clothespin or racer entries!
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="(row, idx) in form.results"
                            :key="idx"
                            draggable="true"
                            @dragstart="onDragStart(idx, $event)"
                            @dragover="onDragOver(idx, $event)"
                            @dragend="onDragEnd"
                            class="flex cursor-move items-center gap-3 rounded-xl border bg-slate-50 p-3 transition-all dark:bg-slate-950"
                            :class="
                                draggedIdx === idx
                                    ? 'scale-[1.01] border-amber-500 bg-amber-500/10 shadow-lg'
                                    : 'border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700'
                            "
                        >
                            <!-- Drag Handle -->
                            <div
                                class="cursor-grab p-1 text-slate-400 hover:text-slate-600 active:cursor-grabbing dark:hover:text-slate-200"
                            >
                                <GripVertical class="h-4 w-4" />
                            </div>

                            <!-- Position Badge -->
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-amber-500/20 bg-amber-500/10 text-xs font-black text-amber-600 dark:text-amber-400"
                            >
                                #{{ idx + 1 }}
                            </div>

                            <!-- Racer Selection Dropdown -->
                            <div class="w-[180px]">
                                <select
                                    v-model="row.racer_id"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-xs font-bold text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                >
                                    <option
                                        v-for="r in racers"
                                        :key="r.id"
                                        :value="r.id"
                                    >
                                        {{ r.first_name }} {{ r.last_name }}
                                    </option>
                                </select>
                            </div>

                            <!-- READ-ONLY INFO COLUMN: Bib # | Pin # | Team -->
                            <div
                                class="flex w-[200px] shrink-0 items-center gap-1.5 overflow-hidden text-xs font-medium text-ellipsis whitespace-nowrap text-slate-700 dark:text-slate-300"
                            >
                                <span
                                    class="font-mono font-bold text-amber-600 dark:text-amber-400"
                                >
                                    #{{
                                        racerMap.get(row.racer_id)
                                            ?.bib_number || '—'
                                    }}
                                </span>
                                <span class="text-slate-400 dark:text-slate-600"
                                    >|</span
                                >
                                <span
                                    class="font-mono font-black text-amber-500"
                                >
                                    {{
                                        racerMap.get(row.racer_id)
                                            ?.registrations?.[0]
                                            ?.clothespin_number
                                            ? 'Pin #' +
                                              racerMap.get(row.racer_id)!
                                                  .registrations![0]
                                                  .clothespin_number
                                            : '—'
                                    }}
                                </span>
                                <span class="text-slate-400 dark:text-slate-600"
                                    >|</span
                                >
                                <span
                                    class="truncate font-semibold text-slate-600 dark:text-slate-400"
                                >
                                    {{
                                        racerMap.get(row.racer_id)?.team
                                            ?.name || 'Ind.'
                                    }}
                                </span>
                            </div>

                            <!-- Category Assignment Dropdown (Scoped strictly to Selected Wave) -->
                            <div class="min-w-[150px] flex-1">
                                <select
                                    v-model="row.category_id"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                >
                                    <option
                                        v-for="cat in waveCategories"
                                        :key="cat.id"
                                        :value="cat.id"
                                    >
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Laps Completed Input -->
                            <div class="w-24">
                                <div
                                    class="mb-0.5 text-[10px] font-semibold text-slate-400 sm:hidden"
                                >
                                    Laps Completed
                                </div>
                                <input
                                    v-model="row.laps_completed"
                                    type="number"
                                    placeholder="Laps"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-center text-xs font-bold text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                                />
                            </div>

                            <!-- Finish Time Input -->
                            <div class="w-28">
                                <div
                                    class="mb-0.5 text-[10px] font-semibold text-slate-400 sm:hidden"
                                >
                                    Finish Time
                                </div>
                                <input
                                    v-model="row.finish_time"
                                    type="text"
                                    placeholder="Time (45:12)"
                                    class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-center font-mono text-xs font-bold text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                                />
                            </div>

                            <!-- Delete Row -->
                            <button
                                type="button"
                                @click="removeRow(idx)"
                                class="rounded-lg p-2 text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between border-t border-slate-200 pt-4 dark:border-slate-800"
                >
                    <span class="text-xs text-slate-500 dark:text-slate-400"
                        >💡 Drag rows up/down to resequence finish order
                        position.</span
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 rounded-xl bg-amber-500 px-6 py-3 text-xs font-black text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <Save class="h-4 w-4" /> Save Results & Compute Points
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
