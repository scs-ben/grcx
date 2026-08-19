<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { LogIn } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Sign in to Admin Dashboard',
        description:
            'Enter your credentials to manage races, timing, and registrations',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Log in" />

    <div
        v-if="status"
        class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3 text-center text-xs font-bold text-emerald-600 dark:text-emerald-400"
    >
        {{ status }}
    </div>

    <PasskeyVerify />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-5 text-xs">
            <div class="grid gap-1.5">
                <Label
                    for="email"
                    class="font-bold text-slate-700 dark:text-slate-300"
                    >Email Address</Label
                >
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="admin@grcx.org"
                    class="rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-900 focus:border-amber-500 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-1.5">
                <div class="flex items-center justify-between">
                    <Label
                        for="password"
                        class="font-bold text-slate-700 dark:text-slate-300"
                        >Password</Label
                    >
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs font-semibold text-amber-600 hover:underline dark:text-amber-400"
                        :tabindex="5"
                    >
                        Forgot password?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="rounded-xl border border-slate-300 bg-slate-50 dark:border-slate-800 dark:bg-slate-950"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <Label
                    for="remember"
                    class="flex cursor-pointer items-center space-x-2.5 font-medium text-slate-600 dark:text-slate-400"
                >
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Remember this session</span>
                </Label>
            </div>

            <button
                type="submit"
                class="mt-2 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-amber-500 py-3 text-xs font-black text-slate-950 shadow-md transition-all hover:bg-amber-400"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                <LogIn v-else class="h-4 w-4" />
                <span>Log In to Dashboard</span>
            </button>
        </div>
    </Form>
</template>
