<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Service Records', url: null },
                { label: 'Corporate Directory', url: '/clients' }
            ]" />
        </template>

        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Corporate Directory</h1>
                <p class="text-sm text-gray-500 mt-1">Manage active B2B clients and their localized contracts.</p>
            </div>
            <a href="/clients/create" class="px-5 py-2.5 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-xl transition shadow-lg shadow-brand-500/20 flex items-center gap-2">
                Onboard Global Client
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase">Corporate Entity</th>
                        <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase">Active Contracts (Click to Edit)</th>
                        <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase">HQ Contact</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="client in clients.data" :key="client.id" class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-4 px-6">
                            <div class="flex items-start gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-gray-50">
                                    <img v-if="resolveLogoUrl(client.logo_url || client.logo_path)" :src="resolveLogoUrl(client.logo_url || client.logo_path)" :alt="client.name" class="h-full w-full object-cover">
                                    <span v-else class="text-sm font-black uppercase tracking-[0.18em] text-gray-500">{{ clientInitials(client.name) }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="font-bold text-gray-900">{{ client.name }}</div>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-[var(--brand-200)] bg-[var(--brand-50)] px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.15em] text-[var(--brand-700)] transition hover:bg-[var(--brand-100)]"
                                            @click="openClientModal(client)"
                                        >
                                            Edit Client
                                        </button>
                                    </div>
                                    <div class="text-xs text-gray-500">Reg: {{ client.registration_number || 'N/A' }}</div>
                                    <div v-if="client.profile" class="mt-2 line-clamp-2 text-xs leading-5 text-gray-600">{{ client.profile }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-6">
                            <div class="flex flex-wrap gap-2 items-center">
                                <button v-for="contract in client.contracts" :key="contract.id" @click="openModal('edit', client, contract)" :title="contract.title" class="inline-flex items-center px-2.5 py-1 rounded-md bg-blue-50 hover:bg-blue-600 hover:text-white text-[var(--brand-700)] text-xs font-bold border border-blue-100 font-mono transition-colors shadow-sm">
                                    {{ contract.contract_no }}
                                </button>

                                <button @click="openModal('add', client)" class="inline-flex items-center px-2 py-1 rounded-md bg-gray-50 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 text-gray-500 text-xs font-bold border border-dashed border-gray-300 transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Add
                                </button>
                            </div>
                        </td>

                        <td class="py-4 px-6">
                            <div class="text-sm text-gray-700 font-medium">{{ client.hq_contact_person || '—' }}</div>
                            <div class="text-xs text-gray-500">{{ client.hq_contact_email }}</div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform transition-all">

                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ modalMode === 'add' ? 'Attach New Contract' : 'Edit Contract' }}
                        </h3>
                        <p class="text-xs text-gray-500">{{ activeClient?.name }}</p>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form @submit.prevent="submitContract" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Contract Number</label>
                        <input v-model="form.contract_no" type="text" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]" required placeholder="e.g. BAYAM-404">
                        <span v-if="form.errors.contract_no" class="text-xs text-red-500 mt-1">{{ form.errors.contract_no }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Contract Title</label>
                        <input v-model="form.title" type="text" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]" required placeholder="e.g. Provision of Logistics">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Billing Address</label>
                        <textarea v-model="form.billing_address" rows="2" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]" required></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Payment Terms</label>
                        <select v-model="form.payment_terms" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]">
                            <option value="CIA">Cash In Advance (CIA)</option>
                            <option value="7 Days">7 Days</option>
                            <option value="14 Days">14 Days</option>
                            <option value="30 Days">30 Days</option>
                            <option value="60 Days">60 Days</option>
                        </select>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-gray-100 mt-6">
                        <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-gray-900 transition">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-lg transition shadow-lg">
                            {{ modalMode === 'add' ? 'Save Contract' : 'Update Contract' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="isClientModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Edit Client Identity</h3>
                        <p class="text-xs text-gray-500">{{ activeClientForEdit?.name }}</p>
                    </div>
                    <button @click="closeClientModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form @submit.prevent="submitClient" class="p-6 space-y-5">
                    <div class="grid gap-5 md:grid-cols-[120px_minmax(0,1fr)] md:items-start">
                        <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-gray-50">
                            <img v-if="clientLogoPreview" :src="clientLogoPreview" :alt="activeClientForEdit?.name" class="h-full w-full object-cover">
                            <span v-else class="text-lg font-black uppercase tracking-[0.2em] text-gray-500">{{ clientInitials(activeClientForEdit?.name || 'Client') }}</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Client Logo</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900"
                                    @change="handleClientLogoChange"
                                >
                                <p class="mt-1 text-xs text-gray-400">Optional. Upload a fresh identity mark for documents and directories.</p>
                                <span v-if="clientForm.errors.logo" class="text-xs text-red-500 mt-1">{{ clientForm.errors.logo }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Company Name</label>
                                <input v-model="clientForm.name" type="text" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]" required>
                                <span v-if="clientForm.errors.name" class="text-xs text-red-500 mt-1">{{ clientForm.errors.name }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Registration Number</label>
                            <input v-model="clientForm.registration_number" type="text" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">HQ Contact Person</label>
                            <input v-model="clientForm.hq_contact_person" type="text" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">HQ Contact Email</label>
                            <input v-model="clientForm.hq_contact_email" type="email" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Client Address</label>
                            <textarea v-model="clientForm.address" rows="3" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]"></textarea>
                            <span v-if="clientForm.errors.address" class="text-xs text-red-500 mt-1">{{ clientForm.errors.address }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Client Profile</label>
                            <textarea v-model="clientForm.profile" rows="5" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:border-[var(--brand-500)]"></textarea>
                            <span v-if="clientForm.errors.profile" class="text-xs text-red-500 mt-1">{{ clientForm.errors.profile }}</span>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-gray-100 mt-6">
                        <button type="button" @click="closeClientModal" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-gray-900 transition">Cancel</button>
                        <button type="submit" :disabled="clientForm.processing" class="px-5 py-2 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-lg transition shadow-lg">
                            Save Client
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </TenantLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';

defineProps({
    clients: Object
});

// Modal State Matrix
const isModalOpen = ref(false);
const modalMode = ref('add');
const activeClient = ref(null);
const activeContractId = ref(null);
const isClientModalOpen = ref(false);
const activeClientForEdit = ref(null);

const form = useForm({
    client_id: null,
    contract_no: '',
    title: '',
    billing_address: '',
    payment_terms: '30 Days'
});

const clientForm = useForm({
    name: '',
    registration_number: '',
    hq_contact_person: '',
    hq_contact_email: '',
    address: '',
    profile: '',
    logo: null,
});

const clientLogoPreview = computed(() => {
    if (clientForm.logo instanceof File) {
        return URL.createObjectURL(clientForm.logo);
    }

    return resolveLogoUrl(activeClientForEdit.value?.logo_url || activeClientForEdit.value?.logo_path);
});

const openModal = (mode, client, contract = null) => {
    modalMode.value = mode;
    activeClient.value = client;
    form.clearErrors();

    if (mode === 'add') {
        form.reset();
        form.client_id = client.id;
        activeContractId.value = null;
    } else {
        form.client_id = client.id;
        activeContractId.value = contract.id;
        form.contract_no = contract.contract_no;
        form.title = contract.title;
        form.billing_address = contract.billing_address;
        form.payment_terms = contract.payment_terms;
    }

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const openClientModal = (client) => {
    activeClientForEdit.value = client;
    clientForm.reset();
    clientForm.clearErrors();
    clientForm.name = client.name || '';
    clientForm.registration_number = client.registration_number || '';
    clientForm.hq_contact_person = client.hq_contact_person || '';
    clientForm.hq_contact_email = client.hq_contact_email || '';
    clientForm.address = client.address || '';
    clientForm.profile = client.profile || '';
    clientForm.logo = null;
    isClientModalOpen.value = true;
};

const closeClientModal = () => {
    isClientModalOpen.value = false;
    clientForm.reset();
    activeClientForEdit.value = null;
};

const handleClientLogoChange = (event) => {
    clientForm.logo = event.target.files?.[0] || null;
};

const submitContract = () => {
    if (modalMode.value === 'add') {
        form.post('/contracts', {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    } else {
        form.put(`/contracts/${activeContractId.value}`, {
            preserveScroll: true,
            onSuccess: () => closeModal()
        });
    }
};

const submitClient = () => {
    if (!activeClientForEdit.value) {
        return;
    }

    clientForm.put(`/clients/${activeClientForEdit.value.id}`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => closeClientModal(),
    });
};

const clientInitials = (name) => {
    return String(name || '')
        .split(/[^A-Za-z0-9]+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() || '')
        .join('') || 'CL';
};

const resolveLogoUrl = (path) => {
    if (!path) {
        return '';
    }

    if (String(path).startsWith('http://') || String(path).startsWith('https://') || String(path).startsWith('data:') || String(path).startsWith('blob:')) {
        return path;
    }

    return String(path).startsWith('/storage/')
        ? path
        : `/storage/${String(path).replace(/^storage\//, '')}`;
};
</script>
