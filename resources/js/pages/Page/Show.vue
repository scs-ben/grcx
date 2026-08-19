<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { marked } from 'marked';
import { computed } from 'vue';

interface Page {
    id: number;
    title: string;
    slug: string;
    content: string;
    updated_at: string;
}

const props = defineProps<{
    page: Page;
}>();

const renderedContent = computed(() => {
    if (!props.page?.content) return '';
    return marked.parse(props.page.content);
});
</script>

<template>
    <PublicLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <article class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 sm:p-10 shadow-sm">
                <div class="border-b border-slate-200 dark:border-slate-800 pb-6 mb-8">
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mb-2">{{ page.title }}</h1>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Grand Rapids Cyclocross 2026</span>
                </div>

                <div
                    class="prose prose-slate dark:prose-invert prose-amber max-w-none text-slate-800 dark:text-slate-200 text-sm sm:text-base leading-relaxed
                           prose-headings:font-bold prose-headings:text-slate-900 dark:prose-headings:text-white
                           prose-a:text-amber-600 dark:prose-a:text-amber-400 prose-a:underline hover:prose-a:text-amber-500
                           prose-strong:text-slate-900 dark:prose-strong:text-white prose-strong:font-bold
                           prose-code:text-amber-600 dark:prose-code:text-amber-400 prose-code:bg-slate-100 dark:prose-code:bg-slate-950 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded
                           prose-ul:list-disc prose-ol:list-decimal prose-li:my-1"
                    v-html="renderedContent"
                ></div>
            </article>
        </div>
    </PublicLayout>
</template>
