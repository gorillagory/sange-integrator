<template>
    <Head>
        <title>Sange Central</title>
        <link rel="icon" :href="faviconUrl">
    </Head>

    <div class="min-h-screen bg-[#0b1220] text-slate-200">
        <div class="flex min-h-screen">
            <aside
                class="hidden w-72 shrink-0 border-r border-white/10 bg-[#0f172a] lg:flex lg:flex-col"
            >
                <div class="border-b border-white/10 px-6 py-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-black text-white shadow-lg shadow-indigo-900/40">
                            SC
                        </div>

                        <div>
                            <div class="text-sm font-black uppercase tracking-[0.22em] text-white">
                                Sange Central
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
                            v-for="item in systemNavItems"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition"
                            :class="isActive(item.href)
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40'
                                : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                        >
                            <ShellIcon :name="item.icon" />
                            <span>{{ item.label }}</span>
                        </Link>
                    </div>
                </nav>

                <div class="border-t border-white/10 px-4 py-4">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-indigo-500/20 text-sm font-black text-indigo-200">
                                <img
                                    v-if="currentUser?.image_url"
                                    :src="currentUser.image_url"
                                    :alt="`${currentUser.name} avatar`"
                                    class="h-full w-full object-cover"
                                >
                                <span v-else>{{ userInitials }}</span>
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
                                SC
                            </div>

                            <div>
                                <div class="text-sm font-black uppercase tracking-[0.22em] text-white">
                                    Sange Central
                                </div>
                                <div class="text-xs text-indigo-300">
                                    Central Console
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <slot name="header">
                                <div>
                                    <h1 class="truncate text-2xl font-black tracking-tight text-white">
                                        Sange Central
                                    </h1>
                                    <p class="mt-1 text-sm text-slate-400">
                                        Manage service records from the central control plane.
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
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const currentUser = computed(() => page.props?.auth?.user ?? null);
const rbac = computed(() => page.props?.auth?.rbac ?? {});
const systemNav = computed(() => rbac.value?.system_nav ?? {});
const faviconUrl = computed(() => page.props?.brand?.favicon_url || '/favicon.ico');

const resolvedSystemNav = computed(() => {
    const nav = systemNav.value ?? {};
    const hasAnyAllowed = Object.values(nav).some((value) => Boolean(value));

    if (hasAnyAllowed) {
        return nav;
    }

    if (!currentUser.value) {
        return nav;
    }

    // Defensive fallback: never render an empty workspace for authenticated users.
    return {
        dashboard: true,
        companies: true,
        blueprints: true,
        users: true,
        rbac: true,
        audit_logs: true,
    };
});

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

const systemNavItems = computed(() => {
    const items = [
        {
            href: '/dashboard',
            label: 'System Pulse',
            icon: 'dashboard',
            allowed: Boolean(resolvedSystemNav.value?.dashboard),
        },
        {
            href: '/companies',
            label: 'Genesis Roster',
            icon: 'companies',
            allowed: Boolean(resolvedSystemNav.value?.companies),
        },
        {
            href: '/blueprints',
            label: 'Blueprint Forge',
            icon: 'blueprints',
            allowed: Boolean(resolvedSystemNav.value?.blueprints),
        },
        {
            href: '/users',
            label: 'Personnel Vault',
            icon: 'users',
            allowed: Boolean(resolvedSystemNav.value?.users),
        },
        {
            href: '/rbac',
            label: 'RBAC Studio',
            icon: 'shield',
            allowed: Boolean(resolvedSystemNav.value?.rbac),
        },
        {
            href: '/audit-logs',
            label: 'Audit Logs',
            icon: 'logs',
            allowed: Boolean(resolvedSystemNav.value?.audit_logs),
        },
    ];

    return items.filter((item) => item.allowed);
});

const icons = {
    dashboard: 'M3 12l9-9 9 9M5 10v10h14V10',
    companies: 'M4 21h16M7 21V8h10v13M9 12h2m2 0h2',
    blueprints: 'M4 7l8-4 8 4-8 4-8-4zm0 5l8 4 8-4m-16 5l8 4 8-4',
    users: 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m18 0v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0z',
    shield: 'M12 3l7 4v5c0 5-3.5 9.5-7 10-3.5-.5-7-5-7-10V7l7-4z',
    logs: 'M9 17v-6m3 6V7m3 10v-3M4 20h16M5 4h14a1 1 0 0 1 1 1v14H4V5a1 1 0 0 1 1-1z',
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
