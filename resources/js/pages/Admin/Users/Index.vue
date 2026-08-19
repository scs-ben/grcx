<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { UserPlus, Trash2, Edit3, Search, X, Shield, Lock } from '@lucide/vue';
import { ref, computed } from 'vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface AdminUser {
    id: number;
    name: string;
    email: string;
    created_at: string;
}

const props = defineProps<{
    users: AdminUser[];
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth?.user as { id: number });

const searchQuery = ref('');
const showCreateModal = ref(false);
const editingUser = ref<AdminUser | null>(null);

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const editForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const filteredUsers = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.users;
    }

    const q = searchQuery.value.toLowerCase().trim();

    return props.users.filter(
        (u) =>
            u.name.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q),
    );
});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
    createForm.clearErrors();
};

const submitCreate = () => {
    createForm.post('/admin/users', {
        onSuccess: () => {
            closeCreateModal();
        },
    });
};

const openEditModal = (user: AdminUser) => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.password = '';
    editForm.password_confirmation = '';
    editForm.clearErrors();
};

const closeEditModal = () => {
    editingUser.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const submitEdit = () => {
    if (!editingUser.value) {
        return;
    }

    editForm.put(`/admin/users/${editingUser.value.id}`, {
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const deleteUser = (user: AdminUser) => {
    if (user.id === currentUser.value?.id) {
        alert('You cannot delete your own admin account.');

        return;
    }

    if (
        confirm(`Are you sure you want to delete admin account "${user.name}"?`)
    ) {
        useForm({}).delete(`/admin/users/${user.id}`);
    }
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Admin Users', href: '/admin/users' },
        ]"
    >
        <div class="w-full p-6">
            <!-- Header Section -->
            <div
                class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div>
                    <h1
                        class="flex items-center gap-2 text-2xl font-bold text-slate-900 dark:text-slate-100"
                    >
                        <Shield class="h-6 w-6 text-amber-500" />
                        <span>Admin Accounts</span>
                    </h1>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                        Create, update, and manage site administrator accounts
                        for GRCX race management.
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 shadow-sm transition-all hover:bg-amber-400"
                >
                    <UserPlus class="h-4 w-4" /> Create New Admin
                </button>
            </div>

            <!-- Search & Count Bar -->
            <div
                class="mb-6 flex flex-col items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="relative w-full sm:w-80">
                    <Search
                        class="absolute top-2.5 left-3 h-4 w-4 text-slate-400"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search admins by name or email..."
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2 pr-3 pl-9 text-xs text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    />
                </div>
                <div
                    class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                >
                    Total Administrators:
                    <span class="font-bold text-slate-900 dark:text-white">{{
                        users.length
                    }}</span>
                </div>
            </div>

            <!-- Admin Users Table -->
            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
                    >
                        <tr>
                            <th class="px-4 py-3">Administrator</th>
                            <th class="px-4 py-3">Email Address</th>
                            <th class="px-4 py-3">Account Created</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-200 dark:divide-slate-800"
                    >
                        <tr
                            v-for="user in filteredUsers"
                            :key="user.id"
                            class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50"
                        >
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500/10 font-bold text-amber-600 dark:text-amber-400"
                                    >
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div
                                            class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white"
                                        >
                                            <span>{{ user.name }}</span>
                                            <span
                                                v-if="
                                                    user.id === currentUser?.id
                                                "
                                                class="rounded bg-amber-500/15 px-1.5 py-0.5 text-[10px] font-bold text-amber-600 dark:text-amber-400"
                                            >
                                                You
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="px-4 py-3.5 font-mono text-slate-600 dark:text-slate-300"
                            >
                                {{ user.email }}
                            </td>
                            <td
                                class="px-4 py-3.5 text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    new Date(
                                        user.created_at,
                                    ).toLocaleDateString()
                                }}
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <button
                                        @click="openEditModal(user)"
                                        class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-amber-400"
                                        title="Edit Admin Account"
                                    >
                                        <Edit3 class="h-4 w-4" />
                                    </button>
                                    <button
                                        v-if="user.id !== currentUser?.id"
                                        @click="deleteUser(user)"
                                        class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-red-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-red-400"
                                        title="Delete Admin Account"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredUsers.length === 0">
                            <td
                                colspan="4"
                                class="px-4 py-8 text-center text-slate-500 dark:text-slate-400"
                            >
                                No admin accounts found matching search.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Create Admin Modal -->
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
            >
                <div
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white"
                        >
                            <UserPlus class="h-5 w-5 text-amber-500" />
                            <span>Create Admin Account</span>
                        </h3>
                        <button
                            @click="closeCreateModal"
                            class="rounded p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <form
                        @submit.prevent="submitCreate"
                        class="space-y-4 text-xs"
                    >
                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Name</label
                            >
                            <input
                                v-model="createForm.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="Admin Name"
                            />
                            <InputError
                                :message="createForm.errors.name"
                                class="mt-1"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Email Address</label
                            >
                            <input
                                v-model="createForm.email"
                                type="email"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="admin@example.com"
                            />
                            <InputError
                                :message="createForm.errors.email"
                                class="mt-1"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Password</label
                            >
                            <input
                                v-model="createForm.password"
                                type="password"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="Minimum 8 characters"
                            />
                            <InputError
                                :message="createForm.errors.password"
                                class="mt-1"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Confirm Password</label
                            >
                            <input
                                v-model="createForm.password_confirmation"
                                type="password"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="Repeat password"
                            />
                        </div>

                        <div class="mt-6 flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                @click="closeCreateModal"
                                class="rounded-lg border border-slate-300 px-4 py-2 font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="createForm.processing"
                                class="rounded-lg bg-amber-500 px-4 py-2 font-bold text-slate-950 hover:bg-amber-400 disabled:opacity-50"
                            >
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Admin Modal -->
            <div
                v-if="editingUser"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-xs"
            >
                <div
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white"
                        >
                            <Edit3 class="h-5 w-5 text-amber-500" />
                            <span>Edit Admin Account</span>
                        </h3>
                        <button
                            @click="closeEditModal"
                            class="rounded p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <form
                        @submit.prevent="submitEdit"
                        class="space-y-4 text-xs"
                    >
                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Name</label
                            >
                            <input
                                v-model="editForm.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                            <InputError
                                :message="editForm.errors.name"
                                class="mt-1"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Email Address</label
                            >
                            <input
                                v-model="editForm.email"
                                type="email"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                            <InputError
                                :message="editForm.errors.email"
                                class="mt-1"
                            />
                        </div>

                        <div
                            class="border-t border-slate-200 pt-3 dark:border-slate-800"
                        >
                            <label
                                class="mb-1 block flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-300"
                            >
                                <Lock class="h-3.5 w-3.5 text-amber-500" />
                                <span>New Password (optional)</span>
                            </label>
                            <input
                                v-model="editForm.password"
                                type="password"
                                class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="Leave blank to keep existing password"
                            />
                            <InputError
                                :message="editForm.errors.password"
                                class="mt-1"
                            />
                        </div>

                        <div v-if="editForm.password">
                            <label
                                class="mb-1 block font-semibold text-slate-700 dark:text-slate-300"
                                >Confirm New Password</label
                            >
                            <input
                                v-model="editForm.password_confirmation"
                                type="password"
                                class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-slate-900 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                placeholder="Repeat new password"
                            />
                        </div>

                        <div class="mt-6 flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                @click="closeEditModal"
                                class="rounded-lg border border-slate-300 px-4 py-2 font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="rounded-lg bg-amber-500 px-4 py-2 font-bold text-slate-950 hover:bg-amber-400 disabled:opacity-50"
                            >
                                Update Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
