<template>
    <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
                <thead class="bg-slate-950/80">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Company</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Group</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Industry</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Tenant DB</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Contact</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Status</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-white/5">
                <tr v-if="!companies.data.length">
                    <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                        No companies found.
                    </td>
                </tr>

                <CompanyTableRow
                    v-for="company in companies.data"
                    :key="company.id"
                    :company="company"
                />
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-4 border-t border-white/10 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="text-sm text-slate-400">
                Showing {{ companies.from ?? 0 }} to {{ companies.to ?? 0 }} of {{ companies.total }} companies
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="link in companies.links"
                    :key="`${link.label}_${link.url}`"
                    type="button"
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                    :class="link.active
                        ? 'bg-indigo-600 text-white'
                        : 'border border-white/10 text-slate-300 hover:bg-white/5'"
                    :disabled="!link.url"
                    @click="$emit('visit-link', link.url)"
                    v-html="link.label"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import CompanyTableRow from './CompanyTableRow.vue';

defineProps({
    companies: {
        type: Object,
        required: true,
    },
});

defineEmits(['visit-link']);
</script>
