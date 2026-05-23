<template>
    <Head>
        <title>{{ companyName }}</title>
        <link rel="icon" :href="tenantFaviconUrl">
    </Head>

    <div class="flex h-screen overflow-hidden bg-slate-100 font-sans" :style="themeStyles">
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
            @click="isMobileMenuOpen = false"
        />

        <aside
            :class="[
                isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
                isExpanded ? 'w-72' : 'w-20',
                'fixed inset-y-0 left-0 z-50 flex flex-col overflow-hidden border-r border-white/10 bg-[var(--brand-900)] py-6 transition-all duration-300 ease-in-out lg:static lg:translate-x-0',
            ]"
            @mouseenter="isExpanded = true"
            @mouseleave="isExpanded = false"
        >
            <div class="mx-4 mb-8 flex h-14 shrink-0 items-center">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/20 bg-white/10 shadow-inner">
                    <img
                        v-if="companyLogoUrl"
                        :src="companyLogoUrl"
                        :alt="`${companyName} logo`"
                        class="h-full w-full object-cover"
                    >

                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-xl font-black text-white"
                    >
                        {{ companyInitial }}
                    </div>
                </div>

                <div
                    class="ml-4 overflow-hidden whitespace-nowrap transition-all duration-300"
                    :class="isExpanded ? 'w-auto opacity-100' : 'w-0 opacity-0'"
                >
                    <div class="font-bold text-white">
                        {{ companyName }}
                    </div>
                    <div class="text-xs text-white/60">
                        {{ companyHost }}
                    </div>
                </div>
            </div>

            <nav class="flex-1 space-y-2 overflow-y-auto px-2 hide-scrollbar">
                <div
                    v-if="operationsNav.length"
                    class="mb-2 mt-4 px-4 text-[10px] font-bold uppercase tracking-widest text-white/40 transition-all duration-300"
                    :class="isExpanded ? 'h-auto opacity-100' : 'h-0 overflow-hidden opacity-0'"
                >
                    Service Records
                </div>

                <Link
                    v-for="item in operationsNav"
                    :key="item.href"
                    :href="item.href"
                    class="group relative mx-2 flex h-12 items-center rounded-xl px-3 transition-all"
                    :class="item.active
                        ? 'bg-[var(--brand-500)] text-white shadow-md'
                        : 'text-white/70 hover:bg-white/10 hover:text-white'"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="item.icon"
                            />
                        </svg>
                    </span>

                    <span
                        class="ml-4 whitespace-nowrap transition-all duration-300"
                        :class="isExpanded ? 'opacity-100' : 'pointer-events-none w-0 overflow-hidden opacity-0'"
                    >
                        {{ item.label }}
                    </span>
                </Link>

                <div
                    v-if="adminNav.length"
                    class="mb-2 mt-6 px-4 text-[10px] font-bold uppercase tracking-widest text-white/40 transition-all duration-300"
                    :class="isExpanded ? 'h-auto opacity-100' : 'h-0 overflow-hidden opacity-0'"
                >
                    Administration
                </div>

                <Link
                    v-for="item in adminNav"
                    :key="item.href"
                    :href="item.href"
                    class="group relative mx-2 flex h-12 items-center rounded-xl px-3 transition-all"
                    :class="item.active
                        ? 'bg-[var(--brand-500)] text-white shadow-md'
                        : 'text-white/70 hover:bg-white/10 hover:text-white'"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="item.icon"
                            />
                        </svg>
                    </span>

                    <span
                        class="ml-4 whitespace-nowrap transition-all duration-300"
                        :class="isExpanded ? 'opacity-100' : 'pointer-events-none w-0 overflow-hidden opacity-0'"
                    >
                        {{ item.label }}
                    </span>
                </Link>

                <div
                    class="mb-2 mt-6 px-4 text-[10px] font-bold uppercase tracking-widest text-white/40 transition-all duration-300"
                    :class="isExpanded ? 'h-auto opacity-100' : 'h-0 overflow-hidden opacity-0'"
                >
                    Account
                </div>

                <Link
                    href="/profile"
                    class="group relative mx-2 flex h-12 items-center rounded-xl px-3 transition-all"
                    :class="isActive('/profile')
                        ? 'bg-[var(--brand-500)] text-white shadow-md'
                        : 'text-white/70 hover:bg-white/10 hover:text-white'"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5.121 17.804A9.969 9.969 0 0112 15c2.4 0 4.603.846 6.121 2.256M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                    </span>

                    <span
                        class="ml-4 whitespace-nowrap transition-all duration-300"
                        :class="isExpanded ? 'opacity-100' : 'pointer-events-none w-0 overflow-hidden opacity-0'"
                    >
                        Profile
                    </span>
                </Link>

                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="group relative mx-2 flex h-12 w-[calc(100%-1rem)] items-center rounded-xl px-3 text-left transition-all hover:bg-white/10 hover:text-white"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"
                            />
                        </svg>
                    </span>

                    <span
                        class="ml-4 whitespace-nowrap transition-all duration-300"
                        :class="isExpanded ? 'opacity-100' : 'pointer-events-none w-0 overflow-hidden opacity-0'"
                    >
                        Logout
                    </span>
                </Link>
            </nav>

            <div class="mx-4 mt-6 rounded-2xl border border-white/10 bg-white/5 p-4 text-white/80">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-white/10 text-sm font-bold text-white">
                        <img
                            v-if="currentUser?.image_url"
                            :src="currentUser.image_url"
                            :alt="`${currentUser.name} avatar`"
                            class="h-full w-full object-cover"
                        >
                        <span v-else>{{ userInitials }}</span>
                    </div>

                    <div
                        class="min-w-0 transition-all duration-300"
                        :class="isExpanded ? 'opacity-100' : 'w-0 overflow-hidden opacity-0'"
                    >
                        <div class="truncate text-sm font-semibold text-white">
                            {{ currentUser?.name || 'Tenant User' }}
                        </div>
                        <div class="truncate text-xs text-white/60">
                            {{ currentUser?.email || 'No email' }}
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <header class="border-b border-slate-200 bg-white px-4 py-4 shadow-sm lg:px-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden"
                            @click="isMobileMenuOpen = !isMobileMenuOpen"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="flex items-center gap-3">
                            <div class="hidden h-10 w-10 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 sm:flex">
                                <img
                                    v-if="companyLogoUrl"
                                    :src="companyLogoUrl"
                                    :alt="`${companyName} logo`"
                                    class="h-full w-full object-cover"
                                >

                                <div v-else class="text-sm font-black text-slate-700">
                                    {{ companyInitial }}
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                    {{ companyHost }}
                                </div>
                                <div class="text-lg font-bold text-slate-900">
                                    {{ companyName }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            href="/profile"
                            class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Profile
                        </Link>

                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold text-white transition"
                            :style="{ backgroundColor: brand600 }"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto p-4 lg:p-6">
                <slot />
            </main>
        </div>

        <GlobalToast />
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import GlobalToast from '@/Components/GlobalToast.vue';

const page = usePage();

const isMobileMenuOpen = ref(false);
const isExpanded = ref(false);

const currentUser = computed(() => page.props?.auth?.user ?? null);
const currentCompany = computed(() => page.props?.currentCompany ?? null);
const brand = computed(() => page.props?.brand ?? {});
const rbac = computed(() => page.props?.auth?.rbac ?? {});
const tenantNav = computed(() => rbac.value?.tenant_nav ?? {});

const resolvedTenantNav = computed(() => {
    const nav = tenantNav.value ?? {};
    const hasAnyAllowed = Object.values(nav).some((value) => Boolean(value));

    if (hasAnyAllowed) {
        return nav;
    }

    if (!currentUser.value) {
        return nav;
    }

    // Defensive fallback: keep tenant shell navigable while backend still enforces access.
    return {
        dashboard: true,
        operations: true,
        clients: true,
        reports: true,
        schemas: true,
        documents: true,
    };
});

const companyName = computed(() => currentCompany.value?.name || brand.value?.tenant?.name || 'Tenant Workspace');
const companyHost = computed(() => brand.value?.tenant?.host || currentCompany.value?.subdomain || brand.value?.host || 'tenant');
const companyInitial = computed(() => companyName.value.charAt(0).toUpperCase());
const tenantFaviconUrl = computed(() => brand.value?.favicon_url || companyLogoUrl.value || '/favicon.ico');

const companyLogoUrl = computed(() => {
    const path = currentCompany.value?.logo_path || brand.value?.tenant?.logo_url;

    if (!path) {
        return null;
    }

    if (String(path).startsWith('http://') || String(path).startsWith('https://')) {
        return path;
    }

    return String(path).startsWith('/storage/')
        ? path
        : `/storage/${String(path).replace(/^storage\//, '')}`;
});

const brand500 = computed(() => normalizeHex(currentCompany.value?.theme_color || brand.value?.tenant?.theme_color || '#4f46e5'));
const brand600 = computed(() => darkenHex(brand500.value, 0.12));
const brand700 = computed(() => darkenHex(brand500.value, 0.22));
const brand50 = computed(() => tintHex(brand500.value, 0.92));
const brand100 = computed(() => tintHex(brand500.value, 0.82));

const themeStyles = computed(() => ({
    '--brand-50': brand50.value,
    '--brand-100': brand100.value,
    '--brand-500': brand500.value,
    '--brand-600': brand600.value,
    '--brand-700': brand700.value,
    '--brand-900': brand700.value,
}));

const userInitials = computed(() => {
    const name = currentUser.value?.name ?? 'Tenant User';

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

const isActive = (prefix) => {
    const url = page.url || '';
    return url === prefix || url.startsWith(`${prefix}/`);
};

const operationsNav = computed(() => [
    {
        href: '/dashboard',
        label: 'Dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        active: isActive('/dashboard'),
        allowed: Boolean(resolvedTenantNav.value?.dashboard),
    },
    {
        href: '/service-records',
        label: 'Service Records',
        icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H8m8 0h1a2 2 0 012 2v8a2 2 0 01-2 2H7a2 2 0 01-2-2V8a2 2 0 012-2h1',
        active: isActive('/service-records') || isActive('/operations'),
        allowed: Boolean(resolvedTenantNav.value?.operations),
    },
    {
        href: '/clients',
        label: 'Clients',
        icon: 'M17 20h5v-1a4 4 0 00-5-3.87M9 20H4v-1a4 4 0 015-3.87m8-6.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 11-6 0 3 3 0 016 0zM6 10a3 3 0 11-6 0 3 3 0 016 0z',
        active: isActive('/clients'),
        allowed: Boolean(resolvedTenantNav.value?.clients),
    },
    {
        href: '/reports',
        label: 'Reports',
        icon: 'M11 3v18m-4-4v4m8-10v10m4-14v14',
        active: isActive('/reports'),
        allowed: Boolean(resolvedTenantNav.value?.reports),
    },
].filter((item) => item.allowed));

const adminNav = computed(() => [
    {
        href: '/admin/schemas',
        label: 'Schema Vectors',
        icon: 'M12 2L2 7l10 5 10-5-10-5zm0 9L2 6m10 14l10-5m-10 5v-9',
        active: isActive('/admin/schemas'),
        allowed: Boolean(resolvedTenantNav.value?.schemas),
    },
    {
        href: '/admin/rbac',
        label: 'RBAC Studio',
        icon: 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0120 17.944M12 14L5.84 10.578A12.084 12.084 0 004 17.944M12 14v7',
        active: isActive('/admin/rbac'),
        allowed: Boolean(resolvedTenantNav.value?.rbac),
    },
    {
        href: '/admin/documents',
        label: 'Documents',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        active: isActive('/admin/documents'),
        allowed: Boolean(resolvedTenantNav.value?.documents),
    },
].filter((item) => item.allowed));

function normalizeHex(hex) {
    const value = String(hex || '').trim();

    if (/^#[0-9a-fA-F]{6}$/.test(value)) {
        return value;
    }

    if (/^#[0-9a-fA-F]{3}$/.test(value)) {
        return `#${value[1]}${value[1]}${value[2]}${value[2]}${value[3]}${value[3]}`;
    }

    return '#4f46e5';
}

function hexToRgb(hex) {
    const normalized = normalizeHex(hex);

    return {
        r: parseInt(normalized.slice(1, 3), 16),
        g: parseInt(normalized.slice(3, 5), 16),
        b: parseInt(normalized.slice(5, 7), 16),
    };
}

function rgbToHex(r, g, b) {
    const toHex = (value) => Math.max(0, Math.min(255, Math.round(value))).toString(16).padStart(2, '0');

    return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
}

function tintHex(hex, amount = 0.5) {
    const { r, g, b } = hexToRgb(hex);

    return rgbToHex(
        r + (255 - r) * amount,
        g + (255 - g) * amount,
        b + (255 - b) * amount
    );
}

function darkenHex(hex, amount = 0.18) {
    const { r, g, b } = hexToRgb(hex);

    return rgbToHex(
        r * (1 - amount),
        g * (1 - amount),
        b * (1 - amount)
    );
}
</script>

<style scoped>
.hide-scrollbar {
    scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
