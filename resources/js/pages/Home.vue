<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Calendar, Trophy, Zap, ShieldAlert, Award, Bike, ArrowRight, CheckCircle2 } from '@lucide/vue';

interface Event {
    id: number;
    name: string;
    location: string;
    event_date: string;
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
        <section class="relative bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 py-20 lg:py-28 overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#f59e0b_1px,transparent_1px)] [background-size:24px_24px]"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-semibold uppercase tracking-wider mb-6">
                        <Bike class="w-4 h-4" /> 2026 Cyclocross Season
                    </div>
                    <h1 class="text-4xl sm:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-tight mb-6">
                        Grand Rapids <br/><span class="text-amber-500">Cyclocross</span> 2026
                    </h1>
                    <p class="text-lg sm:text-xl text-slate-600 dark:text-slate-300 mb-8 leading-relaxed">
                        3 epic race venues across Grand Rapids. 12 competitive scoring categories, single speed, BC open categories, youth & costume parades.
                    </p>
                    <div class="flex flex-wrap items-center gap-4">
                        <Link href="/register-race" class="px-6 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-base shadow-lg shadow-amber-500/20 flex items-center gap-2 transition-all">
                            <span>Register for Races</span>
                            <ArrowRight class="w-5 h-5" />
                        </Link>
                        <Link href="/events" class="px-6 py-3.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white font-bold text-base transition-all">
                            View Race Schedule
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Next Event Highlight -->
        <section v-if="nextEvent" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-6 sm:p-8 text-slate-950 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <span class="text-xs font-black uppercase tracking-wider bg-slate-950 text-amber-400 px-3 py-1 rounded-full">Next Race Day</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-950 mt-2">{{ nextEvent.name }}</h2>
                    <p class="font-bold text-slate-900 mt-1 flex items-center gap-2">
                        <Calendar class="w-4 h-4" /> {{ nextEvent.formatted_date || nextEvent.event_date }} • {{ nextEvent.location }}
                    </p>
                </div>
                <Link :href="`/events/${nextEvent.id}`" class="px-5 py-3 rounded-xl bg-slate-950 hover:bg-slate-900 text-white font-bold text-sm flex items-center gap-2 transition-all">
                    <span>Event & Timing Details</span>
                    <ArrowRight class="w-4 h-4 text-amber-400" />
                </Link>
            </div>
        </section>

        <!-- Quick Info Cards -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-5">
                        <Calendar class="w-6 h-6" />
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">3 Great Venues</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-4">
                        Highland Park (9/26), Richmond Park (10/10), and the Manhattan Park finale (10/25).
                    </p>
                    <Link href="/events" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                        View Schedule & Start Times <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-5">
                        <Trophy class="w-6 h-6" />
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">12 Podium Categories</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-4">
                        Points awarded down to 21st place! Podium recognition and overall season champion titles.
                    </p>
                    <Link href="/standings" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                        View Leaderboard Standings <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-5">
                        <Zap class="w-6 h-6" />
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Easy Clothes-Pin Timing</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-4">
                        Turn in your bib clothes-pin at the finish line for instant live result entry and standings updates.
                    </p>
                    <Link href="/page/schedule-and-rules" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                        Read Race Rules <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Pricing Summary -->
        <section class="bg-slate-100 dark:bg-slate-900/50 border-y border-slate-200 dark:border-slate-800 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white">Simple Season Pricing</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-2">Choose single race entry or get the full 3-race season discount.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 text-center shadow-sm">
                        <div class="text-amber-600 dark:text-amber-500 text-3xl font-black mb-1">$70</div>
                        <div class="font-bold text-slate-900 dark:text-white text-base">Full Season Pass</div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">All 3 races included</p>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 text-center shadow-sm">
                        <div class="text-slate-900 dark:text-white text-3xl font-black mb-1">$35</div>
                        <div class="font-bold text-slate-900 dark:text-white text-base">Single Race Entry</div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Per race day</p>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 text-center shadow-sm">
                        <div class="text-slate-900 dark:text-white text-3xl font-black mb-1">$20</div>
                        <div class="font-bold text-slate-900 dark:text-white text-base">Youth (< 18)</div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Per race day</p>
                    </div>

                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 text-center shadow-sm">
                        <div class="text-emerald-600 dark:text-emerald-400 text-3xl font-black mb-1">FREE</div>
                        <div class="font-bold text-slate-900 dark:text-white text-base">BC / Kids / Costume</div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Free second race entry & parade</p>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
