<!-- resources/js/Pages/Bookings/Components/CorporateRouting.vue -->
<template>
    <div class="relative rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-6 flex items-center gap-2">
            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-[var(--brand-100)] text-xs font-bold text-[var(--brand-700)]">
                1
            </div>
            <h3 class="font-bold text-gray-900">Corporate Routing</h3>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="relative routing-search-root">
                <label class="mb-1 block text-xs font-bold uppercase text-gray-600">
                    Search Corporate Entity <span class="text-red-500">*</span>
                </label>

                <input
                    v-model="clientSearchQuery"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]"
                    placeholder="Type to search..."
                    autocomplete="off"
                    @focus="isDropdownOpen = true"
                >

                <div
                    v-if="isDropdownOpen && filteredClients.length > 0"
                    class="absolute z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"
                >
                    <button
                        v-for="client in filteredClients"
                        :key="client.id"
                        type="button"
                        class="block w-full cursor-pointer border-b border-gray-50 px-4 py-3 text-left transition-colors hover:bg-blue-50"
                        @click="selectClient(client)"
                    >
                        <div class="text-sm font-bold text-gray-900">
                            {{ client.name }}
                        </div>
                        <div class="mt-0.5 text-xs text-gray-500">
                            Reg: {{ client.registration_number || 'N/A' }}
                        </div>
                        <div class="mt-0.5 text-xs text-gray-400">
                            {{ (client.contracts || []).length }} contract<span v-if="(client.contracts || []).length !== 1">s</span>
                        </div>
                    </button>
                </div>

                <div
                    v-if="isDropdownOpen && !filteredClients.length"
                    class="absolute z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-500 shadow-xl"
                >
                    No matching corporate entity found.
                </div>
            </div>

            <div
                v-if="clientId"
                class="animate-fade-in-up"
            >
                <label class="mb-1 block text-xs font-bold uppercase text-[var(--brand-600)]">
                    Select Active Contract <span class="text-red-500">*</span>
                </label>

                <select
                    :value="normalizedContractNo"
                    class="w-full rounded-lg border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-bold text-[var(--brand-900)] focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]"
                    @change="$emit('update:contractNo', normalizeNullable($event.target.value))"
                >
                    <option :value="''" disabled>
                        Select contract...
                    </option>
                    <option
                        v-for="contract in availableContracts"
                        :key="contract.id || contract.contract_no"
                        :value="contract.contract_no"
                    >
                        {{ contract.contract_no }} - {{ contract.title }}
                    </option>
                </select>

                <div
                    v-if="selectedClient"
                    class="mt-3 rounded-xl border border-blue-100 bg-blue-50/60 p-3 text-xs text-blue-900"
                >
                    <div class="font-bold">
                        {{ selectedClient.name }}
                    </div>
                    <div class="mt-1 text-blue-700">
                        Reg: {{ selectedClient.registration_number || 'N/A' }}
                    </div>

                    <div
                        v-if="selectedContract"
                        class="mt-3 border-t border-blue-100 pt-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <span class="font-semibold text-blue-700">Terms</span>
                            <span class="font-bold">{{ selectedContract.payment_terms }}</span>
                        </div>
                        <div class="mt-2 text-blue-700">
                            {{ selectedContract.billing_address }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
    clientId: {
        type: [Number, String, null],
        default: null,
    },
    contractNo: {
        type: [String, null],
        default: null,
    },
});

const emit = defineEmits(['update:clientId', 'update:contractNo']);

const clientSearchQuery = ref('');
const isDropdownOpen = ref(false);

const normalizedContractNo = computed(() => props.contractNo ?? '');

const filteredClients = computed(() => {
    const query = clientSearchQuery.value.trim().toLowerCase();

    if (!query) {
        return props.clients;
    }

    return props.clients.filter((client) => {
        const name = String(client.name || '').toLowerCase();
        const registration = String(client.registration_number || '').toLowerCase();

        return name.includes(query) || registration.includes(query);
    });
});

const selectedClient = computed(() => {
    return props.clients.find((client) => String(client.id) === String(props.clientId)) || null;
});

const availableContracts = computed(() => {
    return selectedClient.value?.contracts || [];
});

const selectedContract = computed(() => {
    return availableContracts.value.find((contract) => contract.contract_no === props.contractNo) || null;
});

watch(
    () => selectedClient.value,
    (client) => {
        clientSearchQuery.value = client?.name || '';
    },
    { immediate: true },
);

watch(
    () => props.clientId,
    () => {
        if (!selectedContract.value && props.contractNo) {
            emit('update:contractNo', null);
        }
    },
);

function selectClient(client) {
    emit('update:clientId', client.id);

    if ((client.contracts || []).length === 1) {
        emit('update:contractNo', client.contracts[0].contract_no);
    } else {
        emit('update:contractNo', null);
    }

    clientSearchQuery.value = client.name;
    isDropdownOpen.value = false;
}

function normalizeNullable(value) {
    return value === '' ? null : value;
}

function handleClickOutside(event) {
    if (!event.target.closest('.routing-search-root')) {
        isDropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.25s ease-out forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
