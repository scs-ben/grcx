<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { FileText, Plus, Trash2, Edit3, Eye } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Page {
    id: number;
    title: string;
    slug: string;
    is_published: boolean;
    updated_at: string;
}

defineProps<{
    pages: Page[];
}>();

const deletePage = (id: number) => {
    if (confirm('Are you sure you want to delete this page?')) {
        useForm({}).delete(`/admin/pages/${id}`);
    }
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Manage Pages', href: '/admin/pages' },
        ]"
    >
        <div class="w-full p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold text-slate-900 dark:text-slate-100"
                    >
                        Content Pages CMS
                    </h1>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                        Manage public landing pages, rules, schedule, and
                        policies.
                    </p>
                </div>
                <Link
                    href="/admin/pages/create"
                    class="flex items-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                >
                    <Plus class="h-4 w-4" /> Create New Page
                </Link>
            </div>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
                    >
                        <tr>
                            <th class="px-6 py-3.5">Title</th>
                            <th class="px-6 py-3.5">Slug</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Last Updated</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 text-slate-800 dark:divide-slate-800/60 dark:text-slate-200"
                    >
                        <tr
                            v-for="p in pages"
                            :key="p.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/40"
                        >
                            <td
                                class="flex items-center gap-2 px-6 py-4 font-bold text-slate-900 dark:text-white"
                            >
                                <FileText class="h-4 w-4 text-amber-500" />
                                {{ p.title }}
                            </td>
                            <td
                                class="px-6 py-4 font-mono text-slate-500 dark:text-slate-400"
                            >
                                /page/{{ p.slug }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                    :class="
                                        p.is_published
                                            ? 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                    "
                                >
                                    {{ p.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    new Date(p.updated_at).toLocaleDateString()
                                }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="`/page/${p.slug}`"
                                        target="_blank"
                                        class="rounded bg-slate-100 p-1.5 text-slate-600 hover:text-slate-900 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-white"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                    <Link
                                        :href="`/admin/pages/${p.id}/edit`"
                                        class="rounded bg-slate-100 p-1.5 text-amber-600 hover:bg-amber-50 dark:bg-slate-800 dark:text-amber-400 dark:hover:bg-slate-700"
                                    >
                                        <Edit3 class="h-4 w-4" />
                                    </Link>
                                    <button
                                        @click="deletePage(p.id)"
                                        class="rounded bg-slate-100 p-1.5 text-rose-600 hover:bg-rose-50 dark:bg-slate-800 dark:text-rose-400 dark:hover:bg-rose-950"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
