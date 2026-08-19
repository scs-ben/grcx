<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    Edit3,
    Trash2,
    Filter,
    X,
    Hash,
    Shield,
    Plus,
    CheckCircle2,
    Clock,
    XCircle,
    Search,
} from '@lucide/vue';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Event {
    id: number;
    name: string;
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
    season_year: number;
    status: 'approved' | 'pending' | 'rejected';
    created_at: string;
    event_id?: number;
    racer: Racer;
    event?: Event;
    categories: Category[];
}

const props = defineProps<{
    registrations: Registration[];
    events: Event[];
    categories: Category[];
    racers: Racer[];
    teams: Team[];
    availableYears: number[];
}>();

const selectedYear = ref<string>('all');

const filteredRegistrations = computed(() => {
    if (selectedYear.value === 'all') {
        return props.registrations;
    }

    return props.registrations.filter(
        (r) => r.season_year === Number(selectedYear.value),
    );
});

const availableWaves = ['C', 'A', 'B', 'Kids'];

// New Registration Modal State
const showCreateModal = ref(false);
const createRacerSearch = ref('');
const isCreateRacerDropdownOpen = ref(false);

const createForm = useForm({
    racer_option: 'new' as 'new' | 'existing',
    racer_id: null as number | null,
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    bib_number: '',
    team_id: null as number | null,
    new_team_name: '',
    waves: ['C'] as string[],
    category_ids: [] as number[],
    is_season_pass: false,
    event_id: props.events[0]?.id || (null as number | null),
    season_year: 2026,
    fee_type: 'race',
    payment_method: 'cash',
    amount_paid: 35.0,
});

const createMatchingRacers = computed(() => {
    const fn = createForm.first_name.trim().toLowerCase();
    const ln = createForm.last_name.trim().toLowerCase();

    if (!fn && !ln) {
        return [];
    }

    return props.racers.filter((r) => {
        const rFn = r.first_name.toLowerCase();
        const rLn = r.last_name.toLowerCase();
        const bib = (r.bib_number || '').toLowerCase();

        if (fn && ln) {
            return (rFn.includes(fn) && rLn.includes(ln)) || bib.includes(fn) || bib.includes(ln);
        }
        if (fn) {
            return rFn.includes(fn) || `${rFn} ${rLn}`.includes(fn) || bib.includes(fn);
        }
        return rLn.includes(ln) || bib.includes(ln);
    });
});

const selectExistingRacerForCreate = (r: Racer) => {
    createForm.racer_option = 'existing';
    createForm.racer_id = r.id;
    createForm.first_name = r.first_name;
    createForm.last_name = r.last_name;
    createForm.email = r.email || '';
    createForm.phone = r.phone || '';
    createForm.bib_number = r.bib_number || '';
    createForm.team_id = r.team_id || null;
    isCreateRacerDropdownOpen.value = false;
};

const onCreateNameInput = () => {
    if (createForm.racer_option === 'existing') {
        createForm.racer_option = 'new';
        createForm.racer_id = null;
    }
    isCreateRacerDropdownOpen.value = true;
};

const createAvailableCategories = computed(() => {
    if (!createForm.waves || createForm.waves.length === 0) {
        return props.categories;
    }

    return props.categories.filter((c) => createForm.waves.includes(c.wave));
});

const toggleWaveForCreate = (wave: string) => {
    if (createForm.waves.includes(wave)) {
        createForm.waves = createForm.waves.filter((w) => w !== wave);
    } else {
        createForm.waves.push(wave);
    }

    const validCatIds = new Set(
        createAvailableCategories.value.map((c) => c.id),
    );
    createForm.category_ids = createForm.category_ids.filter((id) =>
        validCatIds.has(id),
    );
};

const openCreateModal = () => {
    createForm.reset();
    createForm.event_id = props.events[0]?.id || null;
    createRacerSearch.value = '';
    isCreateRacerDropdownOpen.value = false;
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const submitCreateRegistration = () => {
    if (createForm.racer_option === 'existing' && !createForm.racer_id) {
        alert('Please search and select an existing racer.');
        return;
    }

    if (createForm.category_ids.length === 0) {
        alert('Please select at least one race category.');

        return;
    }

    createForm.post('/admin/registrations', {
        onSuccess: () => {
            closeCreateModal();
            createRacerSearch.value = '';
            isCreateRacerDropdownOpen.value = false;
        },
    });
};

const getFeeAmount = (feeType: string, isSeasonPass: boolean): number => {
    if (isSeasonPass || feeType === 'season') {
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
    () => [createForm.fee_type, createForm.is_season_pass],
    ([newFeeType, newIsSeasonPass]) => {
        createForm.amount_paid = getFeeAmount(newFeeType as string, newIsSeasonPass as boolean);
    },
);

// Edit Modal State
const editingRegistration = ref<Registration | null>(null);

const editForm = useForm({
    category_ids: [] as number[],
    event_id: null as number | null,
    season_year: 2026,
    fee_type: 'race',
    payment_method: 'cash',
    amount_paid: 35.0,
    is_season_pass: false,
    status: 'approved' as 'approved' | 'pending' | 'rejected',
    bib_number: '',
    team_id: null as number | null,
    new_team_name: '',
});

watch(
    () => [editForm.fee_type, editForm.is_season_pass],
    ([newFeeType, newIsSeasonPass]) => {
        if (editingRegistration.value) {
            editForm.amount_paid = getFeeAmount(newFeeType as string, newIsSeasonPass as boolean);
        }
    },
);

const openEditModal = (reg: Registration) => {
    editingRegistration.value = reg;
    editForm.category_ids = (reg.categories || []).map((c) => c.id);
    editForm.event_id = reg.event_id || null;
    editForm.season_year = reg.season_year || 2026;
    editForm.fee_type = reg.fee_type;
    editForm.payment_method = reg.payment_method || 'cash';
    editForm.amount_paid = reg.amount_paid;
    editForm.is_season_pass = reg.is_season_pass;
    editForm.status = reg.status || 'approved';
    editForm.bib_number = reg.racer?.bib_number || '';
    editForm.team_id = reg.racer?.team_id || null;
    editForm.new_team_name = '';
};

const closeEditModal = () => {
    editingRegistration.value = null;
};

const updateRegistration = () => {
    if (!editingRegistration.value) {
        return;
    }

    if (editForm.category_ids.length === 0) {
        alert('Please select at least one race category.');
        return;
    }

    editForm.put(`/admin/registrations/${editingRegistration.value.id}`, {
        onSuccess: () => closeEditModal(),
    });
};

const approveRegistration = (reg: Registration) => {
    useForm({}).post(`/admin/registrations/${reg.id}/approve`);
};

const rejectRegistration = (reg: Registration) => {
    if (
        confirm(
            `Are you sure you want to reject registration for ${reg.racer?.first_name} ${reg.racer?.last_name}?`,
        )
    ) {
        useForm({}).post(`/admin/registrations/${reg.id}/reject`);
    }
};

const quickAssignBib = (reg: Registration) => {
    const nextBib = prompt(
        `Assign Bib Number for ${reg.racer?.first_name} ${reg.racer?.last_name}:`,
        reg.racer?.bib_number || '',
    );

    if (nextBib !== null) {
        useForm({
            category_ids: (reg.categories || []).map((c) => c.id),
            event_id: reg.event_id || null,
            season_year: reg.season_year,
            fee_type: reg.fee_type,
            payment_method: reg.payment_method || 'cash',
            amount_paid: reg.amount_paid,
            is_season_pass: reg.is_season_pass,
            status: reg.status,
            bib_number: nextBib,
            team_id: reg.racer?.team_id || null,
        }).put(`/admin/registrations/${reg.id}`);
    }
};

const deleteRegistration = (id: number) => {
    if (confirm('Are you sure you want to delete this registration record?')) {
        useForm({}).delete(`/admin/registrations/${id}`);
    }
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Racer Registrations', href: '/admin/registrations' },
        ]"
    >
        <div class="w-full p-6">
            <div
                class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="text-2xl font-bold text-slate-900 dark:text-slate-100"
                    >
                        Racer Registration Records
                    </h1>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                        Manage multi-year registrations, payment methods (Cash,
                        Venmo, Check, Card), team assignments, and quick bib
                        numbers.
                    </p>
                </div>

                <!-- Actions & Year Filter -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label
                            class="flex items-center gap-1 text-xs font-semibold text-slate-600 dark:text-slate-400"
                        >
                            <Filter class="h-3.5 w-3.5" /> Season Year:
                        </label>
                        <select
                            v-model="selectedYear"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="all">All Years</option>
                            <option
                                v-for="yr in availableYears"
                                :key="yr"
                                :value="yr"
                            >
                                {{ yr }} Season
                            </option>
                        </select>
                    </div>

                    <button
                        @click="openCreateModal"
                        type="button"
                        class="flex items-center gap-1.5 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Register New Racer</span>
                    </button>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
                    >
                        <tr>
                            <th class="px-6 py-3.5">Season Year</th>
                            <th class="px-6 py-3.5">Racer Name</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Team / Club</th>
                            <th class="px-6 py-3.5">Bib #</th>
                            <th class="px-6 py-3.5">Category</th>
                            <th class="px-6 py-3.5">Scope</th>
                            <th class="px-6 py-3.5">Fee Type</th>
                            <th class="px-6 py-3.5">How Paid</th>
                            <th class="px-6 py-3.5 text-right">Amount</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
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
                            <td
                                class="px-6 py-4 font-mono font-bold text-amber-600 dark:text-amber-400"
                            >
                                {{ r.season_year || 2026 }}
                            </td>
                            <td
                                class="px-6 py-4 font-bold text-slate-900 dark:text-white"
                            >
                                {{ r.racer?.first_name }}
                                {{ r.racer?.last_name }}
                                <div
                                    class="text-[10px] font-normal text-slate-500 dark:text-slate-400"
                                >
                                    {{ r.racer?.email }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    v-if="r.status === 'approved'"
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400"
                                >
                                    <CheckCircle2 class="h-3 w-3" />
                                    Approved
                                </span>
                                <span
                                    v-else-if="r.status === 'pending'"
                                    class="inline-flex items-center gap-1 rounded-full border border-amber-500/30 bg-amber-500/20 px-2.5 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300"
                                >
                                    <Clock class="h-3 w-3 animate-pulse" />
                                    Pending Approval
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full border border-rose-500/20 bg-rose-500/10 px-2.5 py-0.5 text-[10px] font-bold text-rose-600 dark:text-rose-400"
                                >
                                    <XCircle class="h-3 w-3" />
                                    Rejected
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    v-if="r.racer?.team"
                                    class="inline-flex items-center gap-1 rounded-md border border-amber-500/20 bg-amber-500/10 px-2.5 py-0.5 text-[11px] font-bold text-amber-600 dark:text-amber-400"
                                >
                                    <Shield class="h-3 w-3 text-amber-500" />
                                    {{ r.racer.team.name }}
                                </span>
                                <span v-else class="text-slate-400 italic"
                                    >Independent</span
                                >
                            </td>
                            <td class="px-6 py-4 font-mono font-bold">
                                <button
                                    @click="quickAssignBib(r)"
                                    class="inline-flex items-center gap-1.5 rounded bg-slate-100 px-2.5 py-1 text-xs font-bold text-amber-600 transition-all hover:bg-amber-500/20 dark:bg-slate-800 dark:text-amber-400"
                                    title="Click to assign or edit Bib #"
                                >
                                    <Hash class="h-3.5 w-3.5" />
                                    <span>{{
                                        r.racer?.bib_number
                                            ? r.racer.bib_number
                                            : 'Assign Bib'
                                    }}</span>
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="font-medium text-slate-900 dark:text-slate-100"
                                    >{{
                                        (r.categories || []).map((c) => c.name).join(', ') || 'None'
                                    }}</span
                                >
                                <span
                                    class="block font-mono text-[10px] text-slate-500 dark:text-slate-400"
                                    >Wave {{
                                        Array.from(new Set((r.categories || []).map((c) => c.wave))).join(', ') || 'N/A'
                                    }}</span
                                >
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    v-if="r.is_season_pass"
                                    class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400"
                                >
                                    Full Season Pass
                                </span>
                                <span
                                    v-else
                                    class="text-slate-700 dark:text-slate-300"
                                >
                                    {{ r.event?.name || 'Single Event' }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-slate-500 capitalize dark:text-slate-400"
                            >
                                {{ r.fee_type }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="rounded border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-700 uppercase dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ r.payment_method || 'cash' }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400"
                            >
                                ${{ Number(r.amount_paid).toFixed(2) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <button
                                        v-if="r.status !== 'approved'"
                                        @click="approveRegistration(r)"
                                        class="rounded bg-emerald-50 px-2 py-1 text-[11px] font-extrabold text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-400"
                                        title="Approve Registration"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        v-if="r.status === 'pending'"
                                        @click="rejectRegistration(r)"
                                        class="rounded bg-rose-50 px-2 py-1 text-[11px] font-extrabold text-rose-600 hover:bg-rose-100 dark:bg-rose-950/50 dark:text-rose-400"
                                        title="Reject Registration"
                                    >
                                        Reject
                                    </button>
                                    <button
                                        @click="openEditModal(r)"
                                        class="rounded bg-slate-100 p-1.5 text-amber-600 hover:bg-amber-50 dark:bg-slate-800 dark:text-amber-400 dark:hover:bg-slate-700"
                                        title="Edit Registration"
                                    >
                                        <Edit3 class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteRegistration(r.id)"
                                        class="rounded bg-slate-100 p-1.5 text-rose-600 hover:bg-rose-50 dark:bg-slate-800 dark:text-rose-400 dark:hover:bg-rose-950"
                                        title="Delete Registration"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Edit Registration Modal -->
            <div
                v-if="editingRegistration"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-lg space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800"
                    >
                        <h2
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Edit Registration:
                            {{ editingRegistration.racer?.first_name }}
                            {{ editingRegistration.racer?.last_name }}
                        </h2>
                        <button
                            @click="closeEditModal"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-white"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form
                        @submit.prevent="updateRegistration"
                        class="space-y-4 text-xs"
                    >
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Season Year</label
                                >
                                <input
                                    v-model="editForm.season_year"
                                    type="number"
                                    required
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Bib Number</label
                                >
                                <input
                                    v-model="editForm.bib_number"
                                    type="text"
                                    placeholder="e.g. 104"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Approval Status</label
                                >
                                <select
                                    v-model="editForm.status"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                >
                                    <option value="approved">Approved</option>
                                    <option value="pending">Pending</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>

                        <!-- Team Selection -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Team / Club</label
                                >
                                <select
                                    v-model="editForm.team_id"
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
                                    v-model="editForm.new_team_name"
                                    type="text"
                                    placeholder="Type new team name..."
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Race Categories *</label
                            >
                            <div
                                class="grid max-h-48 grid-cols-1 gap-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <label
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white p-2 text-xs font-semibold text-slate-900 transition-colors hover:border-amber-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                >
                                    <input
                                        type="checkbox"
                                        :value="cat.id"
                                        v-model="editForm.category_ids"
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

                        <div class="flex items-center gap-2 pt-2">
                            <input
                                type="checkbox"
                                v-model="editForm.is_season_pass"
                                id="modal_season_pass"
                                class="rounded border-slate-300 bg-slate-50 text-amber-500 dark:border-slate-800 dark:bg-slate-950"
                            />
                            <label
                                for="modal_season_pass"
                                class="font-medium text-slate-700 dark:text-slate-300"
                                >Full Season Pass</label
                            >
                        </div>

                        <div v-if="!editForm.is_season_pass">
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Single Event</label
                            >
                            <select
                                v-model="editForm.event_id"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            >
                                <option
                                    v-for="evt in events"
                                    :key="evt.id"
                                    :value="evt.id"
                                >
                                    {{ evt.name }}
                                </option>
                            </select>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Fee Type</label
                                >
                                <select
                                    v-model="editForm.fee_type"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                >
                                    <option value="season">season ($70)</option>
                                    <option value="race">race ($35)</option>
                                    <option value="youth">youth ($20)</option>
                                    <option value="bc">bc (free)</option>
                                    <option value="kids">kids (free)</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >How Paid</label
                                >
                                <select
                                    v-model="editForm.payment_method"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="venmo">Venmo</option>
                                    <option value="check">Check</option>
                                    <option value="card">Card / Online</option>
                                    <option value="free">Free Entry</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Amount Paid ($)</label
                                >
                                <input
                                    v-model="editForm.amount_paid"
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
                                @click="closeEditModal"
                                class="rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="rounded-lg bg-amber-500 px-5 py-2 font-bold text-slate-950 shadow-sm hover:bg-amber-400"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- New Registration Modal -->
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-950/60 p-4 backdrop-blur-xs"
            >
                <div
                    class="my-8 w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                    >
                        <h3
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            Register New Racer
                        </h3>
                        <button
                            @click="closeCreateModal"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form
                        @submit.prevent="submitCreateRegistration"
                        class="space-y-4 text-xs"
                    >
                        <!-- Racer Form Fields with smart match on First/Last name -->
                        <div
                            v-if="createForm.racer_option === 'existing'"
                            class="flex items-center justify-between rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs text-emerald-700 dark:text-emerald-300"
                        >
                            <span class="font-bold">
                                Linked to Existing Racer: {{ createForm.first_name }} {{ createForm.last_name }}
                            </span>
                            <button
                                type="button"
                                @click="createForm.racer_option = 'new'; createForm.racer_id = null;"
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
                                    v-model="createForm.first_name"
                                    @input="onCreateNameInput"
                                    @focus="isCreateRacerDropdownOpen = true"
                                    type="text"
                                    required
                                    placeholder="Jane"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />

                                <!-- Live Existing Racer Suggestions Dropdown -->
                                <div
                                    v-if="isCreateRacerDropdownOpen && createMatchingRacers.length > 0 && createForm.racer_option !== 'existing'"
                                    class="absolute top-full right-0 left-0 z-30 mt-1 max-h-48 divide-y divide-slate-100 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900"
                                >
                                    <div class="bg-slate-50 px-3 py-1.5 text-[10px] font-bold text-amber-600 uppercase dark:bg-slate-950 dark:text-amber-400">
                                        ⚡ Existing Racer Match Found:
                                    </div>
                                    <button
                                        v-for="r in createMatchingRacers"
                                        :key="r.id"
                                        type="button"
                                        @click="selectExistingRacerForCreate(r)"
                                        class="flex w-full items-center justify-between px-3.5 py-2 text-left text-xs transition-colors hover:bg-amber-500/10"
                                    >
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white">
                                                {{ r.first_name }} {{ r.last_name }}
                                            </span>
                                            <span v-if="r.email" class="block text-[10px] text-slate-500 dark:text-slate-400">
                                                {{ r.email }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 font-mono text-[11px]">
                                            <span v-if="r.team" class="text-slate-500 dark:text-slate-400">
                                                {{ r.team.name }}
                                            </span>
                                            <span v-if="r.bib_number" class="rounded bg-amber-500/20 px-1.5 py-0.5 font-bold text-amber-700 dark:text-amber-300">
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
                                    v-model="createForm.last_name"
                                    @input="onCreateNameInput"
                                    @focus="isCreateRacerDropdownOpen = true"
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
                                    >Email Address</label
                                >
                                <input
                                    v-model="createForm.email"
                                    type="email"
                                    placeholder="racer@example.com"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Phone Number</label
                                >
                                <input
                                    v-model="createForm.phone"
                                    type="text"
                                    placeholder="(555) 000-0000"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Racer Bib & Team Selection -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Assigned Bib Number</label
                                >
                                <input
                                    v-model="createForm.bib_number"
                                    type="text"
                                    placeholder="e.g. 104"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                            <div class="hidden sm:block"></div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Team / Club Name</label
                                >
                                <select
                                    v-model="createForm.team_id"
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
                                    v-model="createForm.new_team_name"
                                    type="text"
                                    placeholder="Type new team name..."
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- 1. Wave selection -->
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold tracking-wider text-amber-600 uppercase dark:text-amber-400"
                            >
                                1. Participating Wave(s) *
                            </label>
                            <div
                                class="flex flex-wrap items-center gap-3 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3"
                            >
                                <label
                                    v-for="w in availableWaves"
                                    :key="'create-wave-' + w"
                                    class="flex cursor-pointer items-center gap-1.5 text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    <input
                                        type="checkbox"
                                        :value="w"
                                        :checked="createForm.waves.includes(w)"
                                        @change="toggleWaveForCreate(w)"
                                        class="rounded text-amber-500 focus:ring-amber-500"
                                    />
                                    <span>Wave {{ w }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Category selection -->
                        <div>
                            <label
                                class="mb-1 block text-xs font-bold tracking-wider text-slate-700 uppercase dark:text-slate-300"
                            >
                                2. Select Race Category / Categories *
                            </label>
                            <div
                                class="grid max-h-40 grid-cols-1 gap-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <label
                                    v-for="cat in createAvailableCategories"
                                    :key="'create-cat-' + cat.id"
                                    class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white p-2 text-xs font-semibold text-slate-900 transition-colors hover:border-amber-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                >
                                    <input
                                        type="checkbox"
                                        :value="cat.id"
                                        v-model="createForm.category_ids"
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

                        <!-- Registration Scope & Event -->
                        <div
                            class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="createForm.is_season_pass"
                                    id="create_season_pass"
                                    class="rounded border-slate-300 text-amber-500 focus:ring-amber-500"
                                />
                                <label
                                    for="create_season_pass"
                                    class="font-bold text-slate-900 dark:text-white"
                                >
                                    Full Season Pass Registration
                                </label>
                            </div>

                            <div v-if="!createForm.is_season_pass">
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Single Event *</label
                                >
                                <select
                                    v-model="createForm.event_id"
                                    required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                >
                                    <option
                                        v-for="evt in events"
                                        :key="evt.id"
                                        :value="evt.id"
                                    >
                                        {{ evt.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Payment & Fees -->
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Fee Type</label
                                >
                                <select
                                    v-model="createForm.fee_type"
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
                                    <option value="kids">Kids (Free)</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Payment Method</label
                                >
                                <select
                                    v-model="createForm.payment_method"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="venmo">Venmo</option>
                                    <option value="check">Check</option>
                                    <option value="card">Card / Online</option>
                                    <option value="free">Free Entry</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Amount Paid ($)</label
                                >
                                <input
                                    v-model="createForm.amount_paid"
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
                                @click="closeCreateModal"
                                class="rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="createForm.processing"
                                class="rounded-lg bg-amber-500 px-5 py-2 font-bold text-slate-950 shadow-sm hover:bg-amber-400"
                            >
                                Save Registration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
