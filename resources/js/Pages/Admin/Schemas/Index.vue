<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Admin Settings', url: null },
                { label: 'Service Vectors', url: null }
            ]" />
        </template>

        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Service Vectors</h1>
                <p class="text-sm text-gray-500 mt-1">Manage dynamic operational schemas for cross-industry bookings.</p>
            </div>
            <Link href="/admin/schemas/create" class="px-5 py-2.5 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-xl transition shadow flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Vector
            </Link>
        </div>

        <div v-if="schemas.length === 0" class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
            <h3 class="text-lg font-bold text-gray-900">No Vectors Found</h3>
            <p class="text-sm text-gray-500 mt-1 mb-6">You have not deployed any service vectors yet.</p>
            <Link href="/admin/schemas/create" class="text-sm font-bold text-[var(--brand-600)]">Start Building &rarr;</Link>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-12">
            <div v-for="schema in schemas" :key="schema.id" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition group relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[var(--brand-400)] to-[var(--brand-600)] opacity-0 group-hover:opacity-100 transition"></div>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900">{{ schema.display_name }}</h3>
                        <span class="text-[10px] font-mono text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ schema.service_type }}</span>
                    </div>
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-100 px-2 py-1 rounded uppercase tracking-wider">{{ schema.industry }}</span>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 mb-6 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-500 uppercase">Data Attributes</span>
                    <span class="text-sm font-black text-[var(--brand-600)]">{{ getFieldCount(schema.schema_payload) }} Nodes</span>
                </div>

                <div class="flex gap-2">
                    <Link :href="`/admin/schemas/${schema.id}/edit`" class="flex-1 flex justify-center items-center bg-gray-50 hover:bg-[var(--brand-50)] text-gray-700 text-xs font-bold py-2 rounded-lg transition border border-gray-200">
                        Edit Vector
                    </Link>
                    <button @click="confirmDeletion(schema)" class="px-3 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-600 border border-gray-200 hover:border-red-200 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="deleteState.isOpen"
            title="Delete Service Vector?"
            :message="`Are you sure you want to delete '${deleteState.activeSchema?.display_name}'? This action cannot be undone.`"
            @close="deleteState.isOpen = false"
            @confirm="performDelete"
        />

        <GlobalToast />
    </TenantLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import TenantLayout from '../../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../../Components/UI/Breadcrumbs.vue';
import ConfirmationModal from '../../../Components/UI/ConfirmationModal.vue';
import GlobalToast from '../../../Components/GlobalToast.vue';

defineProps({ schemas: Array });

const deleteState = reactive({ isOpen: false, activeSchema: null });

const getFieldCount = (payload) => {
    try {
        const parsed = typeof payload === 'string' ? JSON.parse(payload) : payload;
        return parsed.fields?.length || 0;
    } catch (e) { return 0; }
};

const confirmDeletion = (schema) => {
    deleteState.activeSchema = schema;
    deleteState.isOpen = true;
};

const performDelete = () => {
    router.delete(`/admin/schemas/${deleteState.activeSchema.id}`, {
        onSuccess: () => { deleteState.isOpen = false; }
    });
};
</script>
