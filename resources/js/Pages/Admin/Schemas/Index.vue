<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Admin Settings', url: null },
                { label: 'Schema Manager', url: null }
            ]" />
        </template>

        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            <div class="font-bold">Tenant schema access is intentionally lightweight.</div>
            <div class="mt-1 text-amber-800">
                Structure design, versioning, and blueprint changes are governed centrally in Blueprint Forge.
                This screen is for reviewing what is available to your team today.
            </div>
        </div>

        <div class="mb-8 flex items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Schema Vector Manager</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Review centrally forged schema vectors for your industry before using them in service records.
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-right shadow-sm">
                <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">Published Vectors</div>
                <div class="mt-1 text-2xl font-black text-[var(--brand-700)]">{{ schemas.length }}</div>
            </div>
        </div>

        <div v-if="schemas.length === 0" class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-sm">
            <h3 class="text-lg font-bold text-gray-900">No schema vectors are available yet</h3>
            <p class="mx-auto mt-2 max-w-2xl text-sm text-gray-500">
                Your tenant can only use vectors that have already been forged centrally for this industry.
                Ask the platform team to publish a new vector if you need a new structure.
            </p>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 pb-12 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="schema in schemas"
                :key="schema.id"
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ schema.service_name || schema.display_name }}</h3>
                        <div class="mt-1 text-[11px] font-mono text-gray-500">
                            {{ schema.service_code || schema.service_type }}
                        </div>
                    </div>
                    <span
                        class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em]"
                        :class="statusClasses(schema.status)"
                    >
                        {{ schema.status || 'draft' }}
                    </span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                        <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Version</div>
                        <div class="mt-1 text-sm font-bold text-gray-900">v{{ schema.version || 1 }}</div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                        <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Default</div>
                        <div class="mt-1 text-sm font-bold" :class="schema.is_default ? 'text-emerald-700' : 'text-gray-500'">
                            {{ schema.is_default ? 'Primary' : 'Secondary' }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                        <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Industry</div>
                        <div class="mt-1 text-sm font-bold text-gray-900">{{ schema.industry }}</div>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                        <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Fields</div>
                        <div class="mt-1 text-sm font-bold text-[var(--brand-700)]">{{ getFieldCount(schema.schema_payload) }}</div>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">Practical Note</div>
                    <p class="mt-2 text-sm text-gray-600">
                        Use this vector as a governed contract in service records. Structural changes belong in Blueprint Forge.
                    </p>
                </div>

                <div class="mt-5">
                    <Link
                        :href="`/admin/schemas/${schema.id}/edit`"
                        class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:border-[var(--brand-300)] hover:bg-[var(--brand-50)] hover:text-[var(--brand-700)]"
                    >
                        Review Vector
                    </Link>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import TenantLayout from '../../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../../Components/UI/Breadcrumbs.vue';

defineProps({
    schemas: {
        type: Array,
        default: () => [],
    },
});

const getFieldCount = (payload) => {
    try {
        const parsed = typeof payload === 'string' ? JSON.parse(payload) : payload;
        return parsed?.fields?.length || 0;
    } catch (error) {
        return 0;
    }
};

const statusClasses = (status) => {
    switch ((status || '').toLowerCase()) {
        case 'active':
            return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
        case 'deprecated':
            return 'bg-amber-50 text-amber-700 border border-amber-200';
        case 'archived':
            return 'bg-gray-100 text-gray-600 border border-gray-200';
        default:
            return 'bg-sky-50 text-sky-700 border border-sky-200';
    }
};
</script>
