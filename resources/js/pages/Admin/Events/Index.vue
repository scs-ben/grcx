<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    Calendar,
    Plus,
    Edit3,
    Trash2,
    X,
    Award,
    MapPin,
    Flag,
} from '@lucide/vue';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Event {
    id: number;
    name: string;
    location: string;
    event_date: string;
    season_year: number;
    description: string | null;
}

interface Category {
    id: number;
    name: string;
    wave: string;
    duration_description: string | null;
    start_order_seconds: number;
    is_scoring: boolean;
    podium_order: number;
}

defineProps<{
    events: Event[];
    categories: Category[];
    waves: string[];
}>();

// Event Modal State
const isEventModalOpen = ref(false);
const editingEvent = ref<Event | null>(null);

const eventForm = useForm({
    name: '',
    location: '',
    event_date: '',
    season_year: 2026,
    description: '',
});

const openCreateEventModal = () => {
    editingEvent.value = null;
    eventForm.reset();
    eventForm.season_year = 2026;
    isEventModalOpen.value = true;
};

const openEditEventModal = (evt: Event) => {
    editingEvent.value = evt;
    eventForm.name = evt.name;
    eventForm.location = evt.location;
    eventForm.event_date = evt.event_date;
    eventForm.season_year = evt.season_year || 2026;
    eventForm.description = evt.description || '';
    isEventModalOpen.value = true;
};

const closeEventModal = () => {
    isEventModalOpen.value = false;
    editingEvent.value = null;
};

const submitEvent = () => {
    if (editingEvent.value) {
        eventForm.put(`/admin/events/${editingEvent.value.id}`, {
            onSuccess: () => closeEventModal(),
        });
    } else {
        eventForm.post('/admin/events', {
            onSuccess: () => closeEventModal(),
        });
    }
};

const deleteEvent = (evt: Event) => {
    if (confirm(`Are you sure you want to delete "${evt.name}"?`)) {
        useForm({}).delete(`/admin/events/${evt.id}`);
    }
};

// Category Modal State
const isCategoryModalOpen = ref(false);
const editingCategory = ref<Category | null>(null);

const categoryForm = useForm({
    name: '',
    wave: 'C',
    duration_description: '30min+1 lap',
    start_order_seconds: 0,
    is_scoring: true,
    podium_order: 1,
});

const openCreateCategoryModal = () => {
    editingCategory.value = null;
    categoryForm.reset();
    categoryForm.wave = 'C';
    categoryForm.is_scoring = true;
    isCategoryModalOpen.value = true;
};

const openEditCategoryModal = (cat: Category) => {
    editingCategory.value = cat;
    categoryForm.name = cat.name;
    categoryForm.wave = cat.wave;
    categoryForm.duration_description = cat.duration_description || '';
    categoryForm.start_order_seconds = cat.start_order_seconds;
    categoryForm.is_scoring = cat.is_scoring;
    categoryForm.podium_order = cat.podium_order;
    isCategoryModalOpen.value = true;
};

const closeCategoryModal = () => {
    isCategoryModalOpen.value = false;
    editingCategory.value = null;
};

const submitCategory = () => {
    if (editingCategory.value) {
        categoryForm.put(`/admin/categories/${editingCategory.value.id}`, {
            onSuccess: () => closeCategoryModal(),
        });
    } else {
        categoryForm.post('/admin/categories', {
            onSuccess: () => closeCategoryModal(),
        });
    }
};

const deleteCategory = (cat: Category) => {
    if (confirm(`Are you sure you want to delete category "${cat.name}"?`)) {
        useForm({}).delete(`/admin/categories/${cat.id}`);
    }
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Race Events & Categories', href: '/admin/events' },
        ]"
    >
        <div class="w-full space-y-10 p-6">
            <!-- Events Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1
                            class="flex items-center gap-2 text-2xl font-bold text-slate-900 dark:text-slate-100"
                        >
                            <Calendar class="h-6 w-6 text-amber-500" />
                            Manage Race Events
                        </h1>
                        <p
                            class="mt-0.5 text-xs text-slate-600 dark:text-slate-400"
                        >
                            Create, edit, or remove series events and venues.
                        </p>
                    </div>
                    <button
                        @click="openCreateEventModal"
                        class="flex items-center gap-1.5 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <Plus class="h-4 w-4" /> Add Race Event
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div
                        v-for="evt in events"
                        :key="evt.id"
                        class="flex flex-col justify-between space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span
                                    class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-bold tracking-wider text-amber-600 uppercase dark:text-amber-400"
                                >
                                    {{ evt.season_year || 2026 }} Season
                                </span>
                                <div class="flex items-center gap-1">
                                    <button
                                        @click="openEditEventModal(evt)"
                                        class="rounded p-1 text-slate-400 hover:text-amber-500"
                                    >
                                        <Edit3 class="h-4 w-4" />
                                    </button>
                                    <button
                                        @click="deleteEvent(evt)"
                                        class="rounded p-1 text-slate-400 hover:text-rose-500"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                            <h2
                                class="text-lg font-extrabold text-slate-900 dark:text-white"
                            >
                                {{ evt.name }}
                            </h2>
                            <div
                                class="space-y-1 text-xs font-medium text-slate-500 dark:text-slate-400"
                            >
                                <div class="flex items-center gap-1.5">
                                    <Calendar
                                        class="h-3.5 w-3.5 text-amber-500"
                                    />
                                    {{ evt.event_date }}
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <MapPin
                                        class="h-3.5 w-3.5 text-amber-500"
                                    />
                                    {{ evt.location }}
                                </div>
                            </div>
                            <p
                                v-if="evt.description"
                                class="line-clamp-2 pt-1 text-xs text-slate-600 italic dark:text-slate-400"
                            >
                                {{ evt.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories & Waves Section -->
            <div
                class="space-y-4 border-t border-slate-200 pt-6 dark:border-slate-800"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2
                            class="flex items-center gap-2 text-2xl font-bold text-slate-900 dark:text-slate-100"
                        >
                            <Award class="h-6 w-6 text-amber-500" />
                            Manage Waves & Race Categories
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-slate-600 dark:text-slate-400"
                        >
                            Configure race categories, assigned waves (C, A, B,
                            Kids), start offsets, and scoring status.
                        </p>
                    </div>
                    <button
                        @click="openCreateCategoryModal"
                        class="flex items-center gap-1.5 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                    >
                        <Plus class="h-4 w-4" /> Add Category
                    </button>
                </div>

                <!-- Wave Groupings -->
                <div class="space-y-6">
                    <div
                        v-for="w in waves"
                        :key="w"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-3 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div class="flex items-center gap-2">
                                <Flag class="h-4 w-4 text-amber-500" />
                                <span
                                    class="text-sm font-black text-slate-900 dark:text-white"
                                    >Wave {{ w }} Heat</span
                                >
                            </div>
                            <span
                                class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    categories.filter((c) => c.wave === w)
                                        .length
                                }}
                                Categories
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead
                                    class="bg-slate-100/50 text-[10px] font-bold text-slate-500 uppercase dark:bg-slate-900/50 dark:text-slate-400"
                                >
                                    <tr>
                                        <th class="px-6 py-3">Category Name</th>
                                        <th class="px-6 py-3">
                                            Duration / Format
                                        </th>
                                        <th class="px-6 py-3">Start Delay</th>
                                        <th class="px-6 py-3">
                                            Scoring Status
                                        </th>
                                        <th class="px-6 py-3 text-right">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-slate-100 dark:divide-slate-800/60"
                                >
                                    <tr
                                        v-for="cat in categories.filter(
                                            (c) => c.wave === w,
                                        )"
                                        :key="cat.id"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-800/30"
                                    >
                                        <td
                                            class="px-6 py-3.5 font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ cat.name }}
                                        </td>
                                        <td
                                            class="px-6 py-3.5 font-mono text-slate-600 dark:text-slate-400"
                                        >
                                            {{
                                                cat.duration_description || '—'
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-3.5 font-mono text-slate-600 dark:text-slate-400"
                                        >
                                            +{{ cat.start_order_seconds }}s
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span
                                                v-if="cat.is_scoring"
                                                class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400"
                                            >
                                                Series Points
                                            </span>
                                            <span
                                                v-else
                                                class="rounded-full border border-slate-500/20 bg-slate-500/10 px-2.5 py-0.5 text-[10px] font-bold text-slate-500"
                                            >
                                                Non-Scoring / Fun
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 text-right">
                                            <div
                                                class="flex items-center justify-end gap-2"
                                            >
                                                <button
                                                    @click="
                                                        openEditCategoryModal(
                                                            cat,
                                                        )
                                                    "
                                                    class="rounded p-1 text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-slate-800"
                                                >
                                                    <Edit3 class="h-4 w-4" />
                                                </button>
                                                <button
                                                    @click="deleteCategory(cat)"
                                                    class="rounded p-1 text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950"
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
                </div>
            </div>

            <!-- Event Modal -->
            <div
                v-if="isEventModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-lg space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                    >
                        <h3
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            {{
                                editingEvent
                                    ? 'Edit Race Event'
                                    : 'Create New Race Event'
                            }}
                        </h3>
                        <button
                            @click="closeEventModal"
                            class="text-slate-400 hover:text-white"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form
                        @submit.prevent="submitEvent"
                        class="space-y-4 text-xs"
                    >
                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Event Name *</label
                            >
                            <input
                                v-model="eventForm.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Venue Location *</label
                                >
                                <input
                                    v-model="eventForm.location"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Event Date *</label
                                >
                                <input
                                    v-model="eventForm.event_date"
                                    type="date"
                                    required
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Season Year</label
                            >
                            <input
                                v-model="eventForm.season_year"
                                type="number"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Description</label
                            >
                            <textarea
                                v-model="eventForm.description"
                                rows="3"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            ></textarea>
                        </div>

                        <div
                            class="flex justify-end gap-2 border-t border-slate-200 pt-3 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                @click="closeEventModal"
                                class="rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="eventForm.processing"
                                class="rounded-lg bg-amber-500 px-5 py-2 font-bold text-slate-950 hover:bg-amber-400"
                            >
                                {{
                                    editingEvent
                                        ? 'Update Event'
                                        : 'Create Event'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Category Modal -->
            <div
                v-if="isCategoryModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            >
                <div
                    class="w-full max-w-lg space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                    >
                        <h3
                            class="text-lg font-bold text-slate-900 dark:text-white"
                        >
                            {{
                                editingCategory
                                    ? 'Edit Category'
                                    : 'Create New Category'
                            }}
                        </h3>
                        <button
                            @click="closeCategoryModal"
                            class="text-slate-400 hover:text-white"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form
                        @submit.prevent="submitCategory"
                        class="space-y-4 text-xs"
                    >
                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Category Name *</label
                            >
                            <input
                                v-model="categoryForm.name"
                                type="text"
                                required
                                placeholder="e.g. Single Speed Open"
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Assigned Wave Heat *</label
                                >
                                <select
                                    v-model="categoryForm.wave"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                >
                                    <option
                                        v-for="w in waves"
                                        :key="w"
                                        :value="w"
                                    >
                                        Wave {{ w }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Duration Description</label
                                >
                                <input
                                    v-model="categoryForm.duration_description"
                                    type="text"
                                    placeholder="e.g. 45min+1 lap"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Start Delay Offset (Seconds)</label
                                >
                                <input
                                    v-model="categoryForm.start_order_seconds"
                                    type="number"
                                    required
                                    min="0"
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                    >Podium Order</label
                                >
                                <input
                                    v-model="categoryForm.podium_order"
                                    type="number"
                                    required
                                    class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-2 text-slate-900 focus:outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input
                                type="checkbox"
                                v-model="categoryForm.is_scoring"
                                id="cat_is_scoring"
                                class="rounded border-slate-300 bg-slate-50 text-amber-500 dark:border-slate-800 dark:bg-slate-950"
                            />
                            <label
                                for="cat_is_scoring"
                                class="font-medium text-slate-700 dark:text-slate-300"
                                >Award Series Points (Scoring Category)</label
                            >
                        </div>

                        <div
                            class="flex justify-end gap-2 border-t border-slate-200 pt-3 dark:border-slate-800"
                        >
                            <button
                                type="button"
                                @click="closeCategoryModal"
                                class="rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="categoryForm.processing"
                                class="rounded-lg bg-amber-500 px-5 py-2 font-bold text-slate-950 hover:bg-amber-400"
                            >
                                {{
                                    editingCategory
                                        ? 'Update Category'
                                        : 'Create Category'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
