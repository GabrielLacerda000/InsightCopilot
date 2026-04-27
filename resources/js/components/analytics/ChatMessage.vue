<script setup lang="ts">
import DataTable from '@/components/analytics/DataTable.vue';
import SqlBlock from '@/components/analytics/SqlBlock.vue';
import type { Message } from '@/types/analytics';

defineProps<{
    message: Message;
}>();
</script>

<template>
    <div :class="message.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
        <!-- User message -->
        <div
            v-if="message.role === 'user'"
            class="max-w-[75%] rounded-2xl border border-brand-primary/20 bg-brand-primary/10 px-4 py-3 text-sm text-brand-body-strong"
        >
            {{ message.content }}
        </div>

        <!-- Assistant message -->
        <div v-else class="w-full max-w-[90%]">
            <!-- Loading skeleton -->
            <div
                v-if="message.loading"
                class="rounded-2xl border border-brand-hairline bg-brand-surface-card px-4 py-4"
            >
                <div class="flex items-center gap-2">
                    <div class="h-2 w-2 animate-bounce rounded-full bg-brand-primary" style="animation-delay: 0ms" />
                    <div class="h-2 w-2 animate-bounce rounded-full bg-brand-primary" style="animation-delay: 150ms" />
                    <div class="h-2 w-2 animate-bounce rounded-full bg-brand-primary" style="animation-delay: 300ms" />
                </div>
            </div>

            <!-- Error state -->
            <div
                v-else-if="message.error"
                class="rounded-2xl border border-brand-error/30 bg-brand-error/10 px-4 py-3 text-sm text-brand-error"
            >
                {{ message.content }}
            </div>

            <!-- Normal response -->
            <div
                v-else
                class="rounded-2xl border border-brand-hairline bg-brand-surface-card px-4 py-4"
            >
                <p class="text-sm leading-relaxed text-brand-body">{{ message.content }}</p>
                <SqlBlock v-if="message.sql" :sql="message.sql" />
                <DataTable v-if="message.data && message.data.length > 0" :data="message.data" />
            </div>
        </div>
    </div>
</template>
