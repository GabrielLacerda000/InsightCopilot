<script setup lang="ts">
import { Check, Copy } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    sql: string;
}>();

const copied = ref(false);

async function copyToClipboard(sql: string) {
    await navigator.clipboard.writeText(sql);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
}
</script>

<template>
    <div class="mt-3 overflow-hidden rounded-lg border border-brand-hairline bg-brand-surface-deep">
        <div class="flex items-center justify-between border-b border-brand-hairline px-4 py-2">
            <span class="font-mono text-xs font-semibold tracking-widest text-brand-muted uppercase">SQL</span>
            <button
                class="flex items-center gap-1.5 rounded px-2 py-1 text-xs text-brand-muted transition-colors hover:bg-brand-surface-card hover:text-brand-body"
                @click="copyToClipboard(sql)"
            >
                <Check v-if="copied" class="h-3.5 w-3.5 text-brand-success" />
                <Copy v-else class="h-3.5 w-3.5" />
                <span>{{ copied ? 'Copied' : 'Copy' }}</span>
            </button>
        </div>
        <div class="overflow-x-auto p-4">
            <pre class="font-mono text-sm leading-relaxed text-brand-body"><code>{{ sql }}</code></pre>
        </div>
    </div>
</template>
