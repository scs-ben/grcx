<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { UserCheck, LayoutDashboard, LogIn, Sun, Moon } from '@lucide/vue';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const page = usePage();
const auth = computed(() => page.props.auth as any);
const pages = computed(
    () => (page.props.pages as Array<{ slug: string; title: string }>) || [],
);
const { appearance, updateAppearance } = useAppearance();

const currentUrl = computed(() => page.url);

const isUrlActive = (url: string) => {
    if (url === '/') {
        return currentUrl.value === '/';
    }

    return currentUrl.value.startsWith(url);
};

const toggleTheme = () => {
    updateAppearance(appearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-slate-50 font-sans text-slate-900 transition-colors duration-200 dark:bg-slate-950 dark:text-slate-100"
    >
        <!-- Main Navbar -->
        <header
            class="sticky top-0 z-50 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <Link
                    href="/"
                    class="flex items-center gap-2.5 text-xl font-extrabold tracking-tight text-slate-900 transition-opacity hover:opacity-90 dark:text-white"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-red-600/40 bg-yellow-400 p-1 shadow-xs"
                    >
                        <img
                            src="/images/grcx-logo.png"
                            alt="GRCX Logo"
                            class="h-full w-full object-contain"
                        />
                    </div>
                    <span
                        >GR CX
                        <span class="font-black text-yellow-500"
                            >2026</span
                        ></span
                    >
                </Link>

                <nav
                    class="hidden items-center gap-6 text-sm font-semibold md:flex"
                >
                    <Link
                        href="/"
                        class="border-b-2 pb-1 transition-colors"
                        :class="
                            isUrlActive('/')
                                ? 'border-amber-500 font-bold text-amber-600 dark:text-amber-400'
                                : 'border-transparent text-slate-600 hover:text-amber-500 dark:text-slate-300'
                        "
                    >
                        Home
                    </Link>
                    <Link
                        href="/events"
                        class="border-b-2 pb-1 transition-colors"
                        :class="
                            isUrlActive('/events')
                                ? 'border-amber-500 font-bold text-amber-600 dark:text-amber-400'
                                : 'border-transparent text-slate-600 hover:text-amber-500 dark:text-slate-300'
                        "
                    >
                        Schedule
                    </Link>
                    <Link
                        href="/results"
                        class="border-b-2 pb-1 transition-colors"
                        :class="
                            isUrlActive('/results')
                                ? 'border-amber-500 font-bold text-amber-600 dark:text-amber-400'
                                : 'border-transparent text-slate-600 hover:text-amber-500 dark:text-slate-300'
                        "
                    >
                        Results
                    </Link>
                    <Link
                        href="/standings"
                        class="border-b-2 pb-1 transition-colors"
                        :class="
                            isUrlActive('/standings')
                                ? 'border-amber-500 font-bold text-amber-600 dark:text-amber-400'
                                : 'border-transparent text-slate-600 hover:text-amber-500 dark:text-slate-300'
                        "
                    >
                        Standings
                    </Link>

                    <template v-for="p in pages" :key="p.slug">
                        <Link
                            :href="`/page/${p.slug}`"
                            class="border-b-2 pb-1 transition-colors"
                            :class="
                                isUrlActive(`/page/${p.slug}`)
                                    ? 'border-amber-500 font-bold text-amber-600 dark:text-amber-400'
                                    : 'border-transparent text-slate-600 hover:text-amber-500 dark:text-slate-300'
                            "
                        >
                            {{ p.title }}
                        </Link>
                    </template>
                </nav>

                <div class="flex items-center gap-3">
                    <button
                        @click="toggleTheme"
                        class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                        title="Toggle theme"
                    >
                        <Sun v-if="appearance === 'dark'" class="h-4 w-4" />
                        <Moon v-else class="h-4 w-4" />
                    </button>

                    <Link
                        href="/register-race"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-slate-950 shadow-md transition-all hover:bg-amber-400"
                    >
                        <UserCheck class="h-4 w-4" />
                        <span>Register</span>
                    </Link>

                    <template v-if="auth?.user">
                        <Link
                            href="/dashboard"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2 text-sm font-medium text-slate-200 transition-all hover:border-slate-600"
                        >
                            <LayoutDashboard class="h-4 w-4 text-amber-400" />
                            <span>Dashboard</span>
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-slate-400 transition-colors hover:text-slate-200"
                        >
                            <LogIn class="h-4 w-4" />
                            <span>Admin Login</span>
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Mobile Subnav -->
            <div
                class="flex items-center justify-around gap-4 overflow-x-auto border-t border-slate-200 bg-white/95 px-4 py-2 text-xs font-medium text-slate-700 md:hidden dark:border-slate-800 dark:bg-slate-900/95 dark:text-slate-300"
            >
                <Link
                    href="/"
                    :class="
                        isUrlActive('/')
                            ? 'font-bold text-amber-600 dark:text-amber-400'
                            : 'hover:text-amber-500'
                    "
                    class="whitespace-nowrap"
                    >Home</Link
                >
                <Link
                    href="/events"
                    :class="
                        isUrlActive('/events')
                            ? 'font-bold text-amber-600 dark:text-amber-400'
                            : 'hover:text-amber-500'
                    "
                    class="whitespace-nowrap"
                    >Events</Link
                >
                <Link
                    href="/results"
                    :class="
                        isUrlActive('/results')
                            ? 'font-bold text-amber-600 dark:text-amber-400'
                            : 'hover:text-amber-500'
                    "
                    class="whitespace-nowrap"
                    >Results</Link
                >
                <Link
                    href="/standings"
                    :class="
                        isUrlActive('/standings')
                            ? 'font-bold text-amber-600 dark:text-amber-400'
                            : 'hover:text-amber-500'
                    "
                    class="whitespace-nowrap"
                    >Standings</Link
                >
                <template v-for="p in pages" :key="p.slug">
                    <Link
                        :href="`/page/${p.slug}`"
                        :class="
                            isUrlActive(`/page/${p.slug}`)
                                ? 'font-bold text-amber-600 dark:text-amber-400'
                                : 'hover:text-amber-500'
                        "
                        class="whitespace-nowrap"
                        >{{ p.title }}</Link
                    >
                </template>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer
            class="mt-16 border-t border-slate-200 bg-slate-100 py-10 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 sm:px-6 md:flex-row lg:px-8"
            >
                <div>
                    <div
                        class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-slate-200"
                    >
                        <img
                            src="/images/grcx-logo.png"
                            alt="GRCX Logo"
                            class="h-6 w-6 object-contain"
                        />
                        <span>Grand Rapids Cyclocross 2026</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Highland Park • Richmond Park • Manhattan Park
                    </p>
                </div>
                <div
                    class="flex flex-wrap items-center gap-6 text-xs font-semibold"
                >
                    <Link href="/results" class="hover:text-amber-500"
                        >Race Results</Link
                    >
                    <Link href="/events" class="hover:text-amber-500"
                        >Events & Timing</Link
                    >
                    <Link href="/standings" class="hover:text-amber-500"
                        >Leaderboard</Link
                    >
                    <Link href="/page/policies" class="hover:text-amber-500"
                        >Weather Policy</Link
                    >
                    <a
                        href="mailto:GrandRapidsCyclocross@gmail.com"
                        class="hover:text-amber-500"
                        >Contact Us</a
                    >
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-500">
                    © 2026 Grand Rapids Cyclocross. Built with Laravel & Vue.
                </p>
            </div>
        </footer>
    </div>
</template>
