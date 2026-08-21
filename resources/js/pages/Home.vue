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
        <!-- Hero Section with Bike Racing Action Photography -->
        <section
            class="relative overflow-hidden border-b border-slate-200 bg-slate-950 py-20 lg:py-32 dark:border-slate-800"
        >
            <!-- Background Cyclocross Action Photography -->
            <div class="absolute inset-0 z-0">
                <img
                    src="/images/hero-cyclocross.jpg"
                    alt="Grand Rapids Cyclocross 2026 Bike Racing Action"
                    class="h-full w-full object-cover object-center opacity-80"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/50 to-slate-950/20 sm:from-slate-950/80 sm:via-slate-950/40 sm:to-transparent"
                ></div>
                <div
                    class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-black/20"
                ></div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <div
                        class="mb-6 inline-flex items-center gap-2.5 rounded-full border border-yellow-500/40 bg-yellow-400/20 px-3.5 py-1.5 text-xs font-black tracking-wider text-yellow-400 uppercase backdrop-blur-xs"
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
                            class="text-4xl leading-tight font-black tracking-tight text-white sm:text-6xl"
                        >
                            Grand Rapids <br /><span class="text-yellow-400"
                                >Cyclocross</span
                            >
                            2026
                        </h1>
                    </div>
                    <p
                        class="mb-8 text-lg leading-relaxed text-slate-200 sm:text-xl"
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
                            class="rounded-xl border border-slate-700 bg-slate-900/80 px-6 py-3.5 text-base font-bold text-white backdrop-blur-xs transition-all hover:bg-slate-800"
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

        <!-- 3 Epic Venues Showcase -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <span
                    class="rounded-full bg-amber-500/10 px-3 py-1 text-xs font-black tracking-wider text-amber-600 uppercase dark:text-amber-400"
                    >West Michigan Courses</span
                >
                <h2
                    class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white"
                >
                    3 Epic Race Venues
                </h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                    Each venue offers distinct terrain, technical obstacles, and an electric festival atmosphere.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <!-- Highland Park -->
                <div
                    class="group flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div>
                        <div class="mb-4">
                            <span class="rounded-md bg-amber-500/10 px-2.5 py-1 text-xs font-black text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                                Stage 1 • Sept 26
                            </span>
                        </div>
                        <h3
                            class="text-xl font-bold text-slate-900 dark:text-white"
                        >
                            Highland Park
                        </h3>
                        <p
                            class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            Opening race featuring deep sand runups, off-camber hill climbs, and fast grass power straightaways.
                        </p>
                    </div>
                    <Link
                        href="/events"
                        class="mt-6 flex items-center gap-1 text-xs font-bold text-amber-600 hover:underline dark:text-amber-400"
                    >
                        View Schedule & Start Times
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <!-- Richmond Park -->
                <div
                    class="group flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div>
                        <div class="mb-4">
                            <span class="rounded-md bg-amber-500/10 px-2.5 py-1 text-xs font-black text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                                Stage 2 • Oct 10
                            </span>
                        </div>
                        <h3
                            class="text-xl font-bold text-slate-900 dark:text-white"
                        >
                            Richmond Park
                        </h3>
                        <p
                            class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            The classic technical course with brutal wooden hurdles, steep stair climbs, and dense autumn forest singletrack.
                        </p>
                    </div>
                    <Link
                        href="/events"
                        class="mt-6 flex items-center gap-1 text-xs font-bold text-amber-600 hover:underline dark:text-amber-400"
                    >
                        View Schedule & Start Times
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>

                <!-- Manhattan Park -->
                <div
                    class="group flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div>
                        <div class="mb-4">
                            <span class="rounded-md bg-amber-500/10 px-2.5 py-1 text-xs font-black text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                                Stage 3 • Oct 25
                            </span>
                        </div>
                        <h3
                            class="text-xl font-bold text-slate-900 dark:text-white"
                        >
                            Manhattan Park Finale
                        </h3>
                        <p
                            class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            The championship finale featuring explosive pack sprints, spectator village, and overall season trophy presentations.
                        </p>
                    </div>
                    <Link
                        href="/events"
                        class="mt-6 flex items-center gap-1 text-xs font-bold text-amber-600 hover:underline dark:text-amber-400"
                    >
                        View Schedule & Start Times
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Quick Feature Highlights -->
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
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
