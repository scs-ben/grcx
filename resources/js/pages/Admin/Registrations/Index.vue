<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Edit3, Trash2, Filter, X, Hash, Shield } from '@lucide/vue';
import { ref, computed } from 'vue';

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

interface Registration {
    id: number;
    fee_type: string;
    payment_method: string;
    amount_paid: number;
    is_season_pass: boolean;
    season_year: number;
    created_at: string;
    category_id: number;
    event_id?: number;
    racer: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        phone: string;
        bib_number: string;
        team_id?: number;
        team?: Team;
    };
    event?: Event;
    category: Category;
}

const props = defineProps<{
    registrations: Registration[];
    events: Event[];
    categories: Category[];
    teams: Team[];
    availableYears: number[];
}>();

const selectedYear = ref<string>('all');

const filteredRegistrations = computed(() => {
    if (selectedYear.value === 'all') return props.registrations;
    return props.registrations.filter((r) => r.season_year === Number(selectedYear.value));
});

// Edit Modal State
const editingRegistration = ref<Registration | null>(null);

const editForm = useForm({
    category_id: 1,
    event_id: null as number | null,
    season_year: 2026,
    fee_type: 'race',
    payment_method: 'cash',
    amount_paid: 35.00,
    is_season_pass: false,
    bib_number: '',
    team_id: null as number | null,
    new_team_name: '',
});

const openEditModal = (reg: Registration) => {
    editingRegistration.value = reg;
    editForm.category_id = reg.category_id;
    editForm.event_id = reg.event_id || null;
    editForm.season_year = reg.season_year || 2026;
    editForm.fee_type = reg.fee_type;
    editForm.payment_method = reg.payment_method || 'cash';
    editForm.amount_paid = reg.amount_paid;
    editForm.is_season_pass = reg.is_season_pass;
    editForm.bib_number = reg.racer?.bib_number || '';
    editForm.team_id = reg.racer?.team_id || null;
    editForm.new_team_name = '';
};

const closeEditModal = () => {
    editingRegistration.value = null;
};

const updateRegistration = () => {
    if (!editingRegistration.value) return;
    editForm.put(`/admin/registrations/${editingRegistration.value.id}`, {
        onSuccess: () => closeEditModal(),
    });
};

const quickAssignBib = (reg: Registration) => {
    const nextBib = prompt(`Assign Bib Number for ${reg.racer?.first_name} ${reg.racer?.last_name}:`, reg.racer?.bib_number || '');
    if (nextBib !== null) {
        useForm({
            category_id: reg.category_id,
            event_id: reg.event_id || null,
            season_year: reg.season_year,
            fee_type: reg.fee_type,
            payment_method: reg.payment_method || 'cash',
            amount_paid: reg.amount_paid,
            is_season_pass: reg.is_season_pass,
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
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: 'Racer Registrations', href: '/admin/registrations' }]">
        <div class="p-6 w-full">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Racer Registration Records</h1>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Manage multi-year registrations, payment methods (Cash, Venmo, Check, Card), team assignments, and quick bib numbers.</p>
                </div>

                <!-- Year Filter -->
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1">
                        <Filter class="w-3.5 h-3.5" /> Season Year:
                    </label>
                    <select v-model="selectedYear" class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-1.5 text-xs text-slate-900 dark:text-white focus:outline-none">
                        <option value="all">All Years</option>
                        <option v-for="yr in availableYears" :key="yr" :value="yr">{{ yr }} Season</option>
                    </select>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Season Year</th>
                            <th class="px-6 py-3.5">Racer Name</th>
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
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                        <tr v-for="r in filteredRegistrations" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-6 py-4 font-mono font-bold text-amber-600 dark:text-amber-400">
                                {{ r.season_year || 2026 }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                {{ r.racer?.first_name }} {{ r.racer?.last_name }}
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">{{ r.racer?.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="r.racer?.team" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold border border-amber-500/20 text-[11px]">
                                    <Shield class="w-3 h-3 text-amber-500" />
                                    {{ r.racer.team.name }}
                                </span>
                                <span v-else class="text-slate-400 italic">Independent</span>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold">
                                <button @click="quickAssignBib(r)" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-bold transition-all" title="Click to assign or edit Bib #">
                                    <Hash class="w-3.5 h-3.5" />
                                    <span>{{ r.racer?.bib_number ? r.racer.bib_number : 'Assign Bib' }}</span>
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-900 dark:text-slate-100">{{ r.category?.name }}</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-mono">Wave {{ r.category?.wave }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="r.is_season_pass" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    Full Season Pass
                                </span>
                                <span v-else class="text-slate-700 dark:text-slate-300">
                                    {{ r.event?.name || 'Single Event' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 capitalize text-slate-500 dark:text-slate-400">{{ r.fee_type }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ r.payment_method || 'cash' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400">${{ Number(r.amount_paid).toFixed(2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEditModal(r)" class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-700" title="Edit Registration">
                                        <Edit3 class="w-4 h-4" />
                                    </button>
                                    <button @click="deleteRegistration(r.id)" class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950" title="Delete Registration">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Edit Registration Modal -->
            <div v-if="editingRegistration" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                            Edit Registration: {{ editingRegistration.racer?.first_name }} {{ editingRegistration.racer?.last_name }}
                        </h2>
                        <button @click="closeEditModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="updateRegistration" class="space-y-4 text-xs">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Season Year</label>
                                <input v-model="editForm.season_year" type="number" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bib Number</label>
                                <input v-model="editForm.bib_number" type="text" placeholder="e.g. 104" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                            </div>
                        </div>

                        <!-- Team Selection -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Team / Club</label>
                                <select v-model="editForm.team_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none">
                                    <option :value="null">Independent (No Team)</option>
                                    <option v-for="t in teams" :key="t.id" :value="t.id">
                                        {{ t.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Or Add New Team</label>
                                <input v-model="editForm.new_team_name" type="text" placeholder="Type new team name..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Race Category</label>
                            <select v-model="editForm.category_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none">
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }} (Wave {{ cat.wave }})
                                </option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" v-model="editForm.is_season_pass" id="modal_season_pass" class="rounded text-amber-500 bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800" />
                            <label for="modal_season_pass" class="font-medium text-slate-700 dark:text-slate-300">Full Season Pass</label>
                        </div>

                        <div v-if="!editForm.is_season_pass">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Single Event</label>
                            <select v-model="editForm.event_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none">
                                <option v-for="evt in events" :key="evt.id" :value="evt.id">
                                    {{ evt.name }}
                                </option>
                            </select>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Fee Type</label>
                                <select v-model="editForm.fee_type" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none">
                                    <option value="season">season ($70)</option>
                                    <option value="race">race ($35)</option>
                                    <option value="youth">youth ($20)</option>
                                    <option value="bc">bc (free)</option>
                                    <option value="kids">kids (free)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">How Paid</label>
                                <select v-model="editForm.payment_method" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none">
                                    <option value="cash">Cash</option>
                                    <option value="venmo">Venmo</option>
                                    <option value="check">Check</option>
                                    <option value="card">Card / Online</option>
                                    <option value="free">Free Entry</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Amount Paid ($)</label>
                                <input v-model="editForm.amount_paid" type="number" step="0.01" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                            <button type="button" @click="closeEditModal" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                                Cancel
                            </button>
                            <button type="submit" :disabled="editForm.processing" class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold shadow-sm">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
