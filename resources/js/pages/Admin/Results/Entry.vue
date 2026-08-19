<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { Trophy, Plus, Trash2, Save, GripVertical, Search } from '@lucide/vue';
import { ref, computed } from 'vue';

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
        { preserveState: false }
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
    if (!racerSearchQuery.value.trim()) return props.racers;
    const q = racerSearchQuery.value.toLowerCase().trim();
    return props.racers.filter((r) => {
        const fullName = `${r.first_name} ${r.last_name}`.toLowerCase();
        const bib = (r.bib_number || '').toLowerCase();
        const pin = (r.registrations?.[0]?.clothespin_number || '').toLowerCase();
        return fullName.includes(q) || bib.includes(q) || pin.includes(q);
    });
});

const selectRacerForQuickAdd = (racer: Racer) => {
    selectedQuickAddRacer.value = racer;
    racerSearchQuery.value = `${racer.first_name} ${racer.last_name}`;
    isDropdownOpen.value = false;

    // Set default category to racer's registered category if in this wave, otherwise wave default
    const registeredCat = racer.registrations?.[0]?.category_id;
    if (registeredCat && waveCategories.value.some((c) => c.id === registeredCat)) {
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
            alert('No matching racer found. Please search for a valid clothespin #, bib #, or name.');
            return;
        }
    }

    const exists = form.results.some((row) => row.racer_id === racerToAdd!.id);
    if (exists) {
        alert(`${racerToAdd.first_name} ${racerToAdd.last_name} is already in the finish sequence.`);
        return;
    }

    const catId = quickAddCategoryId.value || racerToAdd.registrations?.[0]?.category_id || defaultCategoryId.value;

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
    if (draggedIdx.value === null || draggedIdx.value === idx) return;

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
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: 'Timing & Results Entry', href: '/admin/results/entry' }]">
        <div class="p-6 w-full space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                    <Trophy class="w-6 h-6 text-amber-500" />
                    Clothes-Pin Finish Sequence & Results
                </h1>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Select race event and Wave (C, A, B, Kids). Finish sequence order is entered per wave, and racers can be assigned their category!</p>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 space-y-6 shadow-sm">
                <!-- Select Event & Wave -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Race Event</label>
                        <select v-model="selectedEvent" @change="loadSequence" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                            <option v-for="evt in events" :key="evt.id" :value="evt.id">
                                {{ evt.name }} ({{ evt.event_date }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Race Wave (Heat)</label>
                        <select v-model="selectedWave" @change="loadSequence" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                            <option v-for="w in waves" :key="w" :value="w">
                                Wave {{ w }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Quick Add Bar -->
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4 space-y-3">
                    <label class="block text-xs font-bold text-amber-600 dark:text-amber-400">
                        ⚡ Quick Add Wave {{ selectedWave }} Finisher by Clothespin #, Name, or Bib #
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                        <!-- Search Bar Input -->
                        <div class="relative sm:col-span-4">
                            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                            <input
                                v-model="racerSearchQuery"
                                @focus="isDropdownOpen = true"
                                @input="selectedQuickAddRacer = null"
                                type="text"
                                placeholder="Search pin #, bib #, or name..."
                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-xl pl-10 pr-4 py-2 text-xs text-slate-900 dark:text-white font-semibold focus:outline-none focus:border-amber-500 shadow-sm"
                            />

                            <!-- Search Dropdown Results -->
                            <div
                                v-if="isDropdownOpen && filteredRacers.length > 0"
                                class="absolute left-0 right-0 top-11 z-20 max-h-60 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl divide-y divide-slate-100 dark:divide-slate-800"
                            >
                                <button
                                    v-for="r in filteredRacers"
                                    :key="r.id"
                                    type="button"
                                    @click="selectRacerForQuickAdd(r)"
                                    class="w-full px-4 py-2.5 text-left text-xs hover:bg-amber-500/10 flex items-center justify-between transition-colors"
                                >
                                    <span class="font-bold text-slate-900 dark:text-white">
                                        {{ r.first_name }} {{ r.last_name }}
                                    </span>
                                    <div class="flex items-center gap-3 font-mono text-[11px]">
                                        <span v-if="r.team" class="text-slate-500 dark:text-slate-400 font-medium">
                                            {{ r.team.name }}
                                        </span>
                                        <span v-if="r.registrations?.[0]?.clothespin_number" class="px-2 py-0.5 rounded bg-amber-500 text-slate-950 font-black">
                                            Pin #{{ r.registrations[0].clothespin_number }}
                                        </span>
                                        <span class="text-slate-500 dark:text-slate-400 font-semibold">
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
                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 shadow-sm"
                            >
                                <option :value="null">Category (Default)</option>
                                <option v-for="cat in waveCategories" :key="cat.id" :value="cat.id">
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
                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 shadow-sm"
                            />
                        </div>

                        <!-- Optional Default Finish Time Input -->
                        <div class="sm:col-span-2">
                            <input
                                v-model="defaultQuickAddTime"
                                type="text"
                                placeholder="Time (45:12)"
                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-xl px-2.5 py-2 text-xs font-mono font-semibold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 shadow-sm"
                            />
                        </div>

                        <!-- Explicit Add Finisher Button -->
                        <div class="sm:col-span-2">
                            <button
                                type="button"
                                @click="handleQuickAdd"
                                class="w-full py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1 transition-all shadow-sm shrink-0"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Finisher</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Finisher order sequence table -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                            Wave {{ selectedWave }} Finisher Sequence ({{ form.results.length }} Racers)
                        </h2>
                    </div>

                    <!-- Column Header Labels -->
                    <div v-if="form.results.length > 0" class="hidden sm:flex items-center gap-3 px-3 py-1 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <div class="w-6"></div> <!-- Drag handle space -->
                        <div class="w-8 text-center">Pos</div>
                        <div class="w-[180px]">Racer Name</div>
                        <div class="w-[200px]">Bib # | Pin # | Team</div>
                        <div class="flex-1">Assigned Category</div>
                        <div class="w-24 text-center">Laps</div>
                        <div class="w-28 text-center">Finish Time</div>
                        <div class="w-8"></div> <!-- Delete space -->
                    </div>

                    <div v-if="form.results.length === 0" class="text-center py-8 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 text-xs">
                        No Wave {{ selectedWave }} finishers added yet. Use the search bar above to add clothespin or racer entries!
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="(row, idx) in form.results"
                            :key="idx"
                            draggable="true"
                            @dragstart="onDragStart(idx, $event)"
                            @dragover="onDragOver(idx, $event)"
                            @dragend="onDragEnd"
                            class="flex items-center gap-3 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border transition-all cursor-move"
                            :class="draggedIdx === idx ? 'border-amber-500 bg-amber-500/10 shadow-lg scale-[1.01]' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                        >
                            <!-- Drag Handle -->
                            <div class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-grab active:cursor-grabbing p-1">
                                <GripVertical class="w-4 h-4" />
                            </div>

                            <!-- Position Badge -->
                            <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 font-black text-xs flex items-center justify-center border border-amber-500/20 shrink-0">
                                #{{ idx + 1 }}
                            </div>

                            <!-- Racer Selection Dropdown -->
                            <div class="w-[180px]">
                                <select v-model="row.racer_id" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none">
                                    <option v-for="r in racers" :key="r.id" :value="r.id">
                                        {{ r.first_name }} {{ r.last_name }}
                                    </option>
                                </select>
                            </div>

                            <!-- READ-ONLY INFO COLUMN: Bib # | Pin # | Team -->
                            <div class="w-[200px] shrink-0 text-xs font-medium text-slate-700 dark:text-slate-300 flex items-center gap-1.5 overflow-hidden text-ellipsis whitespace-nowrap">
                                <span class="font-mono font-bold text-amber-600 dark:text-amber-400">
                                    #{{ racerMap.get(row.racer_id)?.bib_number || '—' }}
                                </span>
                                <span class="text-slate-400 dark:text-slate-600">|</span>
                                <span class="font-mono font-black text-amber-500">
                                    {{ racerMap.get(row.racer_id)?.registrations?.[0]?.clothespin_number ? 'Pin #' + racerMap.get(row.racer_id)!.registrations![0].clothespin_number : '—' }}
                                </span>
                                <span class="text-slate-400 dark:text-slate-600">|</span>
                                <span class="truncate font-semibold text-slate-600 dark:text-slate-400">
                                    {{ racerMap.get(row.racer_id)?.team?.name || 'Ind.' }}
                                </span>
                            </div>

                            <!-- Category Assignment Dropdown (Scoped strictly to Selected Wave) -->
                            <div class="flex-1 min-w-[150px]">
                                <select v-model="row.category_id" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none">
                                    <option v-for="cat in waveCategories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Laps Completed Input -->
                            <div class="w-24">
                                <div class="text-[10px] text-slate-400 font-semibold mb-0.5 sm:hidden">Laps Completed</div>
                                <input v-model="row.laps_completed" type="number" placeholder="Laps" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-200 text-center focus:outline-none" />
                            </div>

                            <!-- Finish Time Input -->
                            <div class="w-28">
                                <div class="text-[10px] text-slate-400 font-semibold mb-0.5 sm:hidden">Finish Time</div>
                                <input v-model="row.finish_time" type="text" placeholder="Time (45:12)" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg px-2.5 py-2 text-xs font-bold text-slate-900 dark:text-slate-200 text-center font-mono focus:outline-none" />
                            </div>

                            <!-- Delete Row -->
                            <button type="button" @click="removeRow(idx)" class="p-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950 rounded-lg transition-colors">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs text-slate-500 dark:text-slate-400">💡 Drag rows up/down to resequence finish order position.</span>
                    <button type="submit" :disabled="form.processing" class="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center gap-2 transition-all shadow-sm">
                        <Save class="w-4 h-4" /> Save Results & Compute Points
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
