<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    HelpCircle,
    Calendar,
    Trophy,
    Shield,
    Tag,
    ArrowRight,
} from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Help & User Guide', href: '/admin/help' },
        ]"
    >
        <div class="w-full max-w-6xl p-6">
            <!-- Header Banner -->
            <div
                class="mb-8 rounded-2xl border border-amber-500/30 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent p-6 text-slate-900 dark:text-slate-100"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500 text-slate-950 shadow-md"
                    >
                        <HelpCircle class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight">
                            Admin Operations & Race Day Guide
                        </h1>
                        <p
                            class="mt-1 text-xs text-slate-600 dark:text-slate-400"
                        >
                            Complete documentation for setting up race events,
                            managing pre-registration, handling race day
                            check-in, entering timing results, and managing
                            content.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Jump Grid -->
            <div
                class="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4"
            >
                <a
                    href="#event-setup"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-xs transition-all hover:border-amber-500 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="rounded-lg bg-blue-500/10 p-2.5 text-blue-600 dark:text-blue-400"
                    >
                        <Calendar class="h-5 w-5" />
                    </div>
                    <div>
                        <div
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            1. Event Setup
                        </div>
                        <div
                            class="text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            Venues & Waves
                        </div>
                    </div>
                </a>

                <a
                    href="#check-in"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-xs transition-all hover:border-amber-500 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="rounded-lg bg-emerald-500/10 p-2.5 text-emerald-600 dark:text-emerald-400"
                    >
                        <Tag class="h-5 w-5" />
                    </div>
                    <div>
                        <div
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            2. Check-In & Tags
                        </div>
                        <div
                            class="text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            Clothespin assignment
                        </div>
                    </div>
                </a>

                <a
                    href="#results-entry"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-xs transition-all hover:border-amber-500 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="rounded-lg bg-amber-500/10 p-2.5 text-amber-600 dark:text-amber-400"
                    >
                        <Trophy class="h-5 w-5" />
                    </div>
                    <div>
                        <div
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            3. Timing Entry
                        </div>
                        <div
                            class="text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            Quick add & reorder
                        </div>
                    </div>
                </a>

                <a
                    href="#admin-management"
                    class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-xs transition-all hover:border-amber-500 dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="rounded-lg bg-purple-500/10 p-2.5 text-purple-600 dark:text-purple-400"
                    >
                        <Shield class="h-5 w-5" />
                    </div>
                    <div>
                        <div
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            4. Admin & CMS
                        </div>
                        <div
                            class="text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            Pages & user accounts
                        </div>
                    </div>
                </a>
            </div>

            <!-- Detailed Guide Sections -->
            <div
                class="space-y-8 text-xs leading-relaxed text-slate-700 dark:text-slate-300"
            >
                <!-- Section 1: Event & Category Setup -->
                <section
                    id="event-setup"
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-blue-500" />
                            <h2
                                class="text-base font-extrabold text-slate-900 dark:text-white"
                            >
                                1. Event Setup & Category Configuration
                            </h2>
                        </div>
                        <Link
                            href="/admin/events"
                            class="flex items-center gap-1 font-bold text-amber-600 hover:underline dark:text-amber-400"
                        >
                            Go to Events <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h3
                                class="mb-2 text-xs font-bold tracking-wider text-amber-600 text-slate-900 uppercase dark:text-amber-400 dark:text-white"
                            >
                                Setting Up Race Events
                            </h3>
                            <ol class="list-decimal space-y-2 pl-4">
                                <li>
                                    Navigate to
                                    <strong>Race Events & Categories</strong>
                                    (`/admin/events`).
                                </li>
                                <li>
                                    Click <strong>Create Race Event</strong>.
                                </li>
                                <li>
                                    Enter the event name (e.g.,
                                    <em>Highland Park Race #1</em>), venue
                                    location, event date, and season year (e.g.,
                                    <em>2026</em>).
                                </li>
                                <li>
                                    The active event will display automatically
                                    on the home hero section and admin dashboard
                                    standings widget.
                                </li>
                            </ol>
                        </div>

                        <div>
                            <h3
                                class="mb-2 text-xs font-bold tracking-wider text-amber-600 text-slate-900 uppercase dark:text-amber-400 dark:text-white"
                            >
                                Configuring Main Waves & Categories
                            </h3>
                            <ul class="list-disc space-y-2 pl-4">
                                <li>
                                    Each race category is assigned to a main
                                    heat bucket: <strong>Wave C</strong>,
                                    <strong>Wave A</strong>,
                                    <strong>Wave B</strong>, or
                                    <strong>Kids</strong>.
                                </li>
                                <li>
                                    Set the
                                    <strong>Start Order Seconds</strong> (e.g.,
                                    `0` for 1st row start, `30` for 2nd row
                                    stagger).
                                </li>
                                <li>
                                    Check <strong>Scoring Category</strong> to
                                    include category results in season
                                    leaderboard points.
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Race Day Check-In -->
                <section
                    id="check-in"
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-2">
                            <Tag class="h-5 w-5 text-emerald-500" />
                            <h2
                                class="text-base font-extrabold text-slate-900 dark:text-white"
                            >
                                2. Race Day Check-In & Clothespin Tagging
                            </h2>
                        </div>
                        <Link
                            href="/admin/check-in"
                            class="flex items-center gap-1 font-bold text-amber-600 hover:underline dark:text-amber-400"
                        >
                            Go to Check-In <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h3
                                class="mb-2 text-xs font-bold tracking-wider text-amber-600 text-slate-900 uppercase dark:text-amber-400 dark:text-white"
                            >
                                Pre-Registered Racer Check-In
                            </h3>
                            <ol class="list-decimal space-y-2 pl-4">
                                <li>
                                    Select the active
                                    <strong>Race Event</strong> from the top
                                    dropdown.
                                </li>
                                <li>
                                    Search for the racer by name or bib number.
                                </li>
                                <li>
                                    Check the wave box(es) for the categories
                                    they are racing in today.
                                </li>
                                <li>
                                    Type in their assigned
                                    <strong>Clothespin Tag #</strong> (e.g.,
                                    `#104`) and click
                                    <strong>Check In & Assign Tag</strong>.
                                </li>
                            </ol>
                        </div>

                        <div>
                            <h3
                                class="mb-2 text-xs font-bold tracking-wider text-amber-600 text-slate-900 uppercase dark:text-amber-400 dark:text-white"
                            >
                                Day-Of Walk-Up Registration
                            </h3>
                            <ul class="list-disc space-y-2 pl-4">
                                <li>
                                    Click
                                    <strong>Day-Of Registration</strong> in the
                                    top-right corner.
                                </li>
                                <li>
                                    Fill in racer contact details, team name,
                                    bib number, and selected category.
                                </li>
                                <li>
                                    Select payment fee type (<em
                                        >Race Entry $35</em
                                    >, <em>Youth $20</em>, <em>Season Pass</em>,
                                    or <em>Free/Kids</em>).
                                </li>
                                <li>
                                    Click
                                    <strong>Register & Check In</strong> to
                                    create registration and assign tag in one
                                    step.
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Section 3: Timing & Results Entry -->
                <section
                    id="results-entry"
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-2">
                            <Trophy class="h-5 w-5 text-amber-500" />
                            <h2
                                class="text-base font-extrabold text-slate-900 dark:text-white"
                            >
                                3. Timing & Results Entry
                            </h2>
                        </div>
                        <Link
                            href="/admin/results/entry"
                            class="flex items-center gap-1 font-bold text-amber-600 hover:underline dark:text-amber-400"
                        >
                            Go to Timing Entry
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>

                    <div class="space-y-4">
                        <p>
                            GRCX uses a clothespin tag turn-in system at the
                            finish line for rapid, live results publishing:
                        </p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <div
                                    class="mb-1 font-bold text-slate-900 dark:text-white"
                                >
                                    1. Select Wave Heat
                                </div>
                                <p
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Choose event and heat (e.g. Wave A) to load
                                    racers checked into that heat.
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <div
                                    class="mb-1 font-bold text-slate-900 dark:text-white"
                                >
                                    2. Quick Add Finishers
                                </div>
                                <p
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Search by clothespin tag # or bib # as
                                    turn-ins arrive to auto-append next place.
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                            >
                                <div
                                    class="mb-1 font-bold text-slate-900 dark:text-white"
                                >
                                    3. Drag & Save
                                </div>
                                <p
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Drag handles to adjust finish order. Click
                                    Save Results to update live leaderboards.
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-[11px] text-amber-900 dark:text-amber-200"
                        >
                            <strong>Scoring Points Calculation:</strong> 1st
                            place = 25 pts, 2nd = 22 pts, 3rd = 20 pts, 4th = 18
                            pts down to 21st = 1 pt. Individual & Team standings
                            update automatically on save.
                        </div>
                    </div>
                </section>

                <!-- Section 4: Admin Accounts & CMS Pages -->
                <section
                    id="admin-management"
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-2">
                            <Shield class="h-5 w-5 text-purple-500" />
                            <h2
                                class="text-base font-extrabold text-slate-900 dark:text-white"
                            >
                                4. CMS Pages & Admin Accounts
                            </h2>
                        </div>
                        <div class="flex gap-4">
                            <Link
                                href="/admin/pages"
                                class="flex items-center gap-1 font-bold text-amber-600 hover:underline dark:text-amber-400"
                            >
                                Pages <ArrowRight class="h-3.5 w-3.5" />
                            </Link>
                            <Link
                                href="/admin/users"
                                class="flex items-center gap-1 font-bold text-amber-600 hover:underline dark:text-amber-400"
                            >
                                Admin Users <ArrowRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h3
                                class="mb-2 text-xs font-bold tracking-wider text-amber-600 text-slate-900 uppercase dark:text-amber-400 dark:text-white"
                            >
                                Managing Site Pages (`/admin/pages`)
                            </h3>
                            <p class="mb-2 text-slate-600 dark:text-slate-400">
                                Edit rules, category descriptions, and weather
                                policies. Published pages automatically render
                                in the public top header navigation and mobile
                                subnav.
                            </p>
                        </div>

                        <div>
                            <h3
                                class="mb-2 text-xs font-bold tracking-wider text-amber-600 text-slate-900 uppercase dark:text-amber-400 dark:text-white"
                            >
                                Managing Admin Accounts (`/admin/users`)
                            </h3>
                            <p class="text-slate-600 dark:text-slate-400">
                                Create login credentials for volunteers and race
                                staff. Self-deletion protection prevents
                                accidental lockout of your active login account.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
