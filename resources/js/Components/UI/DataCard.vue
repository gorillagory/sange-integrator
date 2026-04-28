<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200/60 overflow-hidden">
        <div v-if="$slots.header" class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
            <slot name="header" />
        </div>

        <div class="w-full overflow-x-auto">
            <slot />
        </div>

        <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            <slot name="footer" />
        </div>
    </div>
</template>
