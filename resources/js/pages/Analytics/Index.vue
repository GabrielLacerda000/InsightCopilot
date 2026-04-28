<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { ask, conversations as conversationsRoute, conversation as conversationRoute } from '@/actions/App/Http/Controllers/AnalyticsController';
import ChatInput from '@/components/analytics/ChatInput.vue';
import ChatMessageList from '@/components/analytics/ChatMessageList.vue';
import ConversationSidebar from '@/components/analytics/ConversationSidebar.vue';
import type { AskResponse, Conversation, Message } from '@/types/analytics';

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
const conversations = ref<Conversation[]>([]);
const currentConversationId = ref<string | null>(null);

onMounted(fetchConversations);

async function fetchConversations() {
    try {
        const response = await fetch(conversationsRoute.url(), {
            headers: { Accept: 'application/json' },
        });
        conversations.value = await response.json();
    } catch {
        // silently ignore — sidebar will show empty state
    }
}

async function loadConversation(id: string) {
    currentConversationId.value = id;

    try {
        const response = await fetch(conversationRoute.url(id), {
            headers: { Accept: 'application/json' },
        });

        const rows: { id: string; role: 'user' | 'assistant'; content: string; meta: string }[] =
            await response.json();

        messages.value = rows.map((row) => {
            if (row.role === 'user') {
                return { id: row.id, role: 'user', content: row.content };
            }

            let sql: string | undefined;
            let data: Record<string, unknown>[] | undefined;

            try {
                const meta = JSON.parse(row.meta);
                sql = meta.sql || undefined;
                data = meta.data?.length > 0 ? meta.data : undefined;
            } catch {
                // meta not parseable — skip
            }

            return { id: row.id, role: 'assistant', content: row.content, sql, data };
        });
    } catch {
        messages.value = [];
    }
}

function newConversation() {
    messages.value = [];
    currentConversationId.value = null;
}

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
            body: JSON.stringify({
                question,
                conversation_id: currentConversationId.value,
            }),
        });

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const result: AskResponse = await response.json();

        currentConversationId.value = result.conversation_id;

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

        await fetchConversations();
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

    <div class="flex h-full flex-1 overflow-hidden bg-brand-canvas">
        <ConversationSidebar
            :conversations="conversations"
            :active-id="currentConversationId"
            @select="loadConversation"
            @new="newConversation"
        />

        <div class="flex flex-1 flex-col overflow-hidden">
            <div
                class="mx-auto flex w-full max-w-3xl flex-1 flex-col overflow-hidden"
            >
                <ChatMessageList :messages="messages" />
                <ChatInput :loading="loading" @submit="handleSubmit" />
            </div>
        </div>
    </div>
</template>
