<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Calendar, MapPin, ChevronRight } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';

interface Event {
    id: number;
    name: string;
    location: string;
    event_date: string;
    description: string;
    formatted_date?: string;
    registrations_count?: number;
}

interface Category {
    id: number;
    name: string;
    wave: string;
    duration_description: string;
    start_order_seconds: number;
    is_scoring: boolean;
}

defineProps<{
    events: Event[];
    categories: Category[];
}>();
</script>

<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-8 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white">2026 Season Schedule & Events</h1>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Grand Rapids Cyclocross Series schedule, start times, waves and results.</p>
                </div>
                <Link href="/register-race" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm shadow-md transition-all self-start md:self-auto">
                    Register for Event
                </Link>
            </div>

            <!-- Race Events Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-10">
                <div v-for="evt in events" :key="evt.id" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all shadow-sm">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-black uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 px-3 py-1 rounded-full">
                                Event {{ evt.id }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1">
                                <Calendar class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" /> {{ evt.formatted_date || evt.event_date }}
                            </span>
                        </div>

                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ evt.name }}</h2>
                        <p class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-1.5 mb-4 font-medium">
                            <MapPin class="w-4 h-4 text-amber-500" /> {{ evt.location }}
                        </p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed mb-6">{{ evt.description }}</p>
                    </div>

                    <Link :href="`/events/${evt.id}`" class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs flex items-center justify-center gap-2 transition-all">
                        <span>View Results & Timing</span>
                        <ChevronRight class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                    </Link>
                </div>
            </div>

            <!-- Wave & Race Day Schedule -->
            <div class="mt-16 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-8 shadow-sm">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Race Day Wave Schedule</h2>
                <p class="text-slate-600 dark:text-slate-400 text-xs mb-8">Official start order according to category waves (30-second interval starts).</p>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Wave C -->
                    <div class="bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-black text-amber-600 dark:text-amber-400 uppercase">Wave C</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">10:30 AM Start</span>
                        </div>
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-300 mb-3">30min + 1 Lap</div>
                        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 font-medium">
                            <div>• Open 9-14 & Girls 9-14</div>
                            <div>• C Womens & C Open</div>
                            <div>• BC (Second Race)</div>
                        </div>
                    </div>

                    <!-- Wave A -->
                    <div class="bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-black text-amber-600 dark:text-amber-400 uppercase">Wave A</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">12:00 PM Start</span>
                        </div>
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-300 mb-3">45min + 1 Lap</div>
                        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 font-medium">
                            <div>• A Open & A Womens</div>
                            <div>• A Open 50+ & A Womens 50+</div>
                        </div>
                    </div>

                    <!-- Kids & Costume -->
                    <div class="bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-black text-amber-600 dark:text-amber-400 uppercase">Kids / Costume</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">1:15 PM Start</span>
                        </div>
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-300 mb-3">1 Lap</div>
                        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 font-medium">
                            <div>• Kids Race (Free)</div>
                            <div>• Costume Parade (Free)</div>
                        </div>
                    </div>

                    <!-- Wave B -->
                    <div class="bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-black text-amber-600 dark:text-amber-400 uppercase">Wave B</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">2:15 PM Start</span>
                        </div>
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-300 mb-3">45min + 1 Lap (Reverse)</div>
                        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 font-medium">
                            <div>• Single Speed Open & Womens</div>
                            <div>• Open 15-18 & Girls 15-18</div>
                            <div>• BC (Second Race)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
