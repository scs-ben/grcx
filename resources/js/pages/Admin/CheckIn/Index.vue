<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { UserCheck, Plus, CheckCircle2, Search, X } from '@lucide/vue';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Event {
    id: number;
    name: string;
    location: string;
    event_date: string;
    formatted_date?: string;
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
    email: string;
    phone: string;
    bib_number: string;
    team_id?: number;
    team?: Team;
}

interface Registration {
    id: number;
    fee_type: string;
    payment_method: string;
    amount_paid: number;
    is_season_pass: boolean;
    clothespin_number: string | null;
    is_checked_in: boolean;
    racer: Racer;
    categories: Category[];
    event?: Event;
}

const props = defineProps<{
    events: Event[];
    selectedEventId: number;
    registrations: Registration[];
    categories: Category[];
    racers: Racer[];
    teams?: Team[];
    waves?: string[];
}>();

const availableWaves = computed(() => props.waves || ['C', 'A', 'B', 'Kids']);

const searchQuery = ref('');
const selectedEvent = ref(props.selectedEventId);

const onEventChange = () => {
    router.get(
        '/admin/check-in',
        { event_id: selectedEvent.value },
        { preserveState: true },
    );
};

const filteredRegistrations = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.registrations;
    }

    const q = searchQuery.value.toLowerCase();

    return props.registrations.filter((r) => {
        const name =
            `${r.racer?.first_name} ${r.racer?.last_name}`.toLowerCase();
        const bib = r.racer?.bib_number || '';
        const clothespin = r.clothespin_number || '';

        return name.includes(q) || bib.includes(q) || clothespin.includes(q);
    });
});

// Check-in modal / quick input
const checkingInReg = ref<Registration | null>(null);

const checkInForm = useForm({
    clothespin_number: '',
    bib_number: '',
    waves: [] as string[],
    category_ids: [] as number[],
});

const checkInAvailableCategories = computed(() => {
    if (!checkInForm.waves || checkInForm.waves.length === 0) {
        return props.categories;
    }

    return props.categories.filter((c) => checkInForm.waves.includes(c.wave));
});

const toggleWaveForCheckIn = (wave: string) => {
    if (checkInForm.waves.includes(wave)) {
        checkInForm.waves = checkInForm.waves.filter((w) => w !== wave);
    } else {
        checkInForm.waves.push(wave);
    }

    const validCatIds = new Set(
        checkInAvailableCategories.value.map((c) => c.id),
    );
    checkInForm.category_ids = checkInForm.category_ids.filter((id) =>
        validCatIds.has(id),
    );
};

const openCheckIn = (reg: Registration) => {
    checkingInReg.value = reg;
    checkInForm.clothespin_number = reg.clothespin_number || '';
    checkInForm.bib_number = reg.racer?.bib_number || '';
    checkInForm.category_ids = (reg.categories || []).map((c) => c.id);
    // Pre-select registration categories' waves
    const waves = Array.from(
        new Set((reg.categories || []).map((c) => c.wave)),
    );
    checkInForm.waves = waves.length > 0 ? waves : ['C'];
};

const closeCheckIn = () => {
    checkingInReg.value = null;
};

const submitCheckIn = () => {
    if (!checkingInReg.value) {
        return;
    }

    checkInForm.post(`/admin/check-in/${checkingInReg.value.id}`, {
        onSuccess: () => closeCheckIn(),
    });
};

// Day-Of Registration Modal
const showDayOfModal = ref(false);
const isDayOfNameDropdownOpen = ref(false);

const dayOfForm = useForm({
    event_id: props.selectedEventId,
    racer_option: 'new' as 'new' | 'existing',
    racer_id: null as number | null,
    first_name: '',
    last_name: '',
    email: '',
    bib_number: '',
    team_id: null as number | null,
    new_team_name: '',
    clothespin_number: '',
    waves: ['C'] as string[],
    category_ids: [] as number[],
    fee_type: 'race',
    payment_method: 'cash',
    amount_paid: 35.0,
});

const getFeeAmount = (feeType: string): number => {
    if (feeType === 'season') {
        return 70.0;
    }

    if (feeType === 'youth') {
        return 20.0;
    }

    if (feeType === 'race') {
        return 35.0;
    }

    return 0.0; // bc, kids, costume, etc.
};

watch(
    () => dayOfForm.fee_type,
    (newFeeType) => {
        dayOfForm.amount_paid = getFeeAmount(newFeeType);
    },
);

const matchingRacers = computed(() => {
    const fn = dayOfForm.first_name.trim().toLowerCase();
    const ln = dayOfForm.last_name.trim().toLowerCase();

    if (!fn && !ln) {
        return [];
    }

    return props.racers.filter((r) => {
        const rFn = r.first_name.toLowerCase();
        const rLn = r.last_name.toLowerCase();
        const bib = (r.bib_number || '').toLowerCase();

        if (fn && ln) {
            return (
                (rFn.includes(fn) && rLn.includes(ln)) ||
                bib.includes(fn) ||
                bib.includes(ln)
            );
        }

        if (fn) {
            return (
                rFn.includes(fn) ||
                `${rFn} ${rLn}`.includes(fn) ||
                bib.includes(fn)
            );
        }

        return rLn.includes(ln) || bib.includes(ln);
    });
});

const selectExistingRacerForDayOf = (r: Racer) => {
    dayOfForm.racer_option = 'existing';
    dayOfForm.racer_id = r.id;
    dayOfForm.first_name = r.first_name;
    dayOfForm.last_name = r.last_name;
    dayOfForm.email = r.email || '';
    dayOfForm.bib_number = r.bib_number || '';
    dayOfForm.team_id = r.team_id || null;
    isDayOfNameDropdownOpen.value = false;
};

const onDayOfNameInput = () => {
    if (dayOfForm.racer_option === 'existing') {
        dayOfForm.racer_option = 'new';
        dayOfForm.racer_id = null;
    }

    isDayOfNameDropdownOpen.value = true;
};

// Categories filtered by selected waves
const dayOfAvailableCategories = computed(() => {
    if (!dayOfForm.waves || dayOfForm.waves.length === 0) {
        return props.categories;
    }

    return props.categories.filter((c) => dayOfForm.waves.includes(c.wave));
});

const toggleWaveForDayOf = (wave: string) => {
    if (dayOfForm.waves.includes(wave)) {
        dayOfForm.waves = dayOfForm.waves.filter((w) => w !== wave);
    } else {
        dayOfForm.waves.push(wave);
    }

    // Filter out selected categories that no longer belong to selected waves
    const validCatIds = new Set(
        dayOfAvailableCategories.value.map((c) => c.id),
    );
    dayOfForm.category_ids = dayOfForm.category_ids.filter((id) =>
        validCatIds.has(id),
    );
};

const submitDayOf = () => {
    dayOfForm.event_id = selectedEvent.value;

    if (dayOfForm.racer_option === 'existing' && !dayOfForm.racer_id) {
        alert('Please search and select an existing racer.');

        return;
    }

    if (dayOfForm.category_ids.length === 0) {
        alert('Please select at least one race category.');

        return;
    }

    dayOfForm.post('/admin/check-in/day-of', {
        onSuccess: () => {
            showDayOfModal.value = false;
            dayOfForm.reset();
            isDayOfNameDropdownOpen.value = false;
        },
    });
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Day-Of Race Check-In', href: '/admin/check-in' },
        ]"
    >
        <div class="w-full space-y-6 p-6">
            <!-- Header & Event Selector -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-bold text-slate-900 dark:text-slate-100"
                    >
                        <UserCheck class="h-6 w-6 text-amber-500" />
                        Race Day Check-In & Clothespin Station
                    </h1>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                        Check in pre-registered racers, assign clothespin
                        numbers for live timing finish order, or add new day-of
                        entries.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label
                            class="text-xs font-bold text-slate-700 dark:text-slate-300"
                            >Active Event:</label
                        >
                        <select
                            v-model="selectedEvent"
                            @change="onEventChange"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-900 shadow-sm focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
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

                    <button
                        @click="showDayOfModal = true"
                        class="flex items-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-xs font-black text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <Plus class="h-4 w-4" /> Add Day-Of Racer
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative max-w-md">
                <Search
                    class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search pre-registrations by racer name, bib #, or clothespin #..."
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pr-4 pl-10 text-xs text-slate-900 shadow-sm focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                />
            </div>

            <!-- Pre-registered Racers Table -->
            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
                    >
                        <tr>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Racer Name</th>
                            <th class="px-6 py-3.5">Bib #</th>
                            <th class="px-6 py-3.5">Clothespin #</th>
                            <th class="px-6 py-3.5">Category</th>
                            <th class="px-6 py-3.5">Fee & Payment</th>
                            <th class="px-6 py-3.5 text-right">
                                Check-In Action
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/60 dark:text-slate-200"
                    >
                        <tr
                            v-for="r in filteredRegistrations"
                            :key="r.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/40"
                        >
                            <td class="px-6 py-4">
                                <span
                                    v-if="r.is_checked_in"
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400"
                                >
                                    <CheckCircle2 class="h-3 w-3" /> Checked In
                                </span>
                                <span
                                    v-else
                                    class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400"
                                >
                                    Pre-Registered
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 font-bold text-slate-900 dark:text-white"
                            >
                                {{ r.racer?.first_name }}
                                {{ r.racer?.last_name }}
                                <div
                                    class="text-[10px] font-normal text-slate-500 dark:text-slate-400"
                                >
                                    {{ r.racer?.email || 'No email' }}
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 font-mono font-bold text-slate-700 dark:text-slate-300"
                            >
                                #{{ r.racer?.bib_number || 'Unassigned' }}
                            </td>
                            <td
                                class="px-6 py-4 font-mono text-sm font-black text-amber-600 dark:text-amber-400"
                            >
                                {{
                                    r.clothespin_number
                                        ? 'Pin #' + r.clothespin_number
                                        : '—'
                                }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="font-medium text-slate-900 dark:text-slate-100"
                                    >{{
                                        (r.categories || [])
                                            .map((c) => c.name)
                                            .join(', ') || 'None'
                                    }}</span
                                >
                                <span
                                    class="block font-mono text-[10px] text-slate-500 dark:text-slate-400"
                                    >Wave
                                    {{
                                        Array.from(
                                            new Set(
                                                (r.categories || []).map(
                                                    (c) => c.wave,
                                                ),
                                            ),
                                        ).join(', ') || 'N/A'
                                    }}</span
                                >
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    class="font-semibold text-slate-700 capitalize dark:text-slate-300"
                                >
                                    {{ r.fee_type }} (${{
                                        Number(r.amount_paid).toFixed(2)
                                    }})
                                </div>
                                <div
                                    class="font-mono text-[10px] text-slate-500 uppercase dark:text-slate-400"
                                >
                                    Paid: {{ r.payment_method || 'cash' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="openCheckIn(r)"
                                    class="rounded-lg px-3 py-1.5 text-xs font-bold shadow-sm transition-all"
                                    :class="
                                        r.is_checked_in
                                            ? 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300'
                                            : 'bg-emerald-500 text-slate-950 hover:bg-emerald-400'
                                    "
                                >
                                    {{
                                        r.is_checked_in
                                            ? 'Edit Clothespin'
                                            : 'Check In & Assign Pin'
                                    }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Check-In Modal -->
            <div
                v-if="checkingInReg"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                    >
                        <h2
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Check In: {{ checkingInReg.racer?.first_name }}
                            {{ checkingInReg.racer?.last_name }}
                        </h2>
                        <button
                            @click="closeCheckIn"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form
                        @submit.prevent="submitCheckIn"
                        class="space-y-4 text-xs"
                    >
                        <div>
                            <label
                                class="mb-1 block font-bold text-slate-900 dark:text-white"
                                >Clothespin Number *</label
                            >
                            <p
                                class="mb-1.5 text-[11px] text-slate-500 dark:text-slate-400"
                            >
                                Enter the clothespin bib number handed to the
                                racer at check-in table.
                            </p>
                            <input
                                v-model="checkInForm.clothespin_number"
                                type="text"
                                placeholder="e.g. 42"
                                required
                                autofocus
                                class="w-full rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-center text-lg font-black text-amber-600 focus:outline-none dark:text-amber-400"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Racer Wave(s) Participating Today *</label
                            >
                            <div
                                class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <label
                                    v-for="w in availableWaves"
                                    :key="w"
                                    class="flex cursor-pointer items-center gap-1.5 font-bold text-slate-900 dark:text-white"
                                >
                                    <input
                                        type="checkbox"
                                        :value="w"
                                        :checked="checkInForm.waves.includes(w)"
                                        @change="toggleWaveForCheckIn(w)"
                                        class="rounded text-amber-500 focus:ring-amber-500"
                                    />
                                    <span>Wave {{ w }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Category Checklist -->
                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Confirm Race Categories *</label
                            >
                            <div
                                class="max-h-44 space-y-1.5 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <label
                                    v-for="cat in checkInAvailableCategories"
                                    :key="cat.id"
                                    class="flex cursor-pointer items-center justify-between rounded-lg p-1.5 transition-colors hover:bg-slate-200/60 dark:hover:bg-slate-900"
                                >
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            :value="cat.id"
                                            v-model="checkInForm.category_ids"
                                            class="rounded text-amber-500 focus:ring-amber-500"
                                        />
                                        <span
                                            class="font-bold text-slate-900 dark:text-white"
                                            >{{ cat.name }}</span
                                        >
                                    </div>
                                    <span
                                        class="rounded bg-slate-200 px-1.5 py-0.5 font-mono text-[10px] text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                                    >
                                        Wave {{ cat.wave }}
                                    </span>
                                </label>
                                <div
                                    v-if="
                                        checkInAvailableCategories.length === 0
                                    "
                                    class="py-2 text-center text-xs text-slate-500"
                                >
                                    No categories available for selected waves.
                                </div>
                            </div>
                            <p
                                v-if="checkInForm.category_ids.length === 0"
                                class="mt-1 text-[11px] font-bold text-rose-500"
                            >
                                Please confirm at least one race category.
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Racer Bib Number (Optional)</label
                            >
                            <input
                                v-model="checkInForm.bib_number"
                                type="text"
                                placeholder="e.g. 104"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>

                        <div
                            class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                @click="closeCheckIn"
                                class="rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="checkInForm.processing"
                                class="rounded-lg bg-emerald-500 px-5 py-2 font-black text-slate-950 shadow-sm hover:bg-emerald-400"
                            >
                                Confirm Check-In
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Day-Of Registration Modal -->
            <div
                v-if="showDayOfModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-lg space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                    >
                        <h2
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Add Day-Of Racer & Check-In
                        </h2>
                        <button
                            @click="showDayOfModal = false"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form
                        @submit.prevent="submitDayOf"
                        class="space-y-4 text-xs"
                    >
                        <!-- Racer Form Fields with smart match on First/Last name -->
                        <div
                            v-if="dayOfForm.racer_option === 'existing'"
                            class="flex items-center justify-between rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-700 dark:text-emerald-300"
                        >
                            <span class="font-bold">
                                Linked to Existing Racer:
                                {{ dayOfForm.first_name }}
                                {{ dayOfForm.last_name }}
                            </span>
                            <button
                                type="button"
                                @click="
                                    dayOfForm.racer_option = 'new';
                                    dayOfForm.racer_id = null;
                                "
                                class="text-[11px] font-semibold text-emerald-800 underline hover:no-underline dark:text-emerald-200"
                            >
                                Change / Unlink
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative">
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >First Name *</label
                                >
                                <input
                                    v-model="dayOfForm.first_name"
                                    @input="onDayOfNameInput"
                                    @focus="isDayOfNameDropdownOpen = true"
                                    type="text"
                                    required
                                    placeholder="Jane"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />

                                <!-- Live Existing Racer Suggestions Dropdown -->
                                <div
                                    v-if="
                                        isDayOfNameDropdownOpen &&
                                        matchingRacers.length > 0 &&
                                        dayOfForm.racer_option !== 'existing'
                                    "
                                    class="absolute top-full right-0 left-0 z-30 mt-1 max-h-48 divide-y divide-slate-100 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900"
                                >
                                    <div
                                        class="bg-slate-50 px-3 py-1.5 text-[10px] font-bold text-amber-600 uppercase dark:bg-slate-950 dark:text-amber-400"
                                    >
                                        ⚡ Existing Racer Match Found:
                                    </div>
                                    <button
                                        v-for="r in matchingRacers"
                                        :key="r.id"
                                        type="button"
                                        @click="selectExistingRacerForDayOf(r)"
                                        class="flex w-full items-center justify-between px-3.5 py-2 text-left text-xs transition-colors hover:bg-amber-500/10"
                                    >
                                        <div>
                                            <span
                                                class="font-bold text-slate-900 dark:text-white"
                                            >
                                                {{ r.first_name }}
                                                {{ r.last_name }}
                                            </span>
                                            <span
                                                v-if="r.email"
                                                class="block text-[10px] text-slate-500 dark:text-slate-400"
                                            >
                                                {{ r.email }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex items-center gap-2 font-mono text-[11px]"
                                        >
                                            <span
                                                v-if="r.team"
                                                class="text-slate-500 dark:text-slate-400"
                                            >
                                                {{ r.team.name }}
                                            </span>
                                            <span
                                                v-if="r.bib_number"
                                                class="rounded bg-amber-500/20 px-1.5 py-0.5 font-bold text-amber-700 dark:text-amber-300"
                                            >
                                                Bib #{{ r.bib_number }}
                                            </span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Last Name *</label
                                >
                                <input
                                    v-model="dayOfForm.last_name"
                                    @input="onDayOfNameInput"
                                    @focus="isDayOfNameDropdownOpen = true"
                                    type="text"
                                    required
                                    placeholder="Doe"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Email (Optional)</label
                                >
                                <input
                                    v-model="dayOfForm.email"
                                    type="email"
                                    placeholder="racer@example.com"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Bib Number (Optional)</label
                                >
                                <input
                                    v-model="dayOfForm.bib_number"
                                    type="text"
                                    placeholder="Assigned bib #"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Team / Club Name</label
                                >
                                <select
                                    v-model="dayOfForm.team_id"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                >
                                    <option :value="null">
                                        Independent (No Team)
                                    </option>
                                    <option
                                        v-for="t in teams"
                                        :key="t.id"
                                        :value="t.id"
                                    >
                                        {{ t.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Or Add New Team</label
                                >
                                <input
                                    v-model="dayOfForm.new_team_name"
                                    type="text"
                                    placeholder="Type new team name..."
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold tracking-wider text-amber-600 uppercase dark:text-amber-400"
                                >1. Participating Wave(s) *</label
                            >
                            <div
                                class="flex flex-wrap items-center gap-3 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3"
                            >
                                <label
                                    v-for="w in availableWaves"
                                    :key="w"
                                    class="flex cursor-pointer items-center gap-1.5 text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    <input
                                        type="checkbox"
                                        :value="w"
                                        :checked="dayOfForm.waves.includes(w)"
                                        @change="toggleWaveForDayOf(w)"
                                        class="rounded text-amber-500 focus:ring-amber-500"
                                    />
                                    <span>Wave {{ w }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold tracking-wider text-slate-700 uppercase dark:text-slate-300"
                                >2. Select Race Category / Categories *</label
                            >
                            <div
                                class="grid max-h-48 grid-cols-1 gap-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <label
                                    v-for="cat in dayOfAvailableCategories"
                                    :key="cat.id"
                                    class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white p-2 text-xs font-semibold text-slate-900 transition-colors hover:border-amber-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                >
                                    <input
                                        type="checkbox"
                                        :value="cat.id"
                                        v-model="dayOfForm.category_ids"
                                        class="rounded text-amber-500 focus:ring-amber-500"
                                    />
                                    <div class="flex flex-col">
                                        <span class="font-bold">{{
                                            cat.name
                                        }}</span>
                                        <span
                                            class="text-[10px] text-slate-500 dark:text-slate-400"
                                            >Wave {{ cat.wave }}</span
                                        >
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block text-xs font-bold text-amber-600 dark:text-amber-400"
                                >Clothespin Tag Number *</label
                            >
                            <input
                                v-model="dayOfForm.clothespin_number"
                                type="text"
                                placeholder="e.g. 15"
                                required
                                class="w-full rounded-lg border border-amber-500/40 bg-amber-500/10 px-3 py-2 text-sm font-bold text-slate-900 focus:outline-none dark:text-white"
                            />
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Fee Type</label
                                >
                                <select
                                    v-model="dayOfForm.fee_type"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                >
                                    <option value="race">
                                        Single Race ($35)
                                    </option>
                                    <option value="youth">Youth ($20)</option>
                                    <option value="season">
                                        Season Pass ($70)
                                    </option>
                                    <option value="bc">BC (Free)</option>
                                    <option value="kids">
                                        Kids / Costume (Free)
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >How Paid</label
                                >
                                <select
                                    v-model="dayOfForm.payment_method"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="venmo">Venmo</option>
                                    <option value="check">Check</option>
                                    <option value="card">Card</option>
                                    <option value="free">Free</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Amount Paid ($)</label
                                >
                                <input
                                    v-model="dayOfForm.amount_paid"
                                    type="number"
                                    step="0.01"
                                    required
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <div
                            class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                @click="showDayOfModal = false"
                                class="rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="dayOfForm.processing"
                                class="rounded-lg bg-amber-500 px-5 py-2 font-black text-slate-950 shadow-sm hover:bg-amber-400"
                            >
                                Create & Check In
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
