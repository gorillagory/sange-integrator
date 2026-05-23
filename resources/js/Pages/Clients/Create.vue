<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Service Records', url: null },
                { label: 'Client Directory', url: '/clients' },
                { label: 'Onboard Contract', url: null }
            ]" />
        </template>

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Corporate Onboarding Matrix</h1>
            <p class="text-sm text-gray-500 mt-1">Connect global entities to your local tenant contracts.</p>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <div class="lg:col-span-8 space-y-6">

                <DataCard>
                    <template #header>
                        <div class="flex justify-between items-center w-full">
                            <div class="font-bold text-gray-800">1. Global Client Identity</div>

                            <div class="flex bg-gray-100 p-1 rounded-lg">
                                <button type="button" @click="form.selection_mode = 'existing'" :class="form.selection_mode === 'existing' ? 'bg-white shadow text-[var(--brand-600)]' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 text-xs font-bold rounded-md transition-all">
                                    Existing Client
                                </button>
                                <button type="button" @click="form.selection_mode = 'new'" :class="form.selection_mode === 'new' ? 'bg-white shadow text-[var(--brand-600)]' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 text-xs font-bold rounded-md transition-all">
                                    New Client
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="p-6">
                        <div v-if="form.selection_mode === 'existing'" class="space-y-4">
                            <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 mb-4">
                                <p class="text-xs text-blue-800">Search the Nexus Global Database. If another subsidiary (e.g. Bayamedic) already onboarded this client, you can securely attach a local contract to them here.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Select Global Entity <span class="text-red-500">*</span></label>
                                <select v-model="form.client_id" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-900 focus:border-[var(--brand-500)]">
                                    <option :value="null" disabled>Select corporate entity...</option>
                                    <option v-for="client in globalClients" :key="client.id" :value="client.id">
                                        {{ client.name }} {{ client.registration_number ? `(${client.registration_number})` : '' }}
                                    </option>
                                </select>
                                <span v-if="form.errors.client_id" class="text-sm text-red-600 mt-1">{{ form.errors.client_id }}</span>
                            </div>
                        </div>

                        <div v-if="form.selection_mode === 'new'" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Company Name <span class="text-red-500">*</span></label>
                                <TextInput v-model="form.name" :error="form.errors.name" placeholder="e.g. Petronas Carigali Sdn Bhd" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Registration Number</label>
                                <TextInput v-model="form.registration_number" placeholder="e.g. 1234567-X" />
                            </div>
                            <div class="sm:col-span-2 pt-4 border-t border-gray-100 mt-2">
                                <h4 class="text-xs font-bold text-gray-800 mb-4">GLOBAL HQ CONTACT</h4>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">HQ Contact Person <span class="text-red-500">*</span></label>
                                <TextInput v-model="form.hq_contact_person" :error="form.errors.hq_contact_person" placeholder="e.g. Ahmad Albab" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">HQ Email <span class="text-red-500">*</span></label>
                                <TextInput type="email" v-model="form.hq_contact_email" :error="form.errors.hq_contact_email" placeholder="ahmad@company.com" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Client Address</label>
                                <textarea
                                    v-model="form.address"
                                    rows="3"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)] transition-colors"
                                    placeholder="Headquarters or primary client address..."
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Client Logo</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900"
                                    @change="form.logo = $event.target.files?.[0] || null"
                                >
                                <p class="mt-1 text-xs text-gray-400">Optional. PNG, JPG, or WEBP up to 3MB.</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Client Profile</label>
                                <textarea
                                    v-model="form.profile"
                                    rows="4"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)] transition-colors"
                                    placeholder="Operating notes, profile summary, approval style, or any identity context helpful to the team..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </DataCard>

                <DataCard>
                    <template #header><div class="font-bold text-gray-800">2. Local Contract Framework</div></template>
                    <div class="p-6 space-y-6">

                        <div class="flex justify-between items-center mb-2">
                            <p class="text-xs text-gray-500">Define the specific billing terms and contract numbers for your specific branch (Bayam Travel).</p>
                            <button @click.prevent="addContract" class="text-xs font-bold text-[var(--brand-600)] hover:text-[var(--brand-800)] px-3 py-1.5 bg-[var(--brand-50)] rounded-lg">+ Add Contract Line</button>
                        </div>

                        <div v-for="(contract, index) in form.contracts" :key="index" class="bg-gray-50 border border-gray-200 rounded-xl p-5 relative group">

                            <button v-if="form.contracts.length > 1" @click.prevent="removeContract(index)" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Contract Number <span class="text-red-500">*</span></label>
                                    <TextInput v-model="contract.contract_no" placeholder="e.g. BAYAM 323" class="!text-sm" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Contract Title <span class="text-red-500">*</span></label>
                                    <TextInput v-model="contract.title" placeholder="e.g. Provision of Travel Management" class="!text-sm" required />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Billing Address (For Invoices) <span class="text-red-500">*</span></label>
                                    <textarea v-model="contract.billing_address" rows="2" class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)] transition-colors" placeholder="Full registered address for invoicing..." required></textarea>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Payment Terms</label>
                                    <SelectInput v-model="contract.payment_terms" class="!text-sm">
                                        <option value="CIA">Cash In Advance (CIA)</option>
                                        <option value="7 Days">7 Days</option>
                                        <option value="14 Days">14 Days</option>
                                        <option value="30 Days">30 Days</option>
                                        <option value="60 Days">60 Days</option>
                                    </SelectInput>
                                </div>
                            </div>
                        </div>

                    </div>
                </DataCard>

            </div>

            <div class="lg:col-span-4 space-y-6 sticky top-6">
                <DataCard>
                    <template #header><div class="font-bold text-gray-800">Action Center</div></template>
                    <div class="p-6">
                        <p class="text-xs text-gray-500 mb-6">Review your inputs carefully. If registering a new Global Client, ensure the spelling matches official documentation to prevent duplicate entries across the Nexus ecosystem.</p>

                        <PrimaryButton type="submit" :disabled="form.processing" class="w-full py-3">
                            Authorize & Save Configuration
                        </PrimaryButton>
                    </div>
                </DataCard>
            </div>

        </form>
    </TenantLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';
import DataCard from '../../Components/UI/DataCard.vue';
import TextInput from '../../Components/Form/TextInput.vue';
import SelectInput from '../../Components/Form/SelectInput.vue';
import PrimaryButton from '../../Components/Form/PrimaryButton.vue';

const props = defineProps({
    globalClients: Array
});

const form = useForm({
    selection_mode: 'existing', // Default to existing to prevent duplicates
    client_id: null,

    // New Client Data
    name: '',
    registration_number: '',
    hq_contact_person: '',
    hq_contact_email: '',
    address: '',
    profile: '',
    logo: null,

    // Local Contracts Array
    contracts: [
        {
            contract_no: '',
            title: '',
            billing_address: '',
            payment_terms: '30 Days'
        }
    ]
});

const addContract = () => {
    form.contracts.push({
        contract_no: '',
        title: '',
        billing_address: '',
        payment_terms: '30 Days'
    });
};

const removeContract = (index) => {
    form.contracts.splice(index, 1);
};

const submit = () => {
    form.post('/clients', {
        forceFormData: true,
    });
};
</script>
