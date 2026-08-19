<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, Printer, Bike, Calendar } from '@lucide/vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

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
    date_of_birth: string | null;
    gender: string | null;
    bib_number: string | null;
    team: Team | null;
}

interface Category {
    id: number;
    name: string;
    wave: string;
}

interface Event {
    id: number;
    name: string;
    location: string;
    event_date: string;
}

interface Registration {
    id: number;
    fee_type: string;
    amount_paid: number;
    is_season_pass: boolean;
    status: string;
    categories: Category[];
    event: Event | null;
}

const props = defineProps<{
    racer: Racer;
    registrations: Registration[];
}>();

const totalPaid = props.registrations.reduce(
    (sum, r) => sum + Number(r.amount_paid),
    0,
);

const printPage = () => {
    window.print();
};
</script>

<template>
    <PublicLayout>
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Print-only header -->
            <div class="mb-6 hidden items-center gap-2 print:flex">
                <span class="text-xl font-black text-slate-900"
                    >GR CX 2026</span
                >
                <span class="text-sm text-slate-500"
                    >— Registration Confirmation</span
                >
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900 print:border-slate-300 print:shadow-none"
            >
                <!-- Success Header -->
                <div
                    class="mb-6 flex items-center gap-3 border-b border-slate-200 pb-6 dark:border-slate-800"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 print:bg-emerald-100"
                    >
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                    <div>
                        <h1
                            class="text-2xl font-black text-slate-900 dark:text-white"
                        >
                            Registration Confirmed!
                        </h1>
                        <p
                            class="mt-0.5 text-xs text-slate-600 dark:text-slate-400"
                        >
                            Save or print this page for your records. Bring it
                            to race check-in.
                        </p>
                    </div>
                </div>

                <!-- Racer Info -->
                <div class="mb-6">
                    <h2
                        class="mb-3 text-xs font-bold tracking-wider text-amber-600 uppercase dark:text-amber-400"
                    >
                        Racer Information
                    </h2>
                    <div
                        class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-950 print:bg-slate-50"
                    >
                        <div>
                            <span
                                class="block text-[10px] font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Full Name</span
                            >
                            <span
                                class="text-sm font-bold text-slate-900 dark:text-white"
                                >{{ racer.first_name }}
                                {{ racer.last_name }}</span
                            >
                        </div>
                        <div>
                            <span
                                class="block text-[10px] font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Email</span
                            >
                            <span
                                class="text-sm text-slate-900 dark:text-white"
                                >{{ racer.email }}</span
                            >
                        </div>
                        <div v-if="racer.phone">
                            <span
                                class="block text-[10px] font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Phone</span
                            >
                            <span
                                class="text-sm text-slate-900 dark:text-white"
                                >{{ racer.phone }}</span
                            >
                        </div>
                        <div v-if="racer.gender">
                            <span
                                class="block text-[10px] font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Gender</span
                            >
                            <span
                                class="text-sm text-slate-900 dark:text-white"
                                >{{ racer.gender }}</span
                            >
                        </div>
                        <div v-if="racer.bib_number">
                            <span
                                class="block text-[10px] font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Bib Number</span
                            >
                            <span
                                class="font-mono text-sm font-bold text-amber-600 dark:text-amber-400"
                                >#{{ racer.bib_number }}</span
                            >
                        </div>
                        <div v-if="racer.team">
                            <span
                                class="block text-[10px] font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                                >Team</span
                            >
                            <span
                                class="text-sm text-slate-900 dark:text-white"
                                >{{ racer.team.name }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Registered Categories -->
                <div class="mb-6">
                    <h2
                        class="mb-3 text-xs font-bold tracking-wider text-amber-600 uppercase dark:text-amber-400"
                    >
                        Registered Categories
                    </h2>
                    <div class="space-y-3">
                        <div
                            v-for="reg in registrations"
                            :key="reg.id"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950 print:bg-slate-50"
                        >
                            <div
                                class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                            >
                                <div
                                    class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400"
                                >
                                    <span
                                        v-if="reg.event"
                                        class="flex items-center gap-1 font-semibold text-slate-900 dark:text-white"
                                    >
                                        <Calendar
                                            class="h-3.5 w-3.5 text-amber-500"
                                        />
                                        {{ reg.event.name }}
                                    </span>
                                    <span
                                        v-else
                                        class="font-semibold text-amber-600 dark:text-amber-400"
                                    >
                                        Full Season Pass (All 3 Events)
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-[10px] font-bold capitalize"
                                        :class="
                                            reg.status === 'approved'
                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'
                                        "
                                        >{{ reg.status }}</span
                                    >
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        ${{
                                            Number(reg.amount_paid).toFixed(2)
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <div
                                    v-for="cat in reg.categories"
                                    :key="cat.id"
                                    class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-xs dark:border-slate-800 dark:bg-slate-900"
                                >
                                    <div
                                        class="flex h-6 w-6 items-center justify-center rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 print:bg-amber-100"
                                    >
                                        <Bike class="h-3.5 w-3.5" />
                                    </div>
                                    <div>
                                        <span
                                            class="font-bold text-slate-900 dark:text-white"
                                            >{{ cat.name }}</span
                                        >
                                        <span
                                            class="ml-1.5 text-[10px] font-semibold text-slate-500 dark:text-slate-400"
                                            >Wave {{ cat.wave }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total & Payment Summary -->
                <div
                    class="mb-6 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950 print:bg-slate-50"
                >
                    <span
                        class="text-xs font-semibold text-slate-600 dark:text-slate-400"
                        >Total Due at Check-In</span
                    >
                    <span
                        class="text-lg font-black text-amber-600 dark:text-amber-400"
                        >${{ totalPaid.toFixed(2) }}</span
                    >
                </div>

                <!-- Actions -->
                <div
                    class="flex flex-col items-center justify-between gap-4 border-t border-slate-200 pt-6 sm:flex-row dark:border-slate-800 print:hidden"
                >
                    <Link
                        href="/events"
                        class="text-sm font-semibold text-slate-600 transition-colors hover:text-amber-500 dark:text-slate-400"
                    >
                        ← View Upcoming Events
                    </Link>
                    <button
                        @click="printPage"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-6 py-3 text-sm font-extrabold text-slate-950 shadow-md transition-all hover:bg-amber-400"
                    >
                        <Printer class="h-4 w-4" />
                        Print / Save
                    </button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
