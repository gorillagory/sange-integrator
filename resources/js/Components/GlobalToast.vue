<template>
    <div class="fixed bottom-4 left-4 right-4 md:left-auto md:right-8 md:bottom-8 z-[9999] flex flex-col gap-3 pointer-events-none md:w-96">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-10 opacity-0 sm:translate-y-0 sm:translate-x-10"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition duration-200 ease-in absolute w-full"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 scale-95"
            move-class="transition duration-300 ease-in-out"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex w-full items-center gap-3 rounded-2xl p-4 shadow-xl backdrop-blur-md border"
                :class="{
                    'bg-green-50/90 border-green-200 text-green-800': toast.type === 'success',
                    'bg-red-50/90 border-red-200 text-red-800': toast.type === 'error',
                    'bg-amber-50/90 border-amber-200 text-amber-800': toast.type === 'warning',
                    'bg-blue-50/90 border-blue-200 text-blue-800': toast.type === 'info'
                }"
            >
                <div class="shrink-0">
                    <svg v-if="toast.type === 'success'" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-if="toast.type === 'error'" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-if="toast.type === 'warning'" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <svg v-if="toast.type === 'info'" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>

                <div class="flex-1 text-sm font-medium">
                    {{ toast.message }}
                </div>

                <button @click="removeToast(toast.id)" class="shrink-0 rounded-xl p-2 md:p-1.5 ml-2 hover:bg-black/10 active:bg-black/20 transition-colors">
                    <svg class="h-5 w-5 opacity-70" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import { useToast } from '../Composables/useToast';
const { toasts, removeToast } = useToast();
</script>
