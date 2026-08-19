<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <div
        :class="className"
        class="flex shrink-0 items-center justify-center overflow-hidden"
        v-bind="$attrs"
    >
        <img
            src="/images/grcx-logo.png"
            alt="GRCX Logo"
            class="h-full w-full object-contain"
        />
    </div>
</template>
