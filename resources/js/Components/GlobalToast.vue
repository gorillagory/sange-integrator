<template>
    <div class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[200] pointer-events-none flex flex-col items-center gap-3">
        <TransitionGroup
            enter-active-class="transition duration-500 ease-out"
            enter-from-class="opacity-0 translate-y-10 scale-90"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-300 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-90"
        >
            <div
                v-for="toast in activeToasts"
                :key="toast.id"
                class="pointer-events-auto flex items-center gap-3 px-6 py-3 rounded-2xl shadow-2xl border min-w-[320px] animate-bounce-short"
                :class="toast.type === 'error' ? 'bg-red-900 border-red-700 text-white' : 'bg-gray-900 border-gray-700 text-white'"
            >
                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="toast.type === 'error' ? 'bg-red-500' : 'bg-emerald-500'">
                    <svg v-if="toast.type === 'error'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="text-sm font-bold tracking-tight">{{ toast.message }}</span>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';

const page = usePage();
const activeToasts = ref([]);
let toastCounter = 0;
const { toasts } = useToast();
const handledToastIds = new Set();

const addToast = (message, type = 'success') => {
    const id = toastCounter++;
    activeToasts.value.push({ id, message, type });
    setTimeout(() => {
        activeToasts.value = activeToasts.value.filter(t => t.id !== id);
    }, 4000);
};

watch(() => page.props.flash, (flash) => {
    if (flash?.success) addToast(flash.success, 'success');
    if (flash?.error) addToast(flash.error, 'error');
}, { deep: true, immediate: true });

watch(toasts, (items) => {
    if (!Array.isArray(items) || !items.length) {
        return;
    }

    items.forEach((toast) => {
        if (!handledToastIds.has(toast.id)) {
            handledToastIds.add(toast.id);
            addToast(toast.message, toast.type);
        }
    });
}, { deep: true, immediate: true });

defineExpose({ addToast });
</script>

<style scoped>
@keyframes bounce-short {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.animate-bounce-short {
    animation: bounce-short 0.5s ease-in-out;
}
</style>
