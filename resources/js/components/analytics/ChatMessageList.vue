<script setup lang="ts">
import { nextTick, useTemplateRef, watch } from 'vue';
import ChatMessage from '@/components/analytics/ChatMessage.vue';
import type { Message } from '@/types/analytics';

const props = defineProps<{
    messages: Message[];
}>();

const bottomAnchor = useTemplateRef<HTMLDivElement>('bottomAnchor');

watch(
    () => props.messages.length,
    () => {
        nextTick(() => {
            bottomAnchor.value?.scrollIntoView({ behavior: 'smooth' });
        });
    },
);
</script>

<template>
    <div class="flex flex-1 flex-col overflow-y-auto px-4 py-6">
        <!-- Empty state -->
        <div
            v-if="messages.length === 0"
            class="flex flex-1 flex-col items-center justify-center gap-3 text-center"
        >
            <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-brand-hairline bg-brand-surface-card">
                <svg class="h-6 w-6 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                </svg>
            </div>
            <div>
                <p class="font-semibold text-brand-body-strong">Ask anything about your data</p>
                <p class="mt-1 text-sm text-brand-muted">I'll generate and run the SQL query for you.</p>
            </div>
        </div>

        <!-- Messages -->
        <div v-else class="flex flex-col gap-4">
            <ChatMessage
                v-for="message in messages"
                :key="message.id"
                :message="message"
            />
        </div>

        <div ref="bottomAnchor" />
    </div>
</template>
