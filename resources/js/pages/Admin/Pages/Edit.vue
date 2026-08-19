<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { FileText, Save, ArrowLeft } from '@lucide/vue';

interface Page {
    id?: number;
    title: string;
    slug: string;
    content: string;
    is_published: boolean;
}

const props = defineProps<{
    page?: Page | null;
}>();

const form = useForm({
    title: props.page?.title || '',
    slug: props.page?.slug || '',
    content: props.page?.content || '',
    is_published: props.page?.is_published ?? true,
});

const submit = () => {
    if (props.page?.id) {
        form.put(`/admin/pages/${props.page.id}`);
    } else {
        form.post('/admin/pages');
    }
};

const autoSlug = () => {
    if (!props.page && form.title) {
        form.slug = form.title
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)+/g, '');
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Manage Pages', href: '/admin/pages' }, { title: page ? 'Edit Page' : 'Create Page', href: '#' }]">
        <div class="p-6 w-full">
            <div class="flex items-center justify-between mb-6">
                <Link href="/admin/pages" class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center gap-1">
                    <ArrowLeft class="w-4 h-4" /> Back to Pages
                </Link>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ page ? 'Edit Page' : 'Create Page' }}</h1>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 space-y-5 shadow-sm">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Page Title *</label>
                    <input v-model="form.title" @input="autoSlug" type="text" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">URL Slug *</label>
                    <div class="flex items-center">
                        <span class="bg-slate-100 dark:bg-slate-950 border border-r-0 border-slate-300 dark:border-slate-800 text-slate-500 text-xs px-3 py-2 rounded-l-lg">/page/</span>
                        <input v-model="form.slug" type="text" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-r-lg px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Page Content (Markdown / Text) *</label>
                    <textarea v-model="form.content" rows="12" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-lg p-3.5 text-xs font-mono text-slate-900 dark:text-slate-200 focus:border-amber-500 focus:outline-none leading-relaxed"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.is_published" id="published" class="rounded text-amber-500 bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-800" />
                    <label for="published" class="text-xs font-medium text-slate-700 dark:text-slate-300">Publish immediately to website</label>
                </div>

                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm">
                        <Save class="w-4 h-4" /> Save Page
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
