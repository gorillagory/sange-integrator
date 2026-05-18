<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <link rel="icon" :href="faviconUrl">
    </Head>

    <div class="min-h-screen bg-slate-100 text-slate-900" :style="themeStyles">
        <div class="grid min-h-screen lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
            <section class="relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between" :style="{ backgroundColor: brand900 }">
                <div class="absolute inset-0 bg-[linear-gradient(150deg,rgba(255,255,255,0.12),rgba(255,255,255,0.02))]" />

                <div class="relative px-12 pt-12">
                    <div class="inline-flex items-center gap-3 rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-white/90">
                        <img
                            v-if="tenantLogo"
                            :src="tenantLogo"
                            :alt="`${tenantName} logo`"
                            class="h-8 w-8 rounded-lg object-cover"
                        >
                        <div
                            v-else
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-sm font-black"
                        >
                            {{ identityInitial }}
                        </div>
                        <div class="text-sm font-semibold">{{ systemName }}</div>
                    </div>
                </div>

                <div class="relative px-12 pb-12 text-white">
                    <div class="text-sm font-semibold uppercase tracking-[0.14em] text-white/70">
                        {{ identityTagline }}
                    </div>
                    <h1 class="mt-4 text-4xl font-black leading-tight">{{ identityTitle }}</h1>
                    <p class="mt-4 max-w-lg text-base text-white/80">{{ identitySubtitle }}</p>
                </div>
            </section>

            <section class="flex items-center justify-center px-4 py-8 sm:px-8 lg:px-12">
                <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-7 shadow-xl shadow-slate-200/70 sm:p-8">
                    <div class="mb-6 flex items-center gap-3">
                        <img
                            v-if="tenantLogo"
                            :src="tenantLogo"
                            :alt="`${tenantName} logo`"
                            class="h-11 w-11 rounded-xl border border-slate-200 object-cover"
                        >
                        <div
                            v-else
                            class="flex h-11 w-11 items-center justify-center rounded-xl text-base font-black text-white"
                            :style="{ backgroundColor: brand600 }"
                        >
                            {{ identityInitial }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold uppercase tracking-[0.14em] text-slate-500">
                                {{ systemName }}
                            </div>
                            <div class="truncate text-lg font-black text-slate-900">
                                {{ identityHeader }}
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="form.errors.email || form.errors.password"
                        class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                    >
                        {{ form.errors.email || form.errors.password }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 outline-none transition focus:border-[var(--brand-600)] focus:ring-2 focus:ring-[var(--brand-100)]"
                                required
                            >
                            <span v-if="form.errors.email" class="mt-1 block text-xs text-rose-600">
                                {{ form.errors.email }}
                            </span>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Password</label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 outline-none transition focus:border-[var(--brand-600)] focus:ring-2 focus:ring-[var(--brand-100)]"
                                required
                            >
                            <span v-if="form.errors.password" class="mt-1 block text-xs text-rose-600">
                                {{ form.errors.password }}
                            </span>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="mt-2 w-full rounded-xl px-4 py-3 text-sm font-bold text-white transition disabled:cursor-not-allowed disabled:opacity-60"
                            :style="{ backgroundColor: brand600 }"
                        >
                            {{ form.processing ? 'Authenticating...' : 'Sign In' }}
                        </button>
                    </form>

                    <div class="mt-5 text-xs text-slate-500">
                        {{ identityFooter }}
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const brand = computed(() => page.props?.brand ?? {});
const systemName = computed(() => brand.value?.system_name || 'Sange Integrator');
const tenant = computed(() => brand.value?.tenant ?? null);
const tenantName = computed(() => tenant.value?.name || 'System Workspace');
const tenantLogo = computed(() => tenant.value?.logo_url || null);
const subdomain = computed(() => tenant.value?.subdomain || null);
const tenantHost = computed(() => tenant.value?.host || (subdomain.value && brand.value?.base_domain
    ? `${subdomain.value}.${brand.value.base_domain}`
    : null));
const isTenantLogin = computed(() => Boolean(tenant.value?.id));
const faviconUrl = computed(() => brand.value?.favicon_url || '/favicon.ico');

const form = useForm({
    email: '',
    password: 'password',
    remember: false,
});

const brand500 = computed(() => normalizeHex(tenant.value?.theme_color || '#4f46e5'));
const brand600 = computed(() => darkenHex(brand500.value, 0.12));
const brand900 = computed(() => darkenHex(brand500.value, 0.35));
const brand100 = computed(() => tintHex(brand500.value, 0.86));

const themeStyles = computed(() => ({
    '--brand-100': brand100.value,
    '--brand-600': brand600.value,
}));

const identityInitial = computed(() => {
    const source = isTenantLogin.value ? tenantName.value : systemName.value;

    return source.charAt(0).toUpperCase();
});

const pageTitle = computed(() => {
    if (isTenantLogin.value) {
        return `${tenantName.value} Login | ${systemName.value}`;
    }

    return `System Login | ${systemName.value}`;
});

const identityTagline = computed(() => isTenantLogin.value
    ? tenantHost.value
    : 'System Console');

const identityTitle = computed(() => isTenantLogin.value
    ? tenantName.value
    : systemName.value);

const identitySubtitle = computed(() => isTenantLogin.value
    ? `You are signing in to the ${tenantName.value} tenant workspace.`
    : 'Central access for company governance, audit, and module administration.');

const identityHeader = computed(() => isTenantLogin.value
    ? `${tenantName.value} Login`
    : 'System Login');

const identityFooter = computed(() => isTenantLogin.value
    ? `Signing in on ${tenantHost.value}`
    : 'Signing in to the central system domain.');

const submit = () => {
    form.post('/login');
};

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
