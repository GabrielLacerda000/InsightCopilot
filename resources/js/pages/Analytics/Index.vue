<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Analytics',
                href: '/analytics',
            },
        ],
    },
});
</script>

<template>
    <Head title="Analytics" />

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div
            class="flex flex-1 items-center justify-center rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <p class="text-sm text-muted-foreground">Analytics — coming soon</p>
        </div>
    </div>
</template>
