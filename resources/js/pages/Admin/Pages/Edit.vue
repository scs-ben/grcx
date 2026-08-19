<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import { Save, ArrowLeft } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';

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
    <AppLayout
        :breadcrumbs="[
            { title: 'Manage Pages', href: '/admin/pages' },
            { title: page ? 'Edit Page' : 'Create Page', href: '#' },
        ]"
    >
        <div class="w-full p-6">
            <div class="mb-6 flex items-center justify-between">
                <Link
                    href="/admin/pages"
                    class="flex items-center gap-1 text-xs text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                >
                    <ArrowLeft class="h-4 w-4" /> Back to Pages
                </Link>
                <h1
                    class="text-xl font-bold text-slate-900 dark:text-slate-100"
                >
                    {{ page ? 'Edit Page' : 'Create Page' }}
                </h1>
            </div>

            <form
                @submit.prevent="submit"
                class="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >Page Title *</label
                    >
                    <input
                        v-model="form.title"
                        @input="autoSlug"
                        type="text"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >URL Slug *</label
                    >
                    <div class="flex items-center">
                        <span
                            class="rounded-l-lg border border-r-0 border-slate-300 bg-slate-100 px-3 py-2 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-950"
                            >/page/</span
                        >
                        <input
                            v-model="form.slug"
                            type="text"
                            required
                            class="w-full rounded-r-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >Page Content (Markdown / Text) *</label
                    >
                    <textarea
                        v-model="form.content"
                        rows="12"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 p-3.5 font-mono text-xs leading-relaxed text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200"
                    ></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        v-model="form.is_published"
                        id="published"
                        class="rounded border-slate-300 bg-slate-50 text-amber-500 dark:border-slate-800 dark:bg-slate-950"
                    />
                    <label
                        for="published"
                        class="text-xs font-medium text-slate-700 dark:text-slate-300"
                        >Publish immediately to website</label
                    >
                </div>

                <div
                    class="flex justify-end border-t border-slate-200 pt-4 dark:border-slate-800"
                >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-1.5 rounded-lg bg-amber-500 px-5 py-2.5 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <Save class="h-4 w-4" /> Save Page
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
