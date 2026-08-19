<script setup lang="ts">
import { Bike } from '@lucide/vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-amber-500 text-slate-950 font-black"
    >
        <Bike class="size-5 text-slate-950" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-bold text-slate-900 dark:text-white">GR CX <span class="text-amber-600 dark:text-amber-500">2026</span></span>
    </div>
</template>
