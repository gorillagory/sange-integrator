<template>
    <nav class="flex items-center text-sm font-medium text-gray-500 whitespace-nowrap overflow-hidden">
        <template v-for="(item, index) in items" :key="index">

            <Link
                v-if="item.url"
                :href="item.url"
                class="hover:text-[var(--brand-600)] transition-colors truncate max-w-[150px] sm:max-w-none"
            >
                {{ item.label }}
            </Link>

            <span
                v-else
                class="truncate max-w-[150px] sm:max-w-none"
                :class="{ 'text-[var(--brand-600)] font-bold': index === items.length - 1 }"
            >
                {{ item.label }}
            </span>

            <svg
                v-if="index < items.length - 1"
                class="w-4 h-4 text-gray-300 mx-2 shrink-0"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>

        </template>
    </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    items: {
        type: Array,
        required: true,
        // Example structure: [{ label: 'Operations', url: null }, { label: 'Bookings', url: '/bookings' }]
    }
});
</script>
