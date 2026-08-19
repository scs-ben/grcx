<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-slate-50 p-6 transition-colors md:p-10 dark:bg-slate-950"
    >
        <div class="w-full max-w-md">
            <div
                class="space-y-6 rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- Brand Header -->
                <div class="flex flex-col items-center gap-3 text-center">
                    <Link
                        :href="home()"
                        class="group flex items-center gap-3 text-xl font-black tracking-tight text-slate-900 dark:text-white"
                    >
                        <div
                            class="flex size-12 items-center justify-center rounded-2xl border border-red-600/40 bg-yellow-400 p-1.5 shadow-md transition-transform group-hover:scale-105"
                        >
                            <img
                                src="/images/grcx-logo.png"
                                alt="GRCX Logo"
                                class="h-full w-full object-contain"
                            />
                        </div>
                        <span
                            >Grand Rapids
                            <span class="font-black text-yellow-500"
                                >CX 2026</span
                            ></span
                        >
                    </Link>
                    <div class="space-y-1">
                        <h1
                            class="text-xl font-bold text-slate-900 dark:text-white"
                        >
                            {{ title }}
                        </h1>
                        <p
                            class="text-xs font-medium text-slate-600 dark:text-slate-400"
                        >
                            {{ description }}
                        </p>
                    </div>
                </div>

                <slot />
            </div>

            <div
                class="mt-6 text-center text-xs font-medium text-slate-500 dark:text-slate-400"
            >
                Official Admin Portal • Grand Rapids Cyclocross
            </div>
        </div>
    </div>
</template>
