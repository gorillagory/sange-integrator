<template>
    <div class="relative">
        <select
            :id="id"
            class="block w-full appearance-none rounded-xl border-gray-300 bg-white px-4 py-2.5 pr-10 text-gray-900 shadow-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)] sm:text-sm transition-colors cursor-pointer"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': error }"
            v-model="model"
        >
            <slot />
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </div>
    </div>
</template>

<script setup>
const model = defineModel({ type: [String, Number], required: true });

defineProps({
    id: String,
    error: { type: String, default: '' },
});
</script>
