<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    data: Record<string, unknown>[];
}>();

const columns = computed(() => (props.data.length > 0 ? Object.keys(props.data[0]) : []));
</script>

<template>
    <div class="mt-3 overflow-hidden rounded-lg border border-brand-hairline">
        <div class="max-h-72 overflow-auto">
            <table class="w-full min-w-full text-sm">
                <thead class="sticky top-0 bg-brand-surface-elevated">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col"
                            class="border-b border-brand-hairline px-4 py-2.5 text-left font-semibold whitespace-nowrap text-brand-body-strong"
                        >
                            {{ col }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, i) in data"
                        :key="i"
                        :class="i % 2 === 0 ? 'bg-brand-surface-card' : 'bg-brand-surface-row'"
                        class="transition-colors hover:bg-brand-surface-elevated"
                    >
                        <td
                            v-for="col in columns"
                            :key="col"
                            class="border-b border-brand-hairline px-4 py-2 whitespace-nowrap text-brand-body"
                        >
                            {{ row[col] ?? '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="border-t border-brand-hairline bg-brand-surface-soft px-4 py-2 text-xs text-brand-muted">
            {{ data.length }} {{ data.length === 1 ? 'row' : 'rows' }}
        </div>
    </div>
</template>
