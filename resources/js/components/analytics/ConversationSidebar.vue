<script setup lang="ts">
import { MessageSquare, Plus } from 'lucide-vue-next';
import type { Conversation } from '@/types/analytics';

defineProps<{
    conversations: Conversation[];
    activeId: string | null;
}>();

const emit = defineEmits<{
    select: [id: string];
    new: [];
}>();

function formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffDays === 0) {
        return 'Today';
    }

    if (diffDays === 1) {
        return 'Yesterday';
    }
    
    if (diffDays < 7) {
        return `${diffDays} days ago`;
    }

    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}
</script>

<template>
    <div class="flex w-60 shrink-0 flex-col border-r border-brand-hairline bg-brand-surface-card">
        <div class="p-3">
            <button
                class="flex w-full items-center gap-2 rounded-lg border border-brand-hairline bg-brand-canvas px-3 py-2 text-sm text-brand-body-strong transition-colors hover:bg-brand-surface-elevated cursor-pointer"
                @click="emit('new')"
            >
                <Plus class="h-4 w-4 shrink-0 text-brand-muted-soft" />
                <span>New conversation</span>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-2 pb-3">
            <p v-if="conversations.length === 0" class="px-2 py-4 text-center text-xs text-brand-muted-soft">
                No conversations yet
            </p>

            <button
                v-for="convo in conversations"
                :key="convo.id"
                class="flex w-full flex-col gap-0.5 rounded-lg px-3 py-2.5 text-left transition-colors cursor-pointer"
                :class="
                    convo.id === activeId
                        ? 'bg-brand-primary/10 text-brand-primary'
                        : 'text-brand-body hover:bg-brand-surface-elevated'
                "
                @click="emit('select', convo.id)"
            >
                <span class="flex items-center gap-2 text-sm font-medium leading-snug">
                    <MessageSquare class="h-3.5 w-3.5 shrink-0 opacity-60" />
                    <span class="truncate">{{ convo.title }}</span>
                </span>
                <span class="pl-5.5 text-xs opacity-50">{{ formatDate(convo.updated_at) }}</span>
            </button>
        </div>
    </div>
</template>
