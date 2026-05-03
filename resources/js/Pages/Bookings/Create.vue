<template>
    <TenantLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="[
                { label: 'Operations', url: null },
                { label: 'Master Bookings', url: '/bookings' },
                { label: 'Initiate Booking', url: null }
            ]" />
        </template>

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Initiate Master Booking</h1>
            <p class="text-sm text-gray-500 mt-1">Define corporate routing, compile manifests, and construct financial margins.</p>
        </div>

        <form @submit.prevent="submitMasterBooking" class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start pb-20">
            <div class="xl:col-span-8 space-y-6">

                <CorporateRouting
                    :clients="clients"
                    v-model:clientId="form.client_id"
                    v-model:contractNo="form.contract_no"
                />

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100" :class="{'opacity-50 pointer-events-none': !form.contract_no}">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-6 h-6 rounded-full bg-[var(--brand-100)] text-[var(--brand-700)] flex items-center justify-center text-xs font-bold">2</div>
                        <h3 class="font-bold text-gray-900">Operational Payload & Margins</h3>
                    </div>

                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Select Service Module</label>
                        <div class="flex gap-2">
                            <select v-model="selectedSchemaId" class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:border-[var(--brand-500)] transition-colors">
                                <option :value="null" disabled>Choose a service to add to cart...</option>
                                <option v-for="schema in schemas" :key="schema.id" :value="schema.id">{{ schema.display_name }}</option>
                            </select>
                            <button @click.prevent="addService" :disabled="!selectedSchemaId" class="px-6 py-3 bg-[var(--brand-600)] hover:bg-[var(--brand-500)] text-white text-sm font-bold rounded-xl transition shadow-lg shadow-brand-500/20 disabled:opacity-50 disabled:shadow-none flex items-center gap-2 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add Service
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <ServiceRow
                            v-for="(item, index) in form.services"
                            :key="index"
                            :item="item"
                            :schemas="schemas"
                            @remove="removeService(index)"
                        />
                    </div>
                </div>
            </div>

            <div class="xl:col-span-4 sticky top-8 z-10 drop-shadow-2xl">
                <CartSummary
                    :totals="totals"
                    :itemCount="form.services.length"
                    :isReady="form.services.length > 0 && !!form.contract_no && !form.processing"
                    @submit="submitMasterBooking"
                />
            </div>
        </form>
    </TenantLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';
import Breadcrumbs from '../../Components/UI/Breadcrumbs.vue';
import CorporateRouting from './Components/CorporateRouting.vue';
import ServiceRow from './Components/ServiceRow.vue';
import CartSummary from './Components/CartSummary.vue';

const props = defineProps({ schemas: Array, clients: Array });

const form = useForm({ client_id: null, contract_no: null, services: [] });
const selectedSchemaId = ref(null);

const normalizeFields = (payload) => {
    let fieldsArray = Array.isArray(payload) ? payload : (payload && Array.isArray(payload.fields) ? payload.fields : []);
    return fieldsArray.map(f => {
        if (typeof f === 'string') return { key: f, type: 'string', is_array: false };
        return {
            key: f.key || 'unknown_field',
            type: f.type || 'string',
            is_array: f.is_array || false
        };
    });
};

const addService = () => {
    const schema = props.schemas.find(s => s.id === selectedSchemaId.value);
    if (schema) {
        const detailsObj = {};
        let payload = schema.schema_payload;
        if (typeof payload === 'string') { try { payload = JSON.parse(payload); } catch (e) { payload = []; } }

        normalizeFields(payload).forEach(field => {
            detailsObj[field.key] = field.is_array ? [''] : '';
        });

        // 🟢 THE FIX: Now using `client_price` instead of `markup_value`
        form.services.push({
            service_type: schema.service_type,
            service_details: detailsObj,
            qty: 1,
            unit_fare: 0,
            tax_type: 'RM',
            tax_value: 0,
            client_price: 0
        });

        selectedSchemaId.value = null;
    }
};

const removeService = (index) => form.services.splice(index, 1);

const totals = computed(() => {
    let base = 0, tax = 0, markup = 0, grand = 0;

    form.services.forEach(item => {
        const q = parseInt(item.qty) || 1;
        const b = parseFloat(item.unit_fare) || 0;

        const itemTax = item.tax_type === '%'
            ? b * ((parseFloat(item.tax_value) || 0) / 100)
            : (parseFloat(item.tax_value) || 0);

        const cp = parseFloat(item.client_price) || 0;

        // Profit margin is auto-calculated based on what you charge the client
        const itemMarkup = cp - b - itemTax;

        base += (b * q);
        tax += (itemTax * q);
        markup += (itemMarkup * q);
        grand += (cp * q);
    });

    return { base, tax, markup, grand };
});

const submitMasterBooking = () => form.post('/bookings');
</script>
