<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { UserCheck, Bike, Check, AlertCircle } from '@lucide/vue';
import { computed } from 'vue';

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
    duration_description: string;
}

interface Team {
    id: number;
    name: string;
}

const props = defineProps<{
    events: Event[];
    categories: Category[];
    teams: Team[];
}>();

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    date_of_birth: '',
    gender: 'Open',
    bib_number: '',
    team_id: null as number | null,
    new_team_name: '',
    is_season_pass: true,
    event_id: props.events[0]?.id || null,
    category_ids: [] as number[],
    fee_type: 'season',
});

const calculatedFee = computed(() => {
    if (form.is_season_pass) return '$70 (Season Pass)';
    if (form.fee_type === 'youth') return '$20 (Youth <18)';
    if (form.fee_type === 'race') return '$35 (Single Race)';
    return 'FREE (BC / Kids / Costume)';
});

const submit = () => {
    form.post('/register-race');
};
</script>

<template>
    <PublicLayout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-6 pb-6 border-b border-slate-200 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                        <Bike class="w-5 h-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white">Race Registration 2026</h1>
                        <p class="text-slate-600 dark:text-slate-400 text-xs mt-0.5">Register for single events or the entire 3-race season pass.</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Personal Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                            <input v-model="form.first_name" type="text" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Last Name *</label>
                            <input v-model="form.last_name" type="text" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                            <input v-model="form.email" type="email" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                            <input v-model="form.phone" type="text" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Date of Birth</label>
                            <input v-model="form.date_of_birth" type="date" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gender Identity</label>
                            <select v-model="form.gender" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                                <option value="Open">Open</option>
                                <option value="Womens">Womens / Girls</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bib Number (Optional)</label>
                            <input v-model="form.bib_number" type="text" placeholder="Assigned bib" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none" />
                        </div>
                    </div>

                    <!-- Team Selection & New Team Add -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Team / Club Name</label>
                            <select v-model="form.team_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                                <option :value="null">Independent (No Team)</option>
                                <option v-for="t in teams" :key="t.id" :value="t.id">
                                    {{ t.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Or Add New Team</label>
                            <input v-model="form.new_team_name" type="text" placeholder="Type new team name..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none" />
                        </div>
                    </div>

                    <!-- Registration Type -->
                    <div class="border-t border-slate-200 dark:border-slate-800 pt-6">
                        <label class="block text-sm font-bold text-slate-900 dark:text-white mb-3">Registration Scope</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-all" :class="form.is_season_pass ? 'bg-amber-500/10 border-amber-500' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800'">
                                <input type="radio" :value="true" v-model="form.is_season_pass" class="text-amber-500" />
                                <div>
                                    <div class="font-bold text-sm text-slate-900 dark:text-white">Full Season Pass ($70)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Includes all 3 series events</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-all" :class="!form.is_season_pass ? 'bg-amber-500/10 border-amber-500' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800'">
                                <input type="radio" :value="false" v-model="form.is_season_pass" class="text-amber-500" />
                                <div>
                                    <div class="font-bold text-sm text-slate-900 dark:text-white">Single Event Entry</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Select specific race day</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Event Select (if single event) -->
                    <div v-if="!form.is_season_pass">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Event</label>
                        <select v-model="form.event_id" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                            <option v-for="evt in events" :key="evt.id" :value="evt.id">
                                {{ evt.name }} ({{ evt.event_date }})
                            </option>
                        </select>
                    </div>

                    <!-- Category Selection (Multi-Category Supported!) -->
                    <div>
                        <label class="block text-sm font-bold text-slate-900 dark:text-white mb-1">Race Categories * (Select all that apply)</label>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-3">You can enter a primary race category plus secondary races (e.g. BC or Single Speed).</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800">
                            <label v-for="cat in categories" :key="cat.id" class="flex items-center gap-2 text-xs cursor-pointer p-2 rounded-lg hover:bg-slate-200/60 dark:hover:bg-slate-900">
                                <input type="checkbox" :value="cat.id" v-model="form.category_ids" class="rounded text-amber-500 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700" />
                                <span class="font-medium text-slate-900 dark:text-white">{{ cat.name }}</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">({{ cat.wave }})</span>
                            </label>
                        </div>
                    </div>

                    <!-- Summary & Payment Method -->
                    <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block">Total Due at Race Check-In</span>
                            <span class="text-lg font-black text-amber-600 dark:text-amber-400">{{ calculatedFee }}</span>
                        </div>
                        <button type="submit" :disabled="form.processing || form.category_ids.length === 0" class="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm shadow-md transition-all disabled:opacity-50">
                            Complete Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>
