<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bike } from '@lucide/vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center bg-slate-50 dark:bg-slate-950 p-6 md:p-10 transition-colors">
        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-xl space-y-6">
                <!-- Brand Header -->
                <div class="flex flex-col items-center gap-3 text-center">
                    <Link :href="home()" class="group flex items-center gap-3 font-black text-slate-900 dark:text-white text-xl tracking-tight">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-amber-500 text-slate-950 font-black shadow-md group-hover:scale-105 transition-transform">
                            <Bike class="size-6 text-slate-950" />
                        </div>
                        <span>Grand Rapids <span class="text-amber-600 dark:text-amber-400">CX 2026</span></span>
                    </Link>
                    <div class="space-y-1">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ title }}</h1>
                        <p class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <slot />
            </div>

            <div class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400 font-medium">
                Official Admin Portal • Grand Rapids Cyclocross
            </div>
        </div>
    </div>
</template>
