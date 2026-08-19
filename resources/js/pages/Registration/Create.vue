<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Bike } from '@lucide/vue';
import { computed } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

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
    if (form.is_season_pass) {
        return '$70 (Season Pass)';
    }

    if (form.fee_type === 'youth') {
        return '$20 (Youth <18)';
    }

    if (form.fee_type === 'race') {
        return '$35 (Single Race)';
    }

    return 'FREE (BC / Kids / Costume)';
});

const submit = () => {
    form.post('/register-race');
};
</script>

<template>
    <PublicLayout>
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mb-6 flex items-center gap-3 border-b border-slate-200 pb-6 dark:border-slate-800"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 font-bold text-amber-600 dark:text-amber-400"
                    >
                        <Bike class="h-5 w-5" />
                    </div>
                    <div>
                        <h1
                            class="text-2xl font-black text-slate-900 dark:text-white"
                        >
                            Race Registration 2026
                        </h1>
                        <p
                            class="mt-0.5 text-xs text-slate-600 dark:text-slate-400"
                        >
                            Register for single events or the entire 3-race
                            season pass.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Personal Info -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >First Name *</label
                            >
                            <input
                                v-model="form.first_name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >Last Name *</label
                            >
                            <input
                                v-model="form.last_name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >Email Address *</label
                            >
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >Phone Number</label
                            >
                            <input
                                v-model="form.phone"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >Date of Birth</label
                            >
                            <input
                                v-model="form.date_of_birth"
                                type="date"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >Gender Identity</label
                            >
                            <select
                                v-model="form.gender"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            >
                                <option value="Open">Open</option>
                                <option value="Womens">Womens / Girls</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >Bib Number (Optional)</label
                            >
                            <input
                                v-model="form.bib_number"
                                type="text"
                                placeholder="Assigned bib"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Team Selection & New Team Add -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >Team / Club Name</label
                            >
                            <select
                                v-model="form.team_id"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
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
                                class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                                >Or Add New Team</label
                            >
                            <input
                                v-model="form.new_team_name"
                                type="text"
                                placeholder="Type new team name..."
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Registration Type -->
                    <div
                        class="border-t border-slate-200 pt-6 dark:border-slate-800"
                    >
                        <label
                            class="mb-3 block text-sm font-bold text-slate-900 dark:text-white"
                            >Registration Scope</label
                        >
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label
                                class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition-all"
                                :class="
                                    form.is_season_pass
                                        ? 'border-amber-500 bg-amber-500/10'
                                        : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950'
                                "
                            >
                                <input
                                    type="radio"
                                    :value="true"
                                    v-model="form.is_season_pass"
                                    class="text-amber-500"
                                />
                                <div>
                                    <div
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        Full Season Pass ($70)
                                    </div>
                                    <div
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Includes all 3 series events
                                    </div>
                                </div>
                            </label>

                            <label
                                class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition-all"
                                :class="
                                    !form.is_season_pass
                                        ? 'border-amber-500 bg-amber-500/10'
                                        : 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950'
                                "
                            >
                                <input
                                    type="radio"
                                    :value="false"
                                    v-model="form.is_season_pass"
                                    class="text-amber-500"
                                />
                                <div>
                                    <div
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        Single Event Entry
                                    </div>
                                    <div
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        Select specific race day
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Event Select (if single event) -->
                    <div v-if="!form.is_season_pass">
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                            >Select Event</label
                        >
                        <select
                            v-model="form.event_id"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
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

                    <!-- Category Selection (Multi-Category Supported!) -->
                    <div>
                        <label
                            class="mb-1 block text-sm font-bold text-slate-900 dark:text-white"
                            >Race Categories * (Select all that apply)</label
                        >
                        <p
                            class="mb-3 text-xs text-slate-600 dark:text-slate-400"
                        >
                            You can enter a primary race category plus secondary
                            races (e.g. BC or Single Speed).
                        </p>

                        <div
                            class="grid max-h-60 grid-cols-1 gap-3 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <label
                                v-for="cat in categories"
                                :key="cat.id"
                                class="flex cursor-pointer items-center gap-2 rounded-lg p-2 text-xs hover:bg-slate-200/60 dark:hover:bg-slate-900"
                            >
                                <input
                                    type="checkbox"
                                    :value="cat.id"
                                    v-model="form.category_ids"
                                    class="rounded border-slate-300 bg-white text-amber-500 dark:border-slate-700 dark:bg-slate-900"
                                />
                                <span
                                    class="font-medium text-slate-900 dark:text-white"
                                    >{{ cat.name }}</span
                                >
                                <span
                                    class="font-mono text-[10px] text-slate-500 dark:text-slate-400"
                                    >({{ cat.wave }})</span
                                >
                            </label>
                        </div>
                    </div>

                    <!-- Summary & Payment Method -->
                    <div
                        class="flex flex-col items-start justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center dark:border-slate-800 dark:bg-slate-950"
                    >
                        <div>
                            <span
                                class="block text-xs text-slate-500 dark:text-slate-400"
                                >Total Due at Race Check-In</span
                            >
                            <span
                                class="text-lg font-black text-amber-600 dark:text-amber-400"
                                >{{ calculatedFee }}</span
                            >
                        </div>
                        <button
                            type="submit"
                            :disabled="
                                form.processing ||
                                form.category_ids.length === 0
                            "
                            class="rounded-xl bg-amber-500 px-6 py-3 text-sm font-extrabold text-slate-950 shadow-md transition-all hover:bg-amber-400 disabled:opacity-50"
                        >
                            Complete Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>
