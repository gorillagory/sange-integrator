<template>
    <div class="space-y-6 rounded-2xl border border-white/10 bg-slate-900 p-6">
        <div>
            <h2 class="text-xl font-bold text-white">Main Group Company</h2>
            <p class="mt-1 text-sm text-slate-400">
                The umbrella or head group that holds subsidiaries.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950 px-4 py-4 text-sm text-slate-200">
                <input
                    :checked="mode === 'existing'"
                    type="radio"
                    class="h-4 w-4 border-white/20 bg-slate-900 text-indigo-500"
                    @change="$emit('update:mode', 'existing')"
                >
                <span>Use Existing Group</span>
            </label>

            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-slate-950 px-4 py-4 text-sm text-slate-200">
                <input
                    :checked="mode === 'new'"
                    type="radio"
                    class="h-4 w-4 border-white/20 bg-slate-900 text-indigo-500"
                    @change="$emit('update:mode', 'new')"
                >
                <span>Create New Group</span>
            </label>
        </div>

        <div v-if="mode === 'existing'" class="space-y-4">
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Select Group</label>
                <select
                    :value="selectedGroupId"
                    class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                    @change="$emit('update:selectedGroupId', normalizeGroupId($event.target.value))"
                >
                    <option :value="''">Select main group</option>
                    <option
                        v-for="group in mainGroupCompanies"
                        :key="group.id"
                        :value="group.id"
                    >
                        {{ group.name }} ({{ group.companies_count }} companies)
                    </option>
                </select>
            </div>

            <div
                v-if="selectedGroup"
                class="rounded-2xl border border-white/10 bg-slate-950 p-4"
            >
                <div class="font-semibold text-white">{{ selectedGroup.name }}</div>
                <div class="mt-1 text-sm text-slate-400">{{ selectedGroup.registration_number || 'No registration number' }}</div>

                <div v-if="selectedGroup.enterprise_types?.length" class="mt-3 flex flex-wrap gap-2">
                    <span
                        v-for="type in selectedGroup.enterprise_types"
                        :key="`${selectedGroup.id}_${type}`"
                        class="rounded-full border border-white/10 px-3 py-1 text-xs text-slate-300"
                    >
                        {{ type }}
                    </span>
                </div>
            </div>
        </div>

        <div v-else class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Group Name</label>
                    <input
                        :value="group.name"
                        type="text"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                        @input="updateGroupField('name', $event.target.value)"
                    >
                    <p v-if="errors['main_group.name']" class="mt-2 text-sm text-red-400">
                        {{ errors['main_group.name'] }}
                    </p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Registration Number</label>
                    <input
                        :value="group.registration_number"
                        type="text"
                        class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white outline-none transition focus:border-indigo-500"
                        @input="updateGroupField('registration_number', $event.target.value)"
                    >
                </div>
            </div>

            <AddressFields
                title="Group Address"
                :model-value="group.address"
                @update:model-value="updateGroupField('address', $event)"
            />

            <PhoneListEditor
                title="Group Phones"
                :model-value="group.phones"
                @update:model-value="updateGroupField('phones', $event)"
            />

            <EnterpriseTypesEditor
                title="Group Enterprise Types"
                :model-value="group.enterprise_types"
                @update:model-value="updateGroupField('enterprise_types', $event)"
            />

            <LogoUploader
                title="Group Logo"
                :model-value="group.logo"
                @update:model-value="updateGroupField('logo', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import AddressFields from './AddressFields.vue';
import PhoneListEditor from './PhoneListEditor.vue';
import EnterpriseTypesEditor from './EnterpriseTypesEditor.vue';
import LogoUploader from './LogoUploader.vue';

const props = defineProps({
    mode: {
        type: String,
        required: true,
    },
    selectedGroupId: {
        type: [Number, null],
        default: null,
    },
    mainGroupCompanies: {
        type: Array,
        default: () => [],
    },
    group: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits([
    'update:mode',
    'update:selectedGroupId',
    'update:group',
]);

const selectedGroup = computed(() => {
    return props.mainGroupCompanies.find((group) => group.id === props.selectedGroupId) ?? null;
});

const updateGroupField = (field, value) => {
    emit('update:group', {
        ...props.group,
        [field]: value,
    });
};

const normalizeGroupId = (value) => {
    return value ? Number(value) : null;
};
</script>
