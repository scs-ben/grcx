<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { UserCheck, Plus, CheckCircle2, Search, X } from '@lucide/vue';
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

interface Racer {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    bib_number: string;
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
    category: Category;
    event?: Event;
}

const props = defineProps<{
    events: Event[];
    selectedEventId: number;
    registrations: Registration[];
    categories: Category[];
    racers: Racer[];
    waves?: string[];
}>();

const availableWaves = computed(() => props.waves || ['C', 'A', 'B', 'Kids']);

const searchQuery = ref('');
const selectedEvent = ref(props.selectedEventId);

const onEventChange = () => {
    router.get('/admin/check-in', { event_id: selectedEvent.value }, { preserveState: true });
};

const filteredRegistrations = computed(() => {
    if (!searchQuery.value.trim()) return props.registrations;
    const q = searchQuery.value.toLowerCase();
    return props.registrations.filter((r) => {
        const name = `${r.racer?.first_name} ${r.racer?.last_name}`.toLowerCase();
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
});

const openCheckIn = (reg: Registration) => {
    checkingInReg.value = reg;
    checkInForm.clothespin_number = reg.clothespin_number || '';
    checkInForm.bib_number = reg.racer?.bib_number || '';
    // Pre-select registration category wave
    const initialWave = reg.category?.wave || 'C';
    checkInForm.waves = [initialWave];
};

const closeCheckIn = () => {
    checkingInReg.value = null;
};

const submitCheckIn = () => {
    if (!checkingInReg.value) return;
    checkInForm.post(`/admin/check-in/${checkingInReg.value.id}`, {
        onSuccess: () => closeCheckIn(),
    });
};

// Day-Of Registration Modal
const showDayOfModal = ref(false);

const dayOfForm = useForm({
    event_id: props.selectedEventId,
    first_name: '',
    last_name: '',
    email: '',
    bib_number: '',
    clothespin_number: '',
    category_id: props.categories[0]?.id || 1,
    fee_type: 'race',
    payment_method: 'cash',
    amount_paid: 35.00,
    waves: ['C'] as string[],
});

const submitDayOf = () => {
    dayOfForm.event_id = selectedEvent.value;
    dayOfForm.post('/admin/check-in/day-of', {
        onSuccess: () => {
            showDayOfModal.value = false;
            dayOfForm.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: 'Day-Of Race Check-In', href: '/admin/check-in' }]">
        <div class="p-6 w-full space-y-6">
            <!-- Header & Event Selector -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <UserCheck class="w-6 h-6 text-amber-500" />
                        Race Day Check-In & Clothespin Station
                    </h1>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Check in pre-registered racers, assign clothespin numbers for live timing finish order, or add new day-of entries.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Active Event:</label>
                        <select v-model="selectedEvent" @change="onEventChange" class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 dark:text-white focus:outline-none shadow-sm">
                            <option v-for="evt in events" :key="evt.id" :value="evt.id">
                                {{ evt.name }} ({{ evt.formatted_date || evt.event_date }})
                            </option>
                        </select>
                    </div>

                    <button @click="showDayOfModal = true" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-sm transition-all">
                        <Plus class="w-4 h-4" /> Add Day-Of Racer
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative max-w-md">
                <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                <input v-model="searchQuery" type="text" placeholder="Search pre-registrations by racer name, bib #, or clothespin #..." class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none shadow-sm" />
            </div>

            <!-- Pre-registered Racers Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Racer Name</th>
                            <th class="px-6 py-3.5">Bib #</th>
                            <th class="px-6 py-3.5">Clothespin #</th>
                            <th class="px-6 py-3.5">Category</th>
                            <th class="px-6 py-3.5">Fee & Payment</th>
                            <th class="px-6 py-3.5 text-right">Check-In Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                        <tr v-for="r in filteredRegistrations" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-6 py-4">
                                <span v-if="r.is_checked_in" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    <CheckCircle2 class="w-3 h-3" /> Checked In
                                </span>
                                <span v-else class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    Pre-Registered
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                {{ r.racer?.first_name }} {{ r.racer?.last_name }}
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">{{ r.racer?.email || 'No email' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                #{{ r.racer?.bib_number || 'Unassigned' }}
                            </td>
                            <td class="px-6 py-4 font-mono font-black text-amber-600 dark:text-amber-400 text-sm">
                                {{ r.clothespin_number ? 'Pin #' + r.clothespin_number : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-900 dark:text-slate-100">{{ r.category?.name }}</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-mono">Wave {{ r.category?.wave }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="capitalize font-semibold text-slate-700 dark:text-slate-300">{{ r.fee_type }} (${{ Number(r.amount_paid).toFixed(2) }})</div>
                                <div class="text-[10px] uppercase font-mono text-slate-500 dark:text-slate-400">Paid: {{ r.payment_method || 'cash' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openCheckIn(r)" class="px-3 py-1.5 rounded-lg font-bold text-xs shadow-sm transition-all" :class="r.is_checked_in ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-950'">
                                    {{ r.is_checked_in ? 'Edit Clothespin' : 'Check In & Assign Pin' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Check-In Modal -->
            <div v-if="checkingInReg" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                            Check In: {{ checkingInReg.racer?.first_name }} {{ checkingInReg.racer?.last_name }}
                        </h2>
                        <button @click="closeCheckIn" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitCheckIn" class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-900 dark:text-white mb-1">Clothespin Number *</label>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px] mb-1.5">Enter the clothespin bib number handed to the racer at check-in table.</p>
                            <input v-model="checkInForm.clothespin_number" type="text" placeholder="e.g. 42" required autofocus class="w-full bg-amber-500/10 border border-amber-500/40 rounded-xl px-4 py-3 text-lg font-black text-amber-600 dark:text-amber-400 focus:outline-none text-center" />
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Racer Wave(s) Participating Today *</label>
                            <div class="flex flex-wrap items-center gap-3 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                                <label v-for="w in availableWaves" :key="w" class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white cursor-pointer">
                                    <input type="checkbox" :value="w" v-model="checkInForm.waves" class="rounded text-amber-500 focus:ring-amber-500" />
                                    <span>Wave {{ w }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Racer Bib Number (Optional)</label>
                            <input v-model="checkInForm.bib_number" type="text" placeholder="e.g. 104" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                            <button type="button" @click="closeCheckIn" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                                Cancel
                            </button>
                            <button type="submit" :disabled="checkInForm.processing" class="px-5 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black shadow-sm">
                                Confirm Check-In
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Day-Of Registration Modal -->
            <div v-if="showDayOfModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Add Day-Of Racer & Check-In</h2>
                        <button @click="showDayOfModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitDayOf" class="space-y-4 text-xs">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                                <input v-model="dayOfForm.first_name" type="text" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Last Name *</label>
                                <input v-model="dayOfForm.last_name" type="text" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Email (Optional)</label>
                                <input v-model="dayOfForm.email" type="email" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bib Number (Optional)</label>
                                <input v-model="dayOfForm.bib_number" type="text" placeholder="Assigned bib #" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-amber-600 dark:text-amber-400 mb-1">Clothespin Number *</label>
                                <input v-model="dayOfForm.clothespin_number" type="text" placeholder="e.g. 15" required class="w-full bg-amber-500/10 border border-amber-500/40 rounded-lg px-3 py-2 text-sm font-bold text-slate-900 dark:text-white focus:outline-none" />
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Race Category *</label>
                                <select v-model="dayOfForm.category_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none">
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }} (Wave {{ cat.wave }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Racer Wave(s) Participating Today *</label>
                            <div class="flex flex-wrap items-center gap-3 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                                <label v-for="w in availableWaves" :key="w" class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white cursor-pointer">
                                    <input type="checkbox" :value="w" v-model="dayOfForm.waves" class="rounded text-amber-500 focus:ring-amber-500" />
                                    <span>Wave {{ w }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Fee Type</label>
                                <select v-model="dayOfForm.fee_type" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none">
                                    <option value="race">Single Race ($35)</option>
                                    <option value="youth">Youth ($20)</option>
                                    <option value="season">Season Pass ($70)</option>
                                    <option value="bc">BC (Free)</option>
                                    <option value="kids">Kids / Costume (Free)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">How Paid</label>
                                <select v-model="dayOfForm.payment_method" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none">
                                    <option value="cash">Cash</option>
                                    <option value="venmo">Venmo</option>
                                    <option value="check">Check</option>
                                    <option value="card">Card</option>
                                    <option value="free">Free</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Amount Paid ($)</label>
                                <input v-model="dayOfForm.amount_paid" type="number" step="0.01" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3 py-2 text-slate-900 dark:text-white focus:outline-none" />
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                            <button type="button" @click="showDayOfModal = false" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                                Cancel
                            </button>
                            <button type="submit" :disabled="dayOfForm.processing" class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black shadow-sm">
                                Create & Check In
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
