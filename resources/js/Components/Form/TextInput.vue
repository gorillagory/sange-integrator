<template>
    <div class="relative">
        <input
            :type="type"
            :id="id"
            :placeholder="placeholder"
            class="block w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)] sm:text-sm transition-colors"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': error }"
            v-model="model"
            ref="input"
        />
        <div v-if="$slots.icon" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
            <slot name="icon" />
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({ type: [String, Number], required: true });

defineProps({
    id: String,
    type: { type: String, default: 'text' },
    placeholder: String,
    error: { type: String, default: '' },
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>
