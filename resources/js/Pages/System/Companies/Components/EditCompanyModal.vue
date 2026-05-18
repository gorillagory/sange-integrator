<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/70 px-4 py-6"
    >
        <div class="mx-auto flex min-h-full w-full max-w-5xl items-start justify-center">
            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl">
                <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-white">Edit Company</h2>
                        <p class="mt-1 text-sm text-slate-400">Update company profile and logo.</p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                        @click="$emit('close')"
                    >
                        Close
                    </button>
                </div>

                <form class="flex flex-col" @submit.prevent="submit">
                    <div class="max-h-[calc(100vh-180px)] overflow-y-auto px-6 py-6">
                        <div class="space-y-6 rounded-2xl border border-white/10 bg-slate-900 p-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Company Name</label>
                                    <input
                                        v-model="form.company.name"
                                        type="text"
                                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    >
                                    <p v-if="form.errors['company.name']" class="mt-2 text-sm text-red-400">{{ form.errors['company.name'] }}</p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Registration Number</label>
                                    <input
                                        v-model="form.company.registration_number"
                                        type="text"
                                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    >
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Subdomain</label>
                                    <input
                                        v-model="form.company.subdomain"
                                        type="text"
                                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    >
                                    <p v-if="form.errors['company.subdomain']" class="mt-2 text-sm text-red-400">{{ form.errors['company.subdomain'] }}</p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Industry</label>
                                    <select
                                        v-model="form.company.industry"
                                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    >
                                        <option value="">Select industry</option>
                                        <option
                                            v-for="industry in industries"
                                            :key="industry"
                                            :value="industry"
                                        >
                                            {{ industry }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors['company.industry']" class="mt-2 text-sm text-red-400">{{ form.errors['company.industry'] }}</p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Main Group</label>
                                    <select
                                        v-model="form.company.main_group_company_id"
                                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    >
                                        <option :value="null">No group</option>
                                        <option
                                            v-for="group in mainGroupCompanies"
                                            :key="group.id"
                                            :value="group.id"
                                        >
                                            {{ group.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Theme Color</label>
                                    <input
                                        v-model="form.company.theme_color"
                                        type="text"
                                        placeholder="#4f46e5"
                                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    >
                                </div>

                                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950 px-4 py-4 text-sm text-slate-200 md:col-span-2">
                                    <input
                                        v-model="form.company.is_active"
                                        type="checkbox"
                                        class="h-4 w-4 border-white/20 bg-slate-900 text-indigo-500"
                                    >
                                    <span>Active company</span>
                                </label>
                            </div>

                            <AddressFields
                                title="Company Address"
                                :model-value="form.company.address"
                                @update:model-value="form.company.address = $event"
                            />

                            <PhoneListEditor
                                title="Company Phones"
                                :model-value="form.company.phones"
                                @update:model-value="form.company.phones = $event"
                            />

                            <EnterpriseTypesEditor
                                title="Company Enterprise Types"
                                :model-value="form.company.enterprise_types"
                                @update:model-value="form.company.enterprise_types = $event"
                            />

                            <LogoUploader
                                title="Company Logo"
                                :model-value="form.company.logo"
                                :existing-path="company?.logo_path || ''"
                                @update:model-value="form.company.logo = $event"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-white/10 bg-slate-950 px-6 py-4">
                        <button
                            type="button"
                            class="rounded-xl border border-white/10 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5"
                            @click="$emit('close')"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Company' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import AddressFields from './AddressFields.vue';
import PhoneListEditor from './PhoneListEditor.vue';
import EnterpriseTypesEditor from './EnterpriseTypesEditor.vue';
import LogoUploader from './LogoUploader.vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    company: {
        type: Object,
        default: null,
    },
    industries: {
        type: Array,
        default: () => [],
    },
    mainGroupCompanies: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const blankAddress = () => ({
    line1: '',
    line2: '',
    city: '',
    state: '',
    postcode: '',
    country: '',
});

const blankPhone = () => ({
    uid: `${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
    label: '',
    type: '',
    number: '',
});

const normalizePhones = (phones = []) => {
    if (!Array.isArray(phones) || phones.length === 0) {
        return [blankPhone()];
    }

    return phones.map((phone) => ({
        uid: phone.uid || `${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
        label: phone.label || '',
        type: phone.type || '',
        number: phone.number || '',
    }));
};

const form = useForm({
    company: {
        main_group_company_id: null,
        name: '',
        registration_number: '',
        subdomain: '',
        industry: '',
        address: blankAddress(),
        phones: [blankPhone()],
        enterprise_types: [],
        logo: null,
        theme_color: '',
        is_active: true,
    },
});

watch(
    () => [props.open, props.company],
    ([open, company]) => {
        if (!open || !company) {
            return;
        }

        form.defaults({
            company: {
                main_group_company_id: company.main_group_company?.id ?? null,
                name: company.name || '',
                registration_number: company.registration_number || '',
                subdomain: company.subdomain || '',
                industry: company.industry || '',
                address: company.address || blankAddress(),
                phones: normalizePhones(company.phones),
                enterprise_types: Array.isArray(company.enterprise_types) ? [...company.enterprise_types] : [],
                logo: null,
                theme_color: company.theme_color || '',
                is_active: Boolean(company.is_active),
            },
        });

        form.reset();
        form.clearErrors();
    },
    { immediate: true }
);

const sanitizePhones = (phones) => {
    return phones
        .map((phone) => ({
            label: phone.label,
            type: phone.type,
            number: phone.number,
        }))
        .filter((phone) => phone.label || phone.type || phone.number);
};

const sanitizeEnterpriseTypes = (types) => {
    return types
        .map((type) => String(type).trim())
        .filter(Boolean);
};

const submit = () => {
    if (!props.company?.id) {
        return;
    }

    form.transform((data) => ({
        company: {
            ...data.company,
            phones: sanitizePhones(data.company.phones),
            enterprise_types: sanitizeEnterpriseTypes(data.company.enterprise_types),
        },
    })).put(`/companies/${props.company.id}`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            emit('close');
            router.reload({
                preserveScroll: true,
                only: ['companies', 'mainGroupCompanies', 'ungroupedCompanies', 'filters', 'metrics'],
            });
        },
    });
};
</script>
