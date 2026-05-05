<template>
    <div class="min-h-screen bg-[#0b1220] text-slate-200">
        <div class="flex min-h-screen">
            <aside
                class="hidden w-72 shrink-0 border-r border-white/10 bg-[#0f172a] lg:flex lg:flex-col"
            >
                <div class="border-b border-white/10 px-6 py-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-black text-white shadow-lg shadow-indigo-900/40">
                            NX
                        </div>

                        <div>
                            <div class="text-sm font-black uppercase tracking-[0.22em] text-white">
                                Nexus OS
                            </div>
                            <div class="mt-1 text-xs font-medium text-indigo-300">
                                System Administrator Console
                            </div>
                        </div>
                    </div>
                </div>

                <nav class="flex-1 px-4 py-6">
                    <div class="mb-3 px-3 text-[11px] font-bold uppercase tracking-[0.24em] text-slate-500">
                        Workspace
                    </div>

                    <div class="space-y-2">
                        <Link
                            href="/dashboard"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition"
                            :class="isActive('/dashboard')
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40'
                                : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                        >
                            <ShellIcon name="dashboard" />
                            <span>System Pulse</span>
                        </Link>

                        <Link
                            href="/companies"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition"
                            :class="isActive('/companies')
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40'
                                : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                        >
                            <ShellIcon name="companies" />
                            <span>Genesis Roster</span>
                        </Link>

                        <Link
                            href="/blueprints"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition"
                            :class="isActive('/blueprints')
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40'
                                : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                        >
                            <ShellIcon name="blueprints" />
                            <span>Blueprint Forge</span>
                        </Link>

                        <Link
                            href="/users"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition"
                            :class="isActive('/users')
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40'
                                : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                        >
                            <ShellIcon name="users" />
                            <span>Personnel Vault</span>
                        </Link>
                    </div>
                </nav>

                <div class="border-t border-white/10 px-4 py-4">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-500/20 text-sm font-black text-indigo-200">
                                {{ userInitials }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-semibold text-white">
                                    {{ currentUser?.name || 'System User' }}
                                </div>
                                <div class="truncate text-xs text-slate-400">
                                    {{ currentUser?.email || 'No email' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <Link
                                href="/profile"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/10 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                            >
                                Profile
                            </Link>

                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="inline-flex items-center justify-center rounded-2xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-500"
                            >
                                Logout
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-40 border-b border-white/10 bg-[#0b1220]/90 backdrop-blur">
                    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3 lg:hidden">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-600 text-sm font-black text-white">
                                NX
                            </div>

                            <div>
                                <div class="text-sm font-black uppercase tracking-[0.22em] text-white">
                                    Nexus OS
                                </div>
                                <div class="text-xs text-indigo-300">
                                    System Admin
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <slot name="header">
                                <div>
                                    <h1 class="truncate text-2xl font-black tracking-tight text-white">
                                        System Workspace
                                    </h1>
                                    <p class="mt-1 text-sm text-slate-400">
                                        Manage operations from the central control plane.
                                    </p>
                                </div>
                            </slot>
                        </div>

                        <div class="flex items-center gap-2 lg:hidden">
                            <Link
                                href="/profile"
                                class="inline-flex items-center rounded-2xl border border-white/10 px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                            >
                                Profile
                            </Link>

                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="inline-flex items-center rounded-2xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-500"
                            >
                                Logout
                            </Link>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, h } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const currentUser = computed(() => page.props?.auth?.user ?? null);

const isActive = (prefix) => {
    const url = page.url || '';

    return url === prefix || url.startsWith(`${prefix}/`);
};

const userInitials = computed(() => {
    const name = currentUser.value?.name ?? 'System User';

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

const icons = {
    dashboard: 'M3 12l9-9 9 9M5 10v10h14V10',
    companies: 'M4 21h16M7 21V8h10v13M9 12h2m2 0h2',
    blueprints: 'M4 7l8-4 8 4-8 4-8-4zm0 5l8 4 8-4m-16 5l8 4 8-4',
    users: 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m18 0v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0z',
};

const ShellIcon = {
    props: {
        name: {
            type: String,
            required: true,
        },
    },
    render() {
        return h(
            'svg',
            {
                class: 'h-5 w-5 shrink-0',
                fill: 'none',
                stroke: 'currentColor',
                viewBox: '0 0 24 24',
            },
            [
                h('path', {
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    d: icons[this.name] ?? icons.dashboard,
                }),
            ]
        );
    },
};
</script>
