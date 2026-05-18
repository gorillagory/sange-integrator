<template>
    <TenantLayout>
        <div class="space-y-6">
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="px-6 py-8 text-white"
                    :style="{
                        background: `linear-gradient(135deg, ${brand700} 0%, ${brand500} 100%)`,
                    }"
                >
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-3xl border border-white/20 bg-white/10 shadow-lg">
                                <img
                                    v-if="companyLogoUrl"
                                    :src="companyLogoUrl"
                                    :alt="`${companyName} logo`"
                                    class="h-full w-full object-cover"
                                >

                                <div
                                    v-else
                                    class="text-2xl font-black text-white"
                                >
                                    {{ companyInitial }}
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="text-xs font-bold uppercase tracking-[0.24em] text-white/70">
                                    Tenant Dashboard
                                </div>
                                <h1 class="mt-2 text-3xl font-black tracking-tight">
                                    {{ companyName }}
                                </h1>
                                <p class="mt-2 max-w-2xl text-sm text-white/80">
                                    Welcome back. Your workspace theme, branding, and navigation now follow the company identity.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <Link
                                v-for="link in quickLinks"
                                :key="link.href"
                                :href="link.href"
                                class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/20"
                            >
                                {{ link.label }}
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="metric in metrics"
                    :key="metric.title"
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl border"
                        :style="{
                            backgroundColor: tintHex(brand500, 0.9),
                            borderColor: tintHex(brand500, 0.78),
                            color: brand500,
                        }"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="metric.icon"
                            />
                        </svg>
                    </div>

                    <h3 class="text-sm font-medium text-slate-500">
                        {{ metric.title }}
                    </h3>
                    <p class="mt-1 text-3xl font-black text-slate-900">
                        {{ metric.value }}
                    </p>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.3fr,0.7fr]">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Workspace Overview
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Core operational modules linked for this company workspace.
                            </p>
                        </div>

                        <span
                            class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.2em]"
                            :style="{ backgroundColor: brand50, color: brand700 }"
                        >
                            {{ currentCompany?.industry || 'tenant' }}
                        </span>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <Link
                            v-for="module in modules"
                            :key="module.href"
                            :href="module.href"
                            class="rounded-2xl border border-slate-200 p-5 transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md"
                        >
                            <div
                                class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em]"
                                :style="{ backgroundColor: '#f8fafc', color: brand500 }"
                            >
                                Module
                            </div>

                            <div class="mt-4 text-base font-bold text-slate-900">
                                {{ module.title }}
                            </div>

                            <p class="mt-2 text-sm text-slate-500">
                                {{ module.description }}
                            </p>
                        </Link>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">
                        Company Identity
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Tenant branding loaded from the company record.
                    </p>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                Company
                            </div>
                            <div class="mt-2 text-sm font-semibold text-slate-900">
                                {{ companyName }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                Subdomain
                            </div>
                            <div class="mt-2 text-sm font-semibold text-slate-900">
                                {{ currentCompany?.subdomain || 'n/a' }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                Theme Color
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <div
                                    class="h-8 w-8 rounded-xl border border-slate-200"
                                    :style="{ backgroundColor: brand500 }"
                                />
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ brand500 }}
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                                Logo Status
                            </div>
                            <div class="mt-2 text-sm font-semibold text-slate-900">
                                {{ companyLogoUrl ? 'Configured' : 'Fallback Initial Active' }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </TenantLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import TenantLayout from '@/Layouts/TenantLayout.vue';

const page = usePage();

const currentCompany = computed(() => page.props.currentCompany ?? null);
const companyName = computed(() => currentCompany.value?.name || 'Tenant Workspace');
const companyInitial = computed(() => companyName.value.charAt(0).toUpperCase());

const companyLogoUrl = computed(() => {
    const path = currentCompany.value?.logo_path;

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

const brand500 = computed(() => normalizeHex(currentCompany.value?.theme_color || '#4f46e5'));
const brand700 = computed(() => darkenHex(brand500.value, 0.22));
const brand50 = computed(() => tintHex(brand500.value, 0.92));

const quickLinks = computed(() => [
    { href: '/service-records', label: 'Service Records' },
    { href: '/clients', label: 'Clients' },
    { href: '/reports', label: 'Reports' },
    { href: '/profile', label: 'Profile' },
]);

const metrics = computed(() => [
    {
        title: 'Active Records',
        value: '24,591',
        icon: 'M13 10V3L4 14h7v7l9-11h-7z',
    },
    {
        title: 'Open Service Records',
        value: '128',
        icon: 'M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z',
    },
    {
        title: 'Active Clients',
        value: '64',
        icon: 'M17 20h5v-1a4 4 0 00-5-3.87M9 20H4v-1a4 4 0 015-3.87m8-6.13a4 4 0 11-8 0 4 4 0 018 0z',
    },
    {
        title: 'Document Templates',
        value: '12',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
]);

const modules = computed(() => [
    {
        href: '/service-records',
        title: 'Service Records',
        description: 'Capture dynamic service records, payloads, and routing data.',
    },
    {
        href: '/clients',
        title: 'Clients',
        description: 'Manage client records and linked contracts.',
    },
    {
        href: '/admin/schemas',
        title: 'Schema Vectors',
        description: 'Maintain service schemas and configuration vectors.',
    },
    {
        href: '/admin/documents',
        title: 'Documents',
        description: 'Manage document templates and generated output flows.',
    },
]);

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
