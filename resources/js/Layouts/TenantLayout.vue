<template>
    <div class="flex h-screen overflow-hidden bg-gray-50 font-sans" :style="themeStyles">

        <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"></div>

        <aside
            @mouseenter="isExpanded = true"
            @mouseleave="isExpanded = false"
            :class="[
            isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
            isExpanded ? 'w-64' : 'w-20',
            'fixed inset-y-0 left-0 z-50 flex flex-col py-6 transition-all duration-300 ease-in-out lg:static lg:translate-x-0 border-r border-white/10 bg-[var(--brand-900)] overflow-hidden'
        ]">
            <div class="flex items-center mx-4 mb-8 h-12 shrink-0">
                <div class="flex items-center justify-center w-12 h-12 bg-white/10 rounded-xl border border-white/20 text-white font-bold text-xl shadow-inner shrink-0">
                    {{ currentCompany?.name?.charAt(0) || 'V' }}
                </div>
                <span class="font-bold text-white whitespace-nowrap overflow-hidden transition-all duration-300" :class="isExpanded ? 'w-auto opacity-100 ml-4' : 'w-0 opacity-0 ml-0'">
                    {{ currentCompany?.name || 'Nexus OS' }}
                </span>
            </div>

            <nav class="flex-1 space-y-2 overflow-y-auto hide-scrollbar">

                <div class="text-[10px] font-bold text-white/40 uppercase tracking-widest mb-2 mt-4 px-6 whitespace-nowrap transition-all duration-300" :class="isExpanded ? 'opacity-100 h-auto' : 'opacity-0 h-0 overflow-hidden'">
                    Operations
                </div>

                <Link href="/dashboard" class="group relative flex items-center h-12 mx-4 px-3 rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition-all" :class="{ '!bg-[var(--brand-500)] !text-white shadow-md': $page.url === '/dashboard' }">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="font-medium whitespace-nowrap overflow-hidden transition-all duration-300" :class="isExpanded ? 'w-auto opacity-100 ml-3' : 'w-0 opacity-0 ml-0'">Dashboard</span>
                </Link>

                <Link href="/bookings" class="group relative flex items-center h-12 mx-4 px-3 rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition-all" :class="{ '!bg-[var(--brand-500)] !text-white shadow-md': $page.url.startsWith('/bookings') }">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium whitespace-nowrap overflow-hidden transition-all duration-300" :class="isExpanded ? 'w-auto opacity-100 ml-3' : 'w-0 opacity-0 ml-0'">Bookings</span>
                </Link>

                <Link href="/clients" class="group relative flex items-center h-12 mx-4 px-3 rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition-all" :class="{ '!bg-[var(--brand-500)] !text-white shadow-md': $page.url.startsWith('/clients') }">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="font-medium whitespace-nowrap overflow-hidden transition-all duration-300" :class="isExpanded ? 'w-auto opacity-100 ml-3' : 'w-0 opacity-0 ml-0'">Clients</span>
                </Link>

                <Link href="/reports" class="group relative flex items-center h-12 mx-4 px-3 rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition-all" :class="{ '!bg-[var(--brand-500)] !text-white shadow-md': $page.url.startsWith('/reports') }">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="font-medium whitespace-nowrap overflow-hidden transition-all duration-300" :class="isExpanded ? 'w-auto opacity-100 ml-3' : 'w-0 opacity-0 ml-0'">Reports</span>
                </Link>

                <div class="text-[10px] font-bold text-[var(--brand-500)] uppercase tracking-widest mb-2 mt-8 px-6 whitespace-nowrap transition-all duration-300" :class="isExpanded ? 'opacity-100 h-auto' : 'opacity-0 h-0 overflow-hidden'">
                    Admin Access
                </div>

                <Link href="/admin/schemas" class="group relative flex items-center h-12 mx-4 px-3 rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition-all" :class="{ '!bg-[var(--brand-500)] !text-white shadow-md': $page.url.startsWith('/admin/schemas') }">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    <span class="font-medium whitespace-nowrap overflow-hidden transition-all duration-300" :class="isExpanded ? 'w-auto opacity-100 ml-3' : 'w-0 opacity-0 ml-0'">Schema Vectors</span>
                </Link>

                <Link href="/admin/documents" class="group relative flex items-center h-12 mx-4 px-3 rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition-all" :class="{ '!bg-[var(--brand-500)] !text-white shadow-md': $page.url.startsWith('/admin/documents') }">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="font-medium whitespace-nowrap overflow-hidden transition-all duration-300" :class="isExpanded ? 'w-auto opacity-100 ml-3' : 'w-0 opacity-0 ml-0'">Document Forge</span>
                </Link>

            </nav>

            <a href="http://sys.bayam.test:8000/dashboard" class="group relative flex items-center h-12 mx-4 px-3 rounded-xl text-white/50 hover:text-red-400 hover:bg-white/5 transition-all mt-auto shrink-0">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="font-medium whitespace-nowrap overflow-hidden transition-all duration-300" :class="isExpanded ? 'w-auto opacity-100 ml-3' : 'w-0 opacity-0 ml-0'">Exit Vault</span>
            </a>
        </aside>

        <div class="flex flex-col flex-1 w-full overflow-hidden">

            <header class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200 h-16 shrink-0 z-30 relative">
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

                    <div class="relative">
                        <button @click="isProfileMenuOpen = !isProfileMenuOpen" class="flex items-center gap-3 focus:outline-none rounded-full ring-2 ring-transparent hover:ring-[var(--brand-200)] transition-all">
                            <div class="h-8 w-8 rounded-full flex items-center justify-center font-bold border bg-[var(--brand-100)] text-[var(--brand-600)] border-[var(--brand-200)] shadow-sm">
                                {{ auth.user?.name?.charAt(0) || 'A' }}
                            </div>
                            <div class="hidden md:flex flex-col items-start">
                                <span class="text-sm font-bold text-gray-700 leading-none">{{ auth.user?.name || 'Administrator' }}</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div v-if="isProfileMenuOpen" @click="isProfileMenuOpen = false" class="fixed inset-0 z-40"></div>

                        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                            <div v-if="isProfileMenuOpen" class="absolute right-0 mt-3 w-60 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden origin-top-right">
                                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ auth.user?.name }}</p>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ auth.user?.email }}</p>
                                    <div class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-[var(--brand-100)] text-[var(--brand-700)]">
                                        Super Administrator
                                    </div>
                                </div>
                                <div class="py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[var(--brand-600)] transition">My Profile</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[var(--brand-600)] transition">Security Settings</a>
                                </div>
                                <div class="border-t border-gray-100 py-1 bg-gray-50/50">
                                    <Link href="/logout" method="post" as="button" type="button" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                        Log Out Securely
                                    </Link>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </header>

            <div class="bg-white border-b border-gray-200 px-8 shrink-0 overflow-x-auto hide-scrollbar z-20">
                <nav class="flex space-x-8 text-sm font-medium h-14 items-center">

                    <template v-if="$page.url === '/dashboard'">
                        <div class="h-full flex items-center border-b-2 border-[var(--brand-600)] text-[var(--brand-700)]">Overview</div>
                        <div class="h-full flex items-center border-b-2 border-transparent text-gray-400 cursor-not-allowed">Recent Activity</div>
                    </template>

                    <template v-if="$page.url.startsWith('/bookings')">
                        <Link href="/bookings" class="h-full flex items-center border-b-2 transition-colors" :class="$page.url === '/bookings' ? 'border-[var(--brand-600)] text-[var(--brand-700)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                            All Bookings
                        </Link>
                        <Link href="/bookings/create" class="h-full flex items-center border-b-2 transition-colors" :class="$page.url === '/bookings/create' ? 'border-[var(--brand-600)] text-[var(--brand-700)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                            Create Booking
                        </Link>
                    </template>

                    <template v-if="$page.url.startsWith('/clients')">
                        <Link href="/clients" class="h-full flex items-center border-b-2 transition-colors" :class="$page.url === '/clients' ? 'border-[var(--brand-600)] text-[var(--brand-700)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                            Client Roster
                        </Link>
                        <Link href="/clients/create" class="h-full flex items-center border-b-2 transition-colors" :class="$page.url === '/clients/create' ? 'border-[var(--brand-600)] text-[var(--brand-700)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                            Create Client
                        </Link>
                    </template>

                    <template v-if="$page.url.startsWith('/reports')">
                        <Link href="/reports" class="h-full flex items-center border-b-2 transition-colors border-[var(--brand-600)] text-[var(--brand-700)]">
                            Analytics
                        </Link>
                        <Link href="#" class="h-full flex items-center border-b-2 transition-colors border-transparent text-gray-500 hover:text-gray-700">
                            Reporting
                        </Link>
                    </template>

                    <template v-if="$page.url.startsWith('/admin')">
                        <Link href="/admin/schemas" class="h-full flex items-center border-b-2 transition-colors" :class="$page.url.startsWith('/admin/schemas') ? 'border-[var(--brand-600)] text-[var(--brand-700)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                            Schema Vectors
                        </Link>
                        <Link href="/admin/documents" class="h-full flex items-center border-b-2 transition-colors" :class="$page.url.startsWith('/admin/documents') ? 'border-[var(--brand-600)] text-[var(--brand-700)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                            Document Forge
                        </Link>
                        <Link href="#" class="h-full flex items-center border-b-2 transition-colors border-transparent text-gray-500 hover:text-gray-700">
                            System Settings
                        </Link>
                    </template>

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
import { usePage, Link } from '@inertiajs/vue3';
import GlobalToast from "../Components/GlobalToast.vue";
import { useToast } from '../Composables/useToast';

const page = usePage();
const auth = computed(() => page.props.auth);
const currentCompany = computed(() => page.props.currentCompany);
const isMobileMenuOpen = ref(false);
const isProfileMenuOpen = ref(false);

const isExpanded = ref(false);

const { addToast } = useToast();

const themeStyles = computed(() => {
    const hex = currentCompany.value?.theme_color || '#0f172a';
    return {
        '--brand-50': `color-mix(in srgb, ${hex} 5%, white)`,
        '--brand-100': `color-mix(in srgb, ${hex} 10%, white)`,
        '--brand-200': `color-mix(in srgb, ${hex} 20%, white)`,
        '--brand-500': hex,
        '--brand-600': `color-mix(in srgb, ${hex} 80%, black)`,
        '--brand-700': `color-mix(in srgb, ${hex} 90%, black)`,
        '--brand-800': `color-mix(in srgb, ${hex} 60%, black)`,
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
