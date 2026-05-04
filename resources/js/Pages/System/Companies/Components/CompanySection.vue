<template>
    <div class="space-y-6 rounded-2xl border border-white/10 bg-slate-900 p-6">
        <div>
            <h2 class="text-xl font-bold text-white">Subsidiary Company</h2>
            <p class="mt-1 text-sm text-slate-400">
                This company will be provisioned with its own tenant database.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Company Name</label>
                <input
                    :value="company.name"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updateCompanyField('name', $event.target.value)"
                >
                <p v-if="errors['company.name']" class="mt-2 text-sm text-red-400">
                    {{ errors['company.name'] }}
                </p>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Registration Number</label>
                <input
                    :value="company.registration_number"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updateCompanyField('registration_number', $event.target.value)"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Subdomain</label>
                <input
                    :value="company.subdomain"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @input="updateCompanyField('subdomain', $event.target.value)"
                >
                <p v-if="errors['company.subdomain']" class="mt-2 text-sm text-red-400">
                    {{ errors['company.subdomain'] }}
                </p>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Database Name</label>
                <input
                    :value="company.db_name"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    placeholder="Optional, auto-generated if empty"
                    @input="updateCompanyField('db_name', $event.target.value)"
                >
                <p v-if="errors['company.db_name']" class="mt-2 text-sm text-red-400">
                    {{ errors['company.db_name'] }}
                </p>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Industry</label>
                <select
                    :value="company.industry"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @change="updateCompanyField('industry', $event.target.value)"
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
                <p v-if="errors['company.industry']" class="mt-2 text-sm text-red-400">
                    {{ errors['company.industry'] }}
                </p>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Theme Color</label>
                <input
                    :value="company.theme_color"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    placeholder="#0f172a"
                    @input="updateCompanyField('theme_color', $event.target.value)"
                >
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950 px-4 py-4 text-sm text-slate-200 md:col-span-2">
                <input
                    :checked="company.is_active"
                    type="checkbox"
                    class="h-4 w-4 border-white/20 bg-slate-900 text-indigo-500"
                    @change="updateCompanyField('is_active', $event.target.checked)"
                >
                <span>Active company</span>
            </label>
        </div>

        <AddressFields
            title="Company Address"
            :model-value="company.address"
            @update:model-value="updateCompanyField('address', $event)"
        />

        <PhoneListEditor
            title="Company Phones"
            :model-value="company.phones"
            @update:model-value="updateCompanyField('phones', $event)"
        />

        <EnterpriseTypesEditor
            title="Company Enterprise Types"
            :model-value="company.enterprise_types"
            @update:model-value="updateCompanyField('enterprise_types', $event)"
        />

        <LogoUploader
            title="Company Logo"
            :model-value="company.logo"
            @update:model-value="updateCompanyField('logo', $event)"
        />
    </div>
</template>

<script setup>
import AddressFields from './AddressFields.vue';
import PhoneListEditor from './PhoneListEditor.vue';
import EnterpriseTypesEditor from './EnterpriseTypesEditor.vue';
import LogoUploader from './LogoUploader.vue';

const props = defineProps({
    company: {
        type: Object,
        required: true,
    },
    industries: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:company']);

const updateCompanyField = (field, value) => {
    emit('update:company', {
        ...props.company,
        [field]: value,
    });
};
</script>
