<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/70 px-4 py-6"
    >
        <div class="mx-auto flex min-h-full w-full max-w-7xl items-start justify-center">
            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl">
                <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-white">Provision Company</h2>
                        <p class="mt-1 text-sm text-slate-400">
                            Create or attach a main group and provision a new subsidiary company.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                        @click="attemptClose"
                    >
                        Close
                    </button>
                </div>

                <form @submit.prevent="submit" class="flex flex-col">
                    <div class="max-h-[calc(100vh-180px)] overflow-y-auto px-6 py-6">
                        <div class="grid gap-6 xl:grid-cols-2">
                            <MainGroupSection
                                :mode="form.main_group_mode"
                                :selected-group-id="form.main_group_company_id"
                                :main-group-companies="mainGroupCompanies"
                                :group="form.main_group"
                                :errors="form.errors"
                                @update:mode="form.main_group_mode = $event"
                                @update:selectedGroupId="form.main_group_company_id = $event"
                                @update:group="form.main_group = $event"
                            />

                            <CompanySection
                                :company="form.company"
                                :industries="industries"
                                :errors="form.errors"
                                @update:company="form.company = $event"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-white/10 bg-slate-950 px-6 py-4">
                        <button
                            type="button"
                            class="rounded-xl border border-white/10 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5"
                            @click="attemptClose"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ form.processing ? 'Provisioning...' : 'Provision Company' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div
        v-if="showDiscardConfirm"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 px-4"
    >
        <div class="w-full max-w-md rounded-2xl border border-white/10 bg-slate-950 p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-white">Discard changes?</h3>
            <p class="mt-2 text-sm text-slate-400">
                You have unsaved company provisioning data. Closing now will lose everything entered in this form.
            </p>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="rounded-xl border border-white/10 px-4 py-3 text-sm font-semibold text-slate-300 transition hover:bg-white/5"
                    @click="showDiscardConfirm = false"
                >
                    Keep Editing
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-rose-500"
                    @click="confirmClose"
                >
                    Discard
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, watch, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import MainGroupSection from './MainGroupSection.vue';
import CompanySection from './CompanySection.vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    mainGroupCompanies: {
        type: Array,
        default: () => [],
    },
    industries: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const showDiscardConfirm = ref(false);

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

const defaults = () => ({
    main_group_mode: 'existing',
    main_group_company_id: null,
    main_group: {
        name: '',
        registration_number: '',
        address: blankAddress(),
        phones: [blankPhone()],
        enterprise_types: [],
        logo: null,
    },
    company: {
        name: '',
        registration_number: '',
        subdomain: '',
        db_name: '',
        industry: '',
        address: blankAddress(),
        phones: [blankPhone()],
        enterprise_types: [],
        logo: null,
        theme_color: '',
        is_active: true,
    },
});

const form = useForm(defaults());

watch(
    () => props.open,
    (value) => {
        if (!value) {
            showDiscardConfirm.value = false;
            return;
        }

        form.defaults(defaults());
        form.reset();
        form.clearErrors();
        showDiscardConfirm.value = false;
    },
    { immediate: true }
);

const hasMeaningfulValue = (value) => {
    if (value === null || value === undefined) {
        return false;
    }

    if (value instanceof File) {
        return true;
    }

    if (typeof value === 'boolean') {
        return value === true;
    }

    if (typeof value === 'string') {
        return value.trim() !== '';
    }

    if (typeof value === 'number') {
        return true;
    }

    if (Array.isArray(value)) {
        return value.some((item) => hasMeaningfulValue(item));
    }

    if (typeof value === 'object') {
        return Object.values(value).some((item) => hasMeaningfulValue(item));
    }

    return false;
};

const isDirty = computed(() => {
    return hasMeaningfulValue({
        main_group_mode: form.main_group_mode === 'new' ? form.main_group_mode : '',
        main_group_company_id: form.main_group_company_id,
        main_group: form.main_group_mode === 'new' ? form.main_group : {},
        company: form.company,
    });
});

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

const attemptClose = () => {
    if (form.processing) {
        return;
    }

    if (!isDirty.value) {
        emit('close');
        return;
    }

    showDiscardConfirm.value = true;
};

const confirmClose = () => {
    showDiscardConfirm.value = false;
    emit('close');
};

const submit = () => {
    form.transform((data) => ({
        main_group_mode: data.main_group_mode,
        main_group_company_id: data.main_group_company_id,
        main_group: {
            name: data.main_group.name,
            registration_number: data.main_group.registration_number,
            address: data.main_group.address,
            phones: sanitizePhones(data.main_group.phones),
            enterprise_types: sanitizeEnterpriseTypes(data.main_group.enterprise_types),
            logo: data.main_group.logo,
        },
        company: {
            name: data.company.name,
            registration_number: data.company.registration_number,
            subdomain: data.company.subdomain,
            db_name: data.company.db_name,
            industry: data.company.industry,
            address: data.company.address,
            phones: sanitizePhones(data.company.phones),
            enterprise_types: sanitizeEnterpriseTypes(data.company.enterprise_types),
            logo: data.company.logo,
            theme_color: data.company.theme_color,
            is_active: data.company.is_active,
        },
    })).post('/companies', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showDiscardConfirm.value = false;
            emit('close');
        },
    });
};
</script>
