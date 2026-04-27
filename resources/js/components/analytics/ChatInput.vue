<script setup lang="ts">
import { ArrowUp, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    loading: boolean;
}>();

const emit = defineEmits<{
    submit: [question: string];
}>();

const question = ref('');
const textarea = ref<HTMLTextAreaElement | null>(null);

function autoResize() {
    const el = textarea.value;

    if (!el) {
        return;
    }

    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function handleSubmit() {
    const trimmed = question.value.trim();

    if (!trimmed || props.loading) {
        return;
    }

    emit('submit', trimmed);
    question.value = '';

    if (textarea.value) {
        textarea.value.style.height = 'auto';
    }
}

function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleSubmit();
    }
}
</script>

<template>
    <div class="border-t border-brand-hairline bg-brand-canvas px-4 py-4">
        <div class="mx-auto flex max-w-3xl items-end gap-3 rounded-xl border border-brand-hairline bg-brand-surface-card px-4 py-3 transition-colors focus-within:border-brand-primary/50">
            <textarea
                ref="textarea"
                v-model="question"
                rows="1"
                placeholder="Ask anything about your data..."
                class="flex-1 resize-none bg-transparent text-sm text-start leading-relaxed text-brand-body-strong placeholder:text-brand-muted-soft outline-none"
                style="max-height: 120px"
                :disabled="loading"
                @input="autoResize"
                @keydown="handleKeydown"
            />
            <button
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all cursor-pointer"
                :class="
                    question.trim() && !loading
                        ? 'bg-brand-primary text-brand-on-primary hover:bg-brand-primary-active'
                        : 'bg-brand-surface-elevated text-brand-muted-soft cursor-not-allowed'
                "
                :disabled="!question.trim() || loading"
                @click="handleSubmit"
            >
                <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
                <ArrowUp v-else class="h-4 w-4" />
            </button>
        </div>
        <p class="mt-2 text-center text-xs text-brand-muted-soft">
            Press Enter to send · Shift+Enter for new line
        </p>
    </div>
</template>
