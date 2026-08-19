<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Calendar, Trophy, Zap, ArrowRight } from '@lucide/vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface Event {
    id: number;
    name: string;
    location: string;
    event_date: string;
    formatted_date?: string;
    description: string;
}

defineProps<{
    nextEvent?: Event;
    events: Event[];
}>();
</script>

<template>
    <PublicLayout>
        <!-- Hero Section -->
        <section
            class="relative overflow-hidden border-b border-slate-200 bg-slate-100 py-20 lg:py-28 dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="absolute inset-0 bg-[radial-gradient(#f59e0b_1px,transparent_1px)] [background-size:24px_24px] opacity-10"
            ></div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <div
                        class="mb-6 inline-flex items-center gap-2.5 rounded-full border border-yellow-500/30 bg-yellow-400/20 px-3 py-1.5 text-xs font-black tracking-wider text-yellow-600 uppercase dark:text-yellow-400"
                    >
                        <img
                            src="/images/grcx-logo.png"
                            alt="GRCX Logo"
                            class="h-4 w-4 object-contain"
                        />
                        <span>2026 Cyclocross Season</span>
                    </div>
                    <div class="mb-6 flex items-center gap-4">
                        <div
                            class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl border-2 border-red-600/40 bg-yellow-400 p-2 shadow-xl sm:h-24 sm:w-24"
                        >
                            <img
                                src="/images/grcx-logo.png"
                                alt="GRCX Official Logo"
                                class="h-full w-full object-contain"
                            />
                        </div>
                        <h1
                            class="text-4xl leading-tight font-black tracking-tight text-slate-900 sm:text-6xl dark:text-white"
                        >
                            Grand Rapids <br /><span class="text-yellow-500"
                                >Cyclocross</span
                            >
                            2026
                        </h1>
                    </div>
                    <p
                        class="mb-8 text-lg leading-relaxed text-slate-600 sm:text-xl dark:text-slate-300"
                    >
                        3 epic race venues across Grand Rapids. 12 competitive
                        scoring categories, single speed, BC open categories,
                        youth & costume parades.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <Link
                            href="/register-race"
                            class="flex items-center gap-2 rounded-xl bg-amber-500 px-6 py-3.5 text-base font-extrabold text-slate-950 shadow-lg shadow-amber-500/20 transition-all hover:bg-amber-400"
                        >
                            <span>Register for Races</span>
                            <ArrowRight class="h-5 w-5" />
                        </Link>
                        <Link
                            href="/events"
                            class="rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-base font-bold text-slate-900 transition-all hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:hover:bg-slate-700"
                        >
                            View Race Schedule
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Next Event Highlight -->
        <section
            v-if="nextEvent"
            class="relative z-20 mx-auto -mt-8 max-w-7xl px-4 sm:px-6 lg:px-8"
        >
            <div
                class="flex flex-col items-start justify-between gap-6 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 p-6 text-slate-950 shadow-xl sm:p-8 md:flex-row md:items-center"
            >
                <div>
                    <span
                        class="rounded-full bg-slate-950 px-3 py-1 text-xs font-black tracking-wider text-amber-400 uppercase"
                        >Next Race Day</span
                    >
                    <h2
                        class="mt-2 text-2xl font-black text-slate-950 sm:text-3xl"
                    >
                        {{ nextEvent.name }}
                    </h2>
                    <p
                        class="mt-1 flex items-center gap-2 font-bold text-slate-900"
                    >
                        <Calendar class="h-4 w-4" />
                        {{ nextEvent.formatted_date || nextEvent.event_date }} •
                        {{ nextEvent.location }}
                    </p>
                </div>
                <Link
                    :href="`/events/${nextEvent.id}`"
                    class="flex items-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition-all hover:bg-slate-900"
                >
                    <span>Event & Timing Details</span>
                    <ArrowRight class="h-4 w-4 text-amber-400" />
                </Link>
            </div>
        </section>

        <!-- Quick Info Cards -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400"
                    >
                        <Calendar class="h-6 w-6" />
                    </div>
                    <h3
                        class="mb-2 text-xl font-bold text-slate-900 dark:text-white"
                    >
                        3 Great Venues
                    </h3>
                    <p
                        class="mb-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400"
                    >
                        Highland Park (9/26), Richmond Park (10/10), and the
                        Manhattan Park finale (10/25).
                    </p>
                    <Link
                        href="/events"
                        class="flex items-center gap-1 text-xs font-bold text-amber-600 hover:underline dark:text-amber-400"
                    >
                        View Schedule & Start Times
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400"
                    >
                        <Trophy class="h-6 w-6" />
                    </div>
                    <h3
                        class="mb-2 text-xl font-bold text-slate-900 dark:text-white"
                    >
                        12 Podium Categories
                    </h3>
                    <p
                        class="mb-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400"
                    >
                        Points awarded down to 21st place! Podium recognition
                        and overall season champion titles.
                    </p>
                    <Link
                        href="/standings"
                        class="flex items-center gap-1 text-xs font-bold text-amber-600 hover:underline dark:text-amber-400"
                    >
                        View Leaderboard Standings
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400"
                    >
                        <Zap class="h-6 w-6" />
                    </div>
                    <h3
                        class="mb-2 text-xl font-bold text-slate-900 dark:text-white"
                    >
                        Easy Clothes-Pin Timing
                    </h3>
                    <p
                        class="mb-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400"
                    >
                        Turn in your bib clothes-pin at the finish line for
                        instant live result entry and standings updates.
                    </p>
                    <Link
                        href="/page/rules"
                        class="flex items-center gap-1 text-xs font-bold text-amber-600 hover:underline dark:text-amber-400"
                    >
                        Read Race Rules <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Pricing Summary -->
        <section
            class="border-y border-slate-200 bg-slate-100 py-16 dark:border-slate-800 dark:bg-slate-900/50"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-2xl text-center">
                    <h2
                        class="text-3xl font-extrabold text-slate-900 dark:text-white"
                    >
                        Simple Season Pricing
                    </h2>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Choose single race entry or get the full 3-race season
                        discount.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="mb-1 text-3xl font-black text-amber-600 dark:text-amber-500"
                        >
                            $70
                        </div>
                        <div
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Full Season Pass
                        </div>
                        <p
                            class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                        >
                            All 3 races included
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="mb-1 text-3xl font-black text-slate-900 dark:text-white"
                        >
                            $35
                        </div>
                        <div
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Single Race Entry
                        </div>
                        <p
                            class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Per race day
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="mb-1 text-3xl font-black text-slate-900 dark:text-white"
                        >
                            $20
                        </div>
                        <div
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Youth (&lt; 18)
                        </div>
                        <p
                            class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Per race day
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="mb-1 text-3xl font-black text-emerald-600 dark:text-emerald-400"
                        >
                            FREE
                        </div>
                        <div
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            BC / Kids / Costume
                        </div>
                        <p
                            class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Free second race entry & parade
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
