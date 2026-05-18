<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/70 px-4 py-6"
    >
        <div class="mx-auto flex min-h-full w-full max-w-4xl items-start justify-center">
            <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl">
                <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-white">Edit Main Group</h2>
                        <p class="mt-1 text-sm text-slate-400">Update umbrella group profile and logo.</p>
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
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Group Name</label>
                                    <input
                                        v-model="form.group.name"
                                        type="text"
                                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    >
                                    <p v-if="form.errors['group.name']" class="mt-2 text-sm text-red-400">{{ form.errors['group.name'] }}</p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Registration Number</label>
                                    <input
                                        v-model="form.group.registration_number"
                                        type="text"
                                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                                    >
                                </div>

                                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950 px-4 py-4 text-sm text-slate-200 md:col-span-2">
                                    <input
                                        v-model="form.group.is_active"
                                        type="checkbox"
                                        class="h-4 w-4 border-white/20 bg-slate-900 text-indigo-500"
                                    >
                                    <span>Active main group</span>
                                </label>
                            </div>

                            <AddressFields
                                title="Group Address"
                                :model-value="form.group.address"
                                @update:model-value="form.group.address = $event"
                            />

                            <PhoneListEditor
                                title="Group Phones"
                                :model-value="form.group.phones"
                                @update:model-value="form.group.phones = $event"
                            />

                            <EnterpriseTypesEditor
                                title="Group Enterprise Types"
                                :model-value="form.group.enterprise_types"
                                @update:model-value="form.group.enterprise_types = $event"
                            />

                            <LogoUploader
                                title="Group Logo"
                                :model-value="form.group.logo"
                                :existing-path="mainGroup?.logo_path || ''"
                                @update:model-value="form.group.logo = $event"
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
                            {{ form.processing ? 'Saving...' : 'Save Group' }}
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
    mainGroup: {
        type: Object,
        default: null,
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
    group: {
        name: '',
        registration_number: '',
        address: blankAddress(),
        phones: [blankPhone()],
        enterprise_types: [],
        logo: null,
        is_active: true,
    },
});

watch(
    () => [props.open, props.mainGroup],
    ([open, mainGroup]) => {
        if (!open || !mainGroup) {
            return;
        }

        form.defaults({
            group: {
                name: mainGroup.name || '',
                registration_number: mainGroup.registration_number || '',
                address: mainGroup.address || blankAddress(),
                phones: normalizePhones(mainGroup.phones),
                enterprise_types: Array.isArray(mainGroup.enterprise_types) ? [...mainGroup.enterprise_types] : [],
                logo: null,
                is_active: Boolean(mainGroup.is_active ?? true),
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
    if (!props.mainGroup?.id) {
        return;
    }

    form.transform((data) => ({
        group: {
            ...data.group,
            phones: sanitizePhones(data.group.phones),
            enterprise_types: sanitizeEnterpriseTypes(data.group.enterprise_types),
        },
    })).put(`/main-group-companies/${props.mainGroup.id}`, {
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
