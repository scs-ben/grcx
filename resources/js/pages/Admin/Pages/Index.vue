<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { FileText, Plus, Trash2, Edit3, Eye, Globe } from '@lucide/vue';

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
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: 'Manage Pages', href: '/admin/pages' }]">
        <div class="p-6 w-full">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Content Pages CMS</h1>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Manage public landing pages, rules, schedule, and policies.</p>
                </div>
                <Link href="/admin/pages/create" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm">
                    <Plus class="w-4 h-4" /> Create New Page
                </Link>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Title</th>
                            <th class="px-6 py-3.5">Slug</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Last Updated</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-800 dark:text-slate-200">
                        <tr v-for="p in pages" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <FileText class="w-4 h-4 text-amber-500" />
                                {{ p.title }}
                            </td>
                            <td class="px-6 py-4 font-mono text-slate-500 dark:text-slate-400">/page/{{ p.slug }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold" :class="p.is_published ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                                    {{ p.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ new Date(p.updated_at).toLocaleDateString() }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="`/page/${p.slug}`" target="_blank" class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link :href="`/admin/pages/${p.id}/edit`" class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-700">
                                        <Edit3 class="w-4 h-4" />
                                    </Link>
                                    <button @click="deletePage(p.id)" class="p-1.5 rounded bg-slate-100 dark:bg-slate-800 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950">
                                        <Trash2 class="w-4 h-4" />
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
