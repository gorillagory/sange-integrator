<template>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-6 h-6 rounded-full bg-[var(--brand-100)] text-[var(--brand-700)] flex items-center justify-center text-xs font-bold">1</div>
            <h3 class="font-bold text-gray-900">Corporate Routing</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="relative">
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Search Corporate Entity <span class="text-red-500">*</span></label>
                <input
                    v-model="clientSearchQuery"
                    @focus="isDropdownOpen = true"
                    type="text"
                    class="w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]"
                    placeholder="Type to search..."
                    autocomplete="off"
                >

                <div v-if="isDropdownOpen && filteredClients.length > 0" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto">
                    <div v-for="client in filteredClients" :key="client.id" @click="selectClient(client)" class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-50 transition-colors">
                        <div class="font-bold text-gray-900 text-sm">{{ client.name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Reg: {{ client.registration_number || 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <div v-if="clientId" class="animate-fade-in-up">
                <label class="block text-xs font-bold text-[var(--brand-600)] uppercase mb-1">Select Active Contract <span class="text-red-500">*</span></label>
                <select :value="contractNo" @input="$emit('update:contractNo', $event.target.value)" class="w-full bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5 text-sm font-bold text-[var(--brand-900)] focus:border-[var(--brand-500)] focus:ring-[var(--brand-500)]">
                    <option :value="null" disabled>Select contract...</option>
                    <option v-for="contract in availableContracts" :key="contract.contract_no" :value="contract.contract_no">
                        {{ contract.contract_no }} - {{ contract.title }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    clients: Array,
    clientId: [Number, String],
    contractNo: String
});

const emit = defineEmits(['update:clientId', 'update:contractNo']);

const clientSearchQuery = ref('');
const isDropdownOpen = ref(false);

const filteredClients = computed(() => {
    if (!clientSearchQuery.value) return props.clients;
    const query = clientSearchQuery.value.toLowerCase();
    return props.clients.filter(c => c.name.toLowerCase().includes(query) || (c.registration_number && c.registration_number.toLowerCase().includes(query)));
});

const selectClient = (client) => {
    emit('update:clientId', client.id);
    clientSearchQuery.value = client.name;
    isDropdownOpen.value = false;

    // Auto-select if only 1 contract exists
    if (client.contracts && client.contracts.length === 1) {
        emit('update:contractNo', client.contracts[0].contract_no);
    } else {
        emit('update:contractNo', null);
    }
};

const availableContracts = computed(() => {
    const client = props.clients.find(c => c.id === props.clientId);
    return client ? (client.contracts || []) : [];
});

const closeDropdown = () => isDropdownOpen.value = false;
const handleClickOutside = (event) => { if (!event.target.closest('.relative')) closeDropdown(); };
onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>

<style scoped>
.animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
