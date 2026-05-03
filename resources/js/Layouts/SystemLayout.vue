<template>
    <div class="min-h-screen bg-[#0f172a] font-sans text-gray-200">

        <header class="bg-[#1e293b] border-b border-white/10 px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/20">NX</div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-widest uppercase">Nexus OS</h1>
                    <p class="text-xs text-indigo-400">System Administrator Console</p>
                </div>
            </div>

            <nav class="flex space-x-8 text-sm font-medium h-full items-end mt-2">
                <Link href="/dashboard" class="pb-5 transition-colors border-b-2" :class="$page.url === '/dashboard' ? 'text-indigo-400 border-indigo-500' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-600'">
                    System Pulse
                </Link>
                <Link href="/companies" class="pb-5 transition-colors border-b-2" :class="$page.url.startsWith('/companies') ? 'text-indigo-400 border-indigo-500' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-600'">
                    Genesis Roster
                </Link>
                <Link href="/blueprints" class="pb-5 transition-colors border-b-2" :class="$page.url.startsWith('/blueprints') ? 'text-indigo-400 border-indigo-500' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-600'">
                    Blueprint Forge
                </Link>
                <Link href="/users" class="pb-5 transition-colors border-b-2" :class="$page.url.startsWith('/users') ? 'text-indigo-400 border-indigo-500' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-600'">
                    Personnel Vault
                </Link>
                <Link href="#" class="pb-5 transition-colors border-b-2 border-transparent text-gray-400 hover:text-white hover:border-gray-600">
                    Nexus Library
                </Link>
            </nav>

            <div class="flex items-center gap-4 relative">
                <button class="text-sm text-gray-400 hover:text-white flex items-center gap-2">
                    {{ $page.props.auth.user?.name || 'Super Admin' }}
                </button>
            </div>
        </header>

        <main class="max-w-7xl mx-auto p-8 mt-4">
            <slot />
        </main>

    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
</script>
