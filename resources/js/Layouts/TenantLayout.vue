<template>
    <div class="flex h-screen overflow-hidden bg-gray-50 font-sans" :style="themeStyles">

        <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"></div>

        <aside :class="[
            isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
            'fixed inset-y-0 left-0 z-50 w-20 flex flex-col items-center py-6 transition-transform duration-300 lg:static lg:translate-x-0 border-r border-white/10 bg-[var(--brand-900)]'
        ]">
            <div class="flex items-center justify-center w-12 h-12 bg-white/10 rounded-xl mb-8 border border-white/20 text-white font-bold text-xl shadow-inner">
                {{ currentCompany?.name?.charAt(0) || 'V' }}
            </div>

            <nav class="flex-1 space-y-4">
                <a href="#" class="group relative flex items-center justify-center w-12 h-12 rounded-xl text-white shadow-md bg-[var(--brand-500)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="absolute left-16 px-2 py-1 ml-2 text-xs font-medium text-white bg-gray-900 rounded-md opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">Dashboard</span>
                </a>

                <a href="#" class="group relative flex items-center justify-center w-12 h-12 rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="absolute left-16 px-2 py-1 ml-2 text-xs font-medium text-white bg-gray-900 rounded-md opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">Users & Roles</span>
                </a>
            </nav>

            <a href="http://sys.bayam.test:8000/dashboard" class="group relative flex items-center justify-center w-12 h-12 rounded-xl text-white/50 hover:text-red-400 hover:bg-white/5 transition-all mt-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="absolute left-16 px-2 py-1 ml-2 text-xs font-medium text-white bg-gray-900 rounded-md opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-50">Exit Vault</span>
            </a>
        </aside>

        <div class="flex flex-col flex-1 w-full overflow-hidden">

            <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200 h-16 shrink-0">
                <div class="flex items-center gap-4">
                    <button @click="isMobileMenuOpen = true" class="p-2 -ml-2 text-gray-500 rounded-lg lg:hidden hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <nav class="hidden sm:flex text-sm font-medium text-gray-500">
                        <slot name="breadcrumbs">
                            <span class="text-[var(--brand-600)]">Overview</span>
                        </slot>
                    </nav>
                </div>

                <div class="flex items-center gap-4">
                    <button class="text-gray-400 hover:text-gray-600 relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
                    </button>

                    <div class="h-8 w-8 rounded-full flex items-center justify-center font-bold border bg-[var(--brand-100)] text-[var(--brand-600)] border-[var(--brand-200)]">
                        {{ auth.user?.name?.charAt(0) || 'A' }}
                    </div>
                </div>
            </header>

            <div class="bg-white border-b border-gray-200 px-6 shrink-0 overflow-x-auto hide-scrollbar">
                <nav class="flex space-x-6 text-sm font-medium">
                    <slot name="subnav">
                        <a href="#" class="py-3 px-1 border-b-2 text-[var(--brand-600)] border-[var(--brand-500)]">General Metrics</a>
                        <a href="#" class="py-3 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all">Recent Activity</a>
                        <a href="#" class="py-3 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all">System Logs</a>
                    </slot>
                </nav>
            </div>

            <main class="flex-1 overflow-y-auto p-6 md:p-8">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>

        </div>
        <GlobalToast />
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GlobalToast from "../Components/GlobalToast.vue";
import { useToast } from '../Composables/useToast';
const page = usePage();
const auth = computed(() => page.props.auth);
const currentCompany = computed(() => page.props.currentCompany);
const isMobileMenuOpen = ref(false);
const { addToast } = useToast(); // 👈 Initialize the engine
// Bulletproof color mixer bypassing Tailwind's v4 compiler bugs
const themeStyles = computed(() => {
    const hex = currentCompany.value?.theme_color || '#0f172a';
    return {
        '--brand-50': `color-mix(in srgb, ${hex} 5%, white)`,
        '--brand-100': `color-mix(in srgb, ${hex} 10%, white)`,
        '--brand-200': `color-mix(in srgb, ${hex} 20%, white)`,
        '--brand-500': hex,
        '--brand-600': `color-mix(in srgb, ${hex} 80%, black)`,
        '--brand-900': `color-mix(in srgb, ${hex} 40%, black)`,
    };
});

watch(() => page.props.flash, (flash) => {
    if (flash?.success) addToast(flash.success, 'success');
    if (flash?.error) addToast(flash.error, 'error');
    if (flash?.warning) addToast(flash.warning, 'warning');
    if (flash?.info) addToast(flash.info, 'info');
}, { deep: true, immediate: true });
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
