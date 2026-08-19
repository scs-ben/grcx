<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Calendar, MapPin, ChevronRight } from '@lucide/vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

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
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-6 border-b border-slate-200 pb-8 md:flex-row md:items-center dark:border-slate-800"
            >
                <div>
                    <h1
                        class="text-3xl font-black text-slate-900 dark:text-white"
                    >
                        2026 Season Schedule & Events
                    </h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Grand Rapids Cyclocross Series schedule, start times,
                        waves and results.
                    </p>
                </div>
                <Link
                    href="/register-race"
                    class="self-start rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-slate-950 shadow-md transition-all hover:bg-amber-400 md:self-auto"
                >
                    Register for Event
                </Link>
            </div>

            <!-- Race Events Grid -->
            <div class="my-10 grid grid-cols-1 gap-6 md:grid-cols-3">
                <div
                    v-for="evt in events"
                    :key="evt.id"
                    class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700"
                >
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <span
                                class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-black tracking-wider text-amber-600 uppercase dark:text-amber-400"
                            >
                                Event {{ evt.id }}
                            </span>
                            <span
                                class="flex items-center gap-1 text-xs font-medium text-slate-500 dark:text-slate-400"
                            >
                                <Calendar
                                    class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500"
                                />
                                {{ evt.formatted_date || evt.event_date }}
                            </span>
                        </div>

                        <h2
                            class="mb-2 text-xl font-bold text-slate-900 dark:text-white"
                        >
                            {{ evt.name }}
                        </h2>
                        <p
                            class="mb-4 flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-400"
                        >
                            <MapPin class="h-4 w-4 text-amber-500" />
                            {{ evt.location }}
                        </p>
                        <p
                            class="mb-6 text-sm leading-relaxed text-slate-700 dark:text-slate-300"
                        >
                            {{ evt.description }}
                        </p>
                    </div>

                    <Link
                        :href="`/events/${evt.id}`"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 py-2.5 text-xs font-bold text-slate-800 transition-all hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        <span>View Results & Timing</span>
                        <ChevronRight
                            class="h-4 w-4 text-amber-600 dark:text-amber-400"
                        />
                    </Link>
                </div>
            </div>

            <!-- Wave & Race Day Schedule -->
            <div
                class="mt-16 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900"
            >
                <h2
                    class="mb-2 text-2xl font-bold text-slate-900 dark:text-white"
                >
                    Race Day Wave Schedule
                </h2>
                <p class="mb-8 text-xs text-slate-600 dark:text-slate-400">
                    Official start order according to category waves (30-second
                    interval starts).
                </p>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <!-- Wave C -->
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="text-sm font-black text-amber-600 uppercase dark:text-amber-400"
                                >Wave C</span
                            >
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >10:30 AM Start</span
                            >
                        </div>
                        <div
                            class="mb-3 text-xs font-semibold text-slate-800 dark:text-slate-300"
                        >
                            30min + 1 Lap
                        </div>
                        <div
                            class="space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-400"
                        >
                            <div>• Open 9-14 & Girls 9-14</div>
                            <div>• C Womens & C Open</div>
                            <div>• BC (Second Race)</div>
                        </div>
                    </div>

                    <!-- Wave A -->
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="text-sm font-black text-amber-600 uppercase dark:text-amber-400"
                                >Wave A</span
                            >
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >12:00 PM Start</span
                            >
                        </div>
                        <div
                            class="mb-3 text-xs font-semibold text-slate-800 dark:text-slate-300"
                        >
                            45min + 1 Lap
                        </div>
                        <div
                            class="space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-400"
                        >
                            <div>• A Open & A Womens</div>
                            <div>• A Open 50+ & A Womens 50+</div>
                        </div>
                    </div>

                    <!-- Kids & Costume -->
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="text-sm font-black text-amber-600 uppercase dark:text-amber-400"
                                >Kids / Costume</span
                            >
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >1:15 PM Start</span
                            >
                        </div>
                        <div
                            class="mb-3 text-xs font-semibold text-slate-800 dark:text-slate-300"
                        >
                            1 Lap
                        </div>
                        <div
                            class="space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-400"
                        >
                            <div>• Kids Race (Free)</div>
                            <div>• Costume Parade (Free)</div>
                        </div>
                    </div>

                    <!-- Wave B -->
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950/60"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="text-sm font-black text-amber-600 uppercase dark:text-amber-400"
                                >Wave B</span
                            >
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >2:15 PM Start</span
                            >
                        </div>
                        <div
                            class="mb-3 text-xs font-semibold text-slate-800 dark:text-slate-300"
                        >
                            45min + 1 Lap (Reverse)
                        </div>
                        <div
                            class="space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-400"
                        >
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
