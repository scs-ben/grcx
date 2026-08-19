<script setup lang="ts">
import { marked } from 'marked';
import { computed } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

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
    if (!props.page?.content) {
        return '';
    }

    return marked.parse(props.page.content);
});
</script>

<template>
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <article
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10 dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="mb-8 border-b border-slate-200 pb-6 dark:border-slate-800"
                >
                    <h1
                        class="mb-2 text-3xl font-black text-slate-900 sm:text-4xl dark:text-white"
                    >
                        {{ page.title }}
                    </h1>
                    <span
                        class="text-xs font-medium text-slate-500 dark:text-slate-400"
                        >Grand Rapids Cyclocross 2026</span
                    >
                </div>

                <div
                    class="prose prose-slate dark:prose-invert prose-amber prose-headings:font-bold prose-headings:text-slate-900 dark:prose-headings:text-white prose-a:text-amber-600 dark:prose-a:text-amber-400 prose-a:underline hover:prose-a:text-amber-500 prose-strong:text-slate-900 dark:prose-strong:text-white prose-strong:font-bold prose-code:text-amber-600 dark:prose-code:text-amber-400 prose-code:bg-slate-100 dark:prose-code:bg-slate-950 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-ul:list-disc prose-ol:list-decimal prose-li:my-1 max-w-none text-sm leading-relaxed text-slate-800 sm:text-base dark:text-slate-200"
                    v-html="renderedContent"
                ></div>
            </article>
        </div>
    </PublicLayout>
</template>
