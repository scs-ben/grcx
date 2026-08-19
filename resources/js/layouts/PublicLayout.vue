<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Calendar, Trophy, UserCheck, FileText, LayoutDashboard, Bike, LogIn, Sun, Moon } from '@lucide/vue';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const page = usePage();
const auth = computed(() => page.props.auth as any);
const pages = computed(() => (page.props.pages as Array<{ slug: string; title: string }>) || []);
const { appearance, updateAppearance } = useAppearance();

const toggleTheme = () => {
    updateAppearance(appearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col font-sans transition-colors duration-200">
        <!-- Main Navbar -->
        <header class="border-b border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-900/90 backdrop-blur sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2.5 font-extrabold text-xl tracking-tight text-slate-900 dark:text-white hover:text-amber-500 transition-colors">
                    <div class="w-9 h-9 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center font-black">
                        <Bike class="w-6 h-6" />
                    </div>
                    <span>GR CX <span class="text-amber-500">2026</span></span>
                </Link>

                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600 dark:text-slate-300">
                    <Link href="/" class="hover:text-amber-500 transition-colors">Home</Link>
                    <Link href="/events" class="hover:text-amber-500 transition-colors">Schedule</Link>
                    <Link href="/standings" class="hover:text-amber-500 transition-colors">Standings</Link>
                    <Link href="/results" class="hover:text-amber-500 transition-colors font-bold text-amber-600 dark:text-amber-400">Results</Link>

                    <template v-for="p in pages" :key="p.slug">
                        <Link :href="`/page/${p.slug}`" class="hover:text-amber-500 transition-colors">
                            {{ p.title }}
                        </Link>
                    </template>
                </nav>

                <div class="flex items-center gap-3">
                    <button @click="toggleTheme" class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Toggle theme">
                        <Sun v-if="appearance === 'dark'" class="w-4 h-4" />
                        <Moon v-else class="w-4 h-4" />
                    </button>

                    <Link href="/register-race" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm shadow-md transition-all">
                        <UserCheck class="w-4 h-4" />
                        <span>Register</span>
                    </Link>

                    <template v-if="auth?.user">
                        <Link href="/dashboard" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-700 hover:border-slate-600 bg-slate-800 text-slate-200 font-medium text-sm transition-all">
                            <LayoutDashboard class="w-4 h-4 text-amber-400" />
                            <span>Dashboard</span>
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-slate-400 hover:text-slate-200 text-sm font-medium transition-colors">
                            <LogIn class="w-4 h-4" />
                            <span>Admin Login</span>
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Mobile Subnav -->
            <div class="md:hidden border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 px-4 py-2 flex items-center justify-around text-xs font-medium text-slate-700 dark:text-slate-300 overflow-x-auto gap-4">
                <Link href="/" class="hover:text-amber-500 whitespace-nowrap">Home</Link>
                <Link href="/events" class="hover:text-amber-500 whitespace-nowrap">Events</Link>
                <Link href="/results" class="hover:text-amber-500 font-bold text-amber-600 dark:text-amber-400 whitespace-nowrap">Results</Link>
                <Link href="/standings" class="hover:text-amber-500 whitespace-nowrap">Standings</Link>
                <template v-for="p in pages" :key="p.slug">
                    <Link :href="`/page/${p.slug}`" class="hover:text-amber-500 whitespace-nowrap">{{ p.title }}</Link>
                </template>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900/60 py-10 mt-16 text-slate-600 dark:text-slate-400 text-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 font-bold text-slate-900 dark:text-slate-200 text-lg">
                        <Bike class="w-5 h-5 text-amber-500" />
                        <span>Grand Rapids Cyclocross 2026</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Highland Park • Richmond Park • Manhattan Park</p>
                </div>
                <div class="flex flex-wrap items-center gap-6 text-xs font-semibold">
                    <Link href="/results" class="hover:text-amber-500">Race Results</Link>
                    <Link href="/events" class="hover:text-amber-500">Events & Timing</Link>
                    <Link href="/standings" class="hover:text-amber-500">Leaderboard</Link>
                    <Link href="/page/weather-and-policies" class="hover:text-amber-500">Weather Policy</Link>
                    <a href="mailto:GrandRapidsCyclocross@gmail.com" class="hover:text-amber-500">Contact Us</a>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-500">© 2026 Grand Rapids Cyclocross. Built with Laravel & Vue.</p>
            </div>
        </footer>
    </div>
</template>
