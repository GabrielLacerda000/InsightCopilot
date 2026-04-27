<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ask } from '@/actions/App/Http/Controllers/AnalyticsController';
import ChatInput from '@/components/analytics/ChatInput.vue';
import ChatMessageList from '@/components/analytics/ChatMessageList.vue';
import type { AskResponse, Message } from '@/types/analytics';

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

const messages = ref<Message[]>([]);
const loading = ref(false);

async function handleSubmit(question: string) {
    const userMessage: Message = {
        id: crypto.randomUUID(),
        role: 'user',
        content: question,
    };

    const placeholderId = crypto.randomUUID();
    const placeholder: Message = {
        id: placeholderId,
        role: 'assistant',
        content: '',
        loading: true,
    };

    messages.value.push(userMessage, placeholder);
    loading.value = true;

    try {
        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') ?? '';

        const response = await fetch(ask.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ question }),
        });

        const result: AskResponse = await response.json();

        const index = messages.value.findIndex((m) => m.id === placeholderId);

        if (index !== -1) {
            messages.value[index] = {
                id: placeholderId,
                role: 'assistant',
                content: result.text,
                sql: result.sql || undefined,
                data: result.data?.length > 0 ? result.data : undefined,
            };
        }
    } catch {
        const index = messages.value.findIndex((m) => m.id === placeholderId);

        if (index !== -1) {
            messages.value[index] = {
                id: placeholderId,
                role: 'assistant',
                content: 'Something went wrong. Please try again.',
                error: true,
            };
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Analytics" />

    <div class="flex h-full flex-1 flex-col overflow-hidden bg-brand-canvas">
        <div
            class="mx-auto flex w-full max-w-3xl flex-1 flex-col overflow-hidden"
        >
            <ChatMessageList :messages="messages" />
            <ChatInput :loading="loading" @submit="handleSubmit" />
        </div>
    </div>
</template>
