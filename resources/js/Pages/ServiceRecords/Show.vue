<template>
    <TenantLayout>
        <div class="mb-8 flex items-end justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-900">{{ record.reference_no }}</h1>
                    <span
                        class="rounded-md border px-2.5 py-1 text-xs font-bold"
                        :class="serviceStatusBadgeClass"
                    >
                        Service: {{ record.service_status || 'Pending' }}
                    </span>
                    <span
                        class="rounded-md border px-2.5 py-1 text-xs font-bold"
                        :class="documentStatusBadgeClass"
                    >
                        Document: {{ documentStatusLabel }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Track execution status, route billing cleanly, then generate the right document outputs from saved templates.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <Link
                    v-if="canEditDraft && !isLocked"
                    :href="`/service-records/${record.id}/edit`"
                    class="flex items-center gap-2 rounded-xl border border-[var(--brand-200)] bg-[var(--brand-50)] px-5 py-2.5 text-sm font-bold text-[var(--brand-700)] shadow-sm transition hover:bg-[var(--brand-100)]"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Draft
                </Link>

                <button
                    v-if="canGenerateDocuments && canGenerateFromStatus && isLocked && documentTemplates.length"
                    type="button"
                    class="flex items-center gap-2 rounded-xl bg-[var(--brand-600)] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)]"
                    @click="documentModalOpen = true"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Generate Documents
                </button>
            </div>
        </div>

        <div class="mb-8 grid grid-cols-1 gap-6 xl:grid-cols-[1.3fr_minmax(320px,0.7fr)]">
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                    <div class="font-bold text-gray-800">Service Status Timeline</div>
                    <div class="mt-1 text-sm text-gray-500">Execution status changes are highlighted here separately from the document activity trail.</div>
                </div>

                <div v-if="serviceStatusTimelineItems.length" class="px-6 py-5">
                    <div class="space-y-4">
                        <div
                            v-for="(item, index) in serviceStatusTimelineItems"
                            :key="`service-status-${item.id}`"
                            class="relative pl-10"
                        >
                            <div class="absolute left-[15px] top-0 h-full w-px" :class="index === serviceStatusTimelineItems.length - 1 ? 'bg-transparent' : 'bg-emerald-200'"></div>

                            <button
                                type="button"
                                class="group w-full rounded-2xl border text-left transition"
                                :class="isServiceStatusExpanded(item.id)
                                    ? 'border-emerald-200 bg-gradient-to-r from-emerald-50 via-white to-white shadow-sm shadow-emerald-100/70'
                                    : 'border-gray-200 bg-white hover:border-emerald-200 hover:bg-emerald-50/30'"
                                @click="toggleServiceStatusItem(item.id)"
                            >
                                <div class="absolute left-0 top-5 flex w-8 justify-center">
                                    <span
                                        class="flex h-8 w-8 items-center justify-center rounded-full border-4 border-white text-[10px] font-bold shadow-sm"
                                        :class="index === 0
                                            ? (isServiceStatusExpanded(item.id) ? 'border-emerald-100 bg-emerald-500 text-white' : 'border-emerald-100 bg-emerald-50 text-emerald-700')
                                            : (isServiceStatusExpanded(item.id) ? 'border-emerald-100 bg-[var(--brand-600)] text-white' : 'border-gray-200 bg-white text-gray-500')"
                                    >
                                        {{ index === 0 ? 'Now' : index + 1 }}
                                    </span>
                                </div>

                                <div class="flex items-start justify-between gap-4 px-5 py-4">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="text-sm font-bold text-gray-900">{{ item.label }}</div>
                                            <span
                                                v-if="statusTransitionSummary(item)"
                                                class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700"
                                            >
                                                {{ statusTransitionSummary(item) }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ item.actor.name }}
                                            <span v-if="item.actor.email">· {{ item.actor.email }}</span>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">{{ formatDateTime(item.created_at) }}</div>
                                        <div class="mt-3 text-[11px] font-semibold" :class="isServiceStatusExpanded(item.id) ? 'text-emerald-700' : 'text-gray-500 group-hover:text-gray-700'">
                                            {{ isServiceStatusExpanded(item.id) ? 'Hide' : 'Reveal' }}
                                        </div>
                                    </div>
                                </div>

                                <div v-if="isServiceStatusExpanded(item.id)" class="border-t border-gray-100 px-5 py-4">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div v-if="payloadPreview(item.old_values).length" class="rounded-xl border border-amber-100 bg-amber-50/60 p-4">
                                            <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-700">Before</div>
                                            <div class="space-y-1 text-sm text-amber-900">
                                                <div v-for="entry in payloadPreview(item.old_values)" :key="entry.label">
                                                    <span class="font-semibold">{{ entry.label }}:</span> {{ entry.value }}
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="payloadPreview(item.new_values).length" class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                                            <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700">After</div>
                                            <div class="space-y-1 text-sm text-emerald-900">
                                                <div v-for="entry in payloadPreview(item.new_values)" :key="entry.label">
                                                    <span class="font-semibold">{{ entry.label }}:</span> {{ entry.value }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="px-6 py-10 text-sm text-gray-500">
                    The first execution update will create a visible service-status timeline here.
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                    <div class="font-bold text-gray-800">Execution Control</div>
                    <div class="mt-1 text-sm text-gray-500">Move the service through its operational lifecycle before generating documents.</div>
                </div>

                <div class="space-y-4 p-6">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500">User Identity</div>
                        <div class="mt-3 space-y-3 text-sm text-gray-700">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500">Author</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ record.author?.name || 'Legacy / System' }}</div>
                                <div v-if="record.author?.email" class="text-xs text-gray-500">{{ record.author.email }}</div>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500">Assigned To</div>
                                <div class="mt-1 font-semibold text-gray-900">{{ record.assigned_user?.name || 'Unassigned' }}</div>
                                <div v-if="record.assigned_user?.email" class="text-xs text-gray-500">{{ record.assigned_user.email }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500">Current Service Status</div>
                        <div class="mt-2 text-lg font-bold text-gray-900">{{ record.service_status || 'Pending' }}</div>
                        <div class="mt-1 text-xs text-gray-500">Generation unlocks when the service reaches Confirmed or Delivered.</div>
                    </div>

                    <template v-if="canManageServiceStatus">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">Update Service Status</label>
                            <select v-model="serviceStatusForm.service_status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)]">
                                <option v-for="status in serviceStatusOptions" :key="status" :value="status">{{ status }}</option>
                            </select>
                        </div>

                        <button
                            type="button"
                            :disabled="serviceStatusForm.processing || serviceStatusForm.service_status === (record.service_status || 'Pending')"
                            class="w-full rounded-xl bg-[var(--brand-600)] py-3 text-sm font-bold text-white transition hover:bg-[var(--brand-500)] disabled:opacity-50"
                            @click="updateServiceStatus"
                        >
                            Update Execution Status
                        </button>
                    </template>

                    <div v-else class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-500">
                        Only operational roles can move the service execution status.
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                <div v-if="record.remarks" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="font-bold text-gray-800">Client Remarks Snapshot</div>
                        <div class="mt-1 text-sm text-gray-500">These notes were stored on the service record at capture time.</div>
                    </div>

                    <div class="px-6 py-5">
                        <div
                            v-if="record.client_remark_preset?.title"
                            class="mb-3 inline-flex rounded-full border border-[var(--brand-200)] bg-[var(--brand-50)] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--brand-700)]"
                        >
                            {{ record.client_remark_preset.title }}
                        </div>

                        <div class="whitespace-pre-line rounded-xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-700">
                            {{ record.remarks }}
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="font-bold text-gray-800">Captured Service Rows</div>
                        <div class="mt-1 text-sm text-gray-500">Each row preserves its own schema vector, structured payload, and finance state.</div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        <div v-for="(item, index) in serviceRows" :key="item.id || index" class="p-6">
                            <div class="mb-4 flex items-start justify-between">
                                <div>
                                    <h4 class="text-sm font-bold uppercase text-gray-900">{{ item.service_name || 'Service Row' }}</h4>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ item.service_code || 'service' }} · vector v{{ item.schema_version || 1 }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900">RM {{ Number(item.line_total || 0).toFixed(2) }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ item.qty || 1 }} {{ item.unit_name || '' }}</div>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                    <div class="mb-2 text-[10px] font-bold uppercase text-gray-500">Primary Payload</div>
                                    <div v-if="hasEntries(item.service_details)" class="space-y-2 text-sm text-gray-700">
                                        <div v-for="([key, value], detailIndex) in entriesOf(item.service_details)" :key="`${key}-${detailIndex}`">
                                            <span class="font-semibold capitalize">{{ humanize(key) }}:</span>
                                            {{ renderValue(value) }}
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-gray-500">No structured values were stored in the primary payload.</div>
                                </div>

                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                    <div class="mb-2 text-[10px] font-bold uppercase text-gray-500">Extra Payload & Finance</div>
                                    <div class="space-y-2 text-sm text-gray-700">
                                        <div v-if="hasEntries(item.service_details_extra)">
                                            <div v-for="([key, value], extraIndex) in entriesOf(item.service_details_extra)" :key="`${key}-${extraIndex}`">
                                                <span class="font-semibold capitalize">{{ humanize(key) }}:</span>
                                                {{ renderValue(value) }}
                                            </div>
                                        </div>
                                        <div><span class="font-semibold">Unit:</span> {{ item.unit_name || 'unit' }}</div>
                                        <div><span class="font-semibold">Base Cost:</span> RM {{ Number((item.base_cost ?? item.unit_fare) || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Supplier Cost:</span> RM {{ Number((item.supplier_cost ?? item.base_cost ?? item.unit_fare) || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Discount:</span> {{ item.discount_type || 'RM' }} {{ Number(item.discount_value || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Tax:</span> {{ item.tax_type || 'RM' }} {{ Number(item.tax_value || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Sell Price:</span> RM {{ Number((item.sell_price ?? item.client_price) || 0).toFixed(2) }}</div>
                                        <div><span class="font-semibold">Line Total:</span> RM {{ Number(item.line_total || 0).toFixed(2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="font-bold text-gray-800">Document Timeline</div>
                        <div class="mt-1 text-sm text-gray-500">Creation, edits, and status transitions are captured with actor and data context.</div>
                    </div>

                    <div v-if="timelineItems.length" class="px-6 py-6">
                        <div class="space-y-4">
                            <div
                                v-for="(item, index) in timelineItems"
                                :key="item.id"
                                class="relative pl-10"
                            >
                                <div
                                    class="absolute left-[15px] top-0 h-full w-px"
                                    :class="index === timelineItems.length - 1 ? 'bg-transparent' : 'bg-gray-200'"
                                ></div>

                                <button
                                    type="button"
                                    class="group w-full rounded-2xl border text-left transition"
                                    :class="isTimelineExpanded(item.id) ? latestTimelineCardClass(index) : collapsedTimelineCardClass(index)"
                                    @click="toggleTimelineItem(item.id)"
                                >
                                    <div class="absolute left-0 top-5 flex w-8 justify-center">
                                        <span
                                            class="flex h-8 w-8 items-center justify-center rounded-full border-4 border-white text-[10px] font-bold shadow-sm"
                                            :class="timelineNodeClass(index, isTimelineExpanded(item.id))"
                                        >
                                            {{ index === 0 ? 'Now' : index + 1 }}
                                        </span>
                                    </div>

                                    <div class="flex items-start justify-between gap-4 px-5 py-4">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <div class="text-sm font-bold text-gray-900">{{ item.label }}</div>
                                                <span
                                                    v-if="index === 0"
                                                    class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700"
                                                >
                                                    Latest Activity
                                                </span>
                                            </div>

                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ item.actor.name }}
                                                <span v-if="item.actor.email">· {{ item.actor.email }}</span>
                                            </div>

                                            <div v-if="timelineSummary(item).length" class="mt-3 flex flex-wrap gap-2">
                                                <div
                                                    v-for="entry in timelineSummary(item)"
                                                    :key="entry.label"
                                                    class="rounded-full border border-gray-200 bg-white/80 px-3 py-1 text-[11px] text-gray-700"
                                                >
                                                    <span class="font-bold">{{ entry.label }}:</span> {{ entry.value }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="shrink-0 text-right">
                                            <div class="text-xs text-gray-500">{{ formatDateTime(item.created_at) }}</div>
                                            <div class="mt-1 font-mono text-[10px] uppercase text-gray-400">{{ humanizeAction(item.action) }}</div>
                                            <div class="mt-3 text-[11px] font-semibold" :class="isTimelineExpanded(item.id) ? 'text-[var(--brand-700)]' : 'text-gray-500 group-hover:text-gray-700'">
                                                {{ isTimelineExpanded(item.id) ? 'Hide Details' : 'Reveal Details' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="isTimelineExpanded(item.id)" class="border-t border-gray-100 px-5 py-4">
                                        <div class="grid gap-4 xl:grid-cols-2">
                                            <div v-if="payloadPreview(item.old_values).length" class="rounded-xl border border-amber-100 bg-amber-50/60 p-4">
                                                <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-700">Before</div>
                                                <div class="space-y-1 text-sm text-amber-900">
                                                    <div v-for="entry in payloadPreview(item.old_values)" :key="entry.label">
                                                        <span class="font-semibold">{{ entry.label }}:</span> {{ entry.value }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="payloadPreview(item.new_values).length" class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                                                <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700">After</div>
                                                <div class="space-y-1 text-sm text-emerald-900">
                                                    <div v-for="entry in payloadPreview(item.new_values)" :key="entry.label">
                                                        <span class="font-semibold">{{ entry.label }}:</span> {{ entry.value }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="rowSummary(item).length" class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                                            <div class="mb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-gray-500">Service Row Snapshot</div>
                                            <div class="space-y-2 text-sm text-gray-700">
                                                <div v-for="row in rowSummary(item)" :key="row.key" class="rounded-lg border border-white bg-white px-3 py-2">
                                                    <span class="font-semibold text-gray-900">{{ row.name }}</span>
                                                    <span class="mx-1 text-gray-400">·</span>
                                                    <span>{{ row.qty }} {{ row.unit }}</span>
                                                    <span class="mx-1 text-gray-400">·</span>
                                                    <span>{{ row.total }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-6 py-12 text-center text-sm text-gray-500">
                        Timeline data will appear here once the record starts moving through its lifecycle.
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-4">
                <div class="sticky top-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4"><div class="font-bold text-gray-800">Document Routing</div></div>

                    <form class="space-y-5 p-6" @submit.prevent="lockDocument">
                        <div v-if="record.document_no" class="mb-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-blue-500">Generated Document</div>
                            <div class="font-mono text-lg font-bold text-blue-900">{{ record.document_no }}</div>
                        </div>

                        <template v-if="isLocked">
                            <div class="space-y-4">
                                <div>
                                    <div class="mb-1 text-xs font-semibold uppercase text-gray-500">Billed To</div>
                                    <div class="font-bold text-gray-900">{{ selectedClient?.name }}</div>
                                    <div class="text-xs text-gray-500">Reg: {{ selectedClient?.registration_number }}</div>
                                </div>
                                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                    <div class="mb-1 text-xs font-semibold uppercase text-[var(--brand-600)]">Active Contract</div>
                                    <div class="font-mono font-bold text-gray-900">{{ selectedContract?.contract_no }}</div>
                                    <div class="mt-1 text-xs text-gray-700">{{ selectedContract?.title }}</div>
                                    <div class="mt-2 border-t border-gray-200 pt-2 text-[10px] text-gray-500">{{ selectedContract?.billing_address }}</div>
                                </div>
                                <div class="flex gap-2 rounded-lg border border-amber-100 bg-amber-50 p-3 text-xs text-amber-600">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    This document is locked. Update routing through an administrator if it needs to change.
                                </div>

                                <button
                                    v-if="canManageDocumentStatus"
                                    type="button"
                                    :disabled="unlockForm.processing"
                                    class="w-full rounded-xl border border-amber-200 bg-white py-3 text-sm font-bold text-amber-700 transition hover:bg-amber-50 disabled:opacity-50"
                                    @click="unlockDocument"
                                >
                                    Return To Draft For Editing
                                </button>
                            </div>
                        </template>

                        <template v-else>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">1. Select Corporate Client</label>
                                <select v-model="form.client_id" :disabled="!canManageDocumentStatus" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)] disabled:bg-gray-100 disabled:text-gray-400">
                                    <option :value="null" disabled>Select a client...</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                                </select>

                                <div v-if="selectedClient" class="mt-2 flex justify-between px-1 text-xs text-gray-500">
                                    <span>Reg: {{ selectedClient.registration_number || 'N/A' }}</span>
                                    <span>HQ: {{ selectedClient.hq_contact_person || 'N/A' }}</span>
                                </div>
                            </div>

                            <div v-if="form.client_id">
                                <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">2. Assign Contract</label>
                                <select v-model="form.contract_no" :disabled="!canManageDocumentStatus" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-[var(--brand-500)] disabled:bg-gray-100 disabled:text-gray-400">
                                    <option :value="null" disabled>Select active contract...</option>
                                    <option v-for="contract in availableContracts" :key="contract.contract_no" :value="contract.contract_no">
                                        {{ contract.contract_no }} - {{ contract.title }}
                                    </option>
                                </select>

                                <div v-if="selectedContract" class="mt-3 space-y-1 rounded-lg border border-gray-100 bg-gray-50 p-3 text-xs">
                                    <div class="flex items-start justify-between">
                                        <span class="font-bold text-gray-700">Payment Terms:</span>
                                        <span class="font-bold text-[var(--brand-600)]">{{ selectedContract.payment_terms }}</span>
                                    </div>
                                    <div class="mt-2 border-t border-gray-200 pt-1 text-gray-500">{{ selectedContract.billing_address }}</div>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-gray-100 pt-4">
                                <button v-if="canManageDocumentStatus" type="submit" :disabled="form.processing || !form.client_id || !form.contract_no" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--brand-600)] py-3 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-[var(--brand-500)] disabled:opacity-50">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Lock & Generate Document
                                </button>
                                <div v-else class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-500">
                                    Only Agency Admin, Document Manager, or Super Admin can lock document routing.
                                </div>
                            </div>
                        </template>
                    </form>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="font-bold text-gray-800">Generated Documents</div>
                                <div class="mt-1 text-sm text-gray-500">Every generated output keeps its own number, type, and audit trail.</div>
                            </div>

                            <button
                                v-if="canGenerateDocuments && canGenerateFromStatus && isLocked && documentTemplates.length"
                                type="button"
                                class="rounded-xl border border-[var(--brand-200)] bg-[var(--brand-50)] px-4 py-2 text-xs font-bold text-[var(--brand-700)] transition hover:bg-[var(--brand-100)]"
                                @click="documentModalOpen = true"
                            >
                                Generate
                            </button>
                        </div>
                    </div>

                    <div v-if="generatedDocuments.length" class="divide-y divide-gray-100">
                        <div v-for="document in generatedDocuments" :key="document.id" class="px-6 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="text-sm font-bold text-gray-900">{{ prettifyDocumentType(document.document_type) }}</div>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-emerald-700">
                                            {{ document.status }}
                                        </span>
                                    </div>
                                    <div class="mt-2 font-mono text-sm text-[var(--brand-700)]">{{ document.document_number }}</div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ document.template_name || 'Saved template' }}
                                        <span v-if="document.template_code">· {{ document.template_code }}</span>
                                    </div>
                                    <div class="mt-2 text-[11px] text-gray-400">
                                        Generated {{ formatDateTime(document.generated_at) }}
                                        <span v-if="document.last_downloaded_at">· Downloaded {{ formatDateTime(document.last_downloaded_at) }}</span>
                                    </div>
                                </div>

                                <a
                                    :href="`/service-records/${record.id}/documents/${document.id}/download`"
                                    class="shrink-0 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-500"
                                >
                                    Open PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-6 py-8 text-sm text-gray-500">
                        Generate an output once the service is confirmed and document routing is locked.
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                        <div class="font-bold text-gray-800">Status Authority</div>
                        <div class="mt-1 text-sm text-gray-500">Who can move the record between working and final document states.</div>
                    </div>

                    <div class="space-y-4 p-6">
                        <div
                            v-for="entry in statusAuthority"
                            :key="entry.status"
                            class="rounded-xl border border-gray-100 bg-gray-50 p-4"
                        >
                            <div class="text-sm font-bold text-gray-900">{{ entry.label }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ entry.description }}</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    v-for="role in entry.roles"
                                    :key="role"
                                    class="rounded-full border border-gray-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-700"
                                >
                                    {{ role }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <teleport to="body">
            <div
                v-if="documentModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
                @click.self="documentModalOpen = false"
            >
                <div class="max-h-[90vh] w-full max-w-4xl overflow-hidden rounded-3xl border border-white/10 bg-slate-900 shadow-2xl shadow-black/40">
                    <div class="flex items-start justify-between gap-4 border-b border-white/10 px-6 py-5">
                        <div>
                            <h2 class="text-2xl font-black tracking-tight text-white">Generate Document Output</h2>
                            <p class="mt-2 text-sm text-slate-400">Choose any saved template. The generated document number will follow the selected document type plus this service number.</p>
                        </div>

                        <button
                            type="button"
                            class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                            @click="documentModalOpen = false"
                        >
                            Close
                        </button>
                    </div>

                    <div class="max-h-[calc(90vh-92px)] overflow-y-auto px-6 py-6">
                        <div v-if="!canGenerateFromStatus" class="mb-5 rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                            Move the service to <span class="font-bold">Confirmed</span> or <span class="font-bold">Delivered</span> before generating a document.
                        </div>

                        <div v-if="!isLocked" class="mb-5 rounded-2xl border border-sky-400/20 bg-sky-500/10 px-4 py-3 text-sm text-sky-100">
                            Lock document routing first so the billing client and contract are frozen for output.
                        </div>

                        <div class="space-y-6">
                            <div v-for="(templates, type) in documentTemplatesByType" :key="type">
                                <div class="mb-3 text-xs font-bold uppercase tracking-[0.24em] text-slate-400">{{ prettifyDocumentType(type) }}</div>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <div
                                        v-for="template in templates"
                                        :key="template.id"
                                        class="rounded-2xl border border-white/10 bg-slate-950/80 p-4"
                                    >
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <div class="text-sm font-bold text-white">{{ template.name }}</div>
                                                <div class="mt-1 font-mono text-[11px] uppercase tracking-[0.18em] text-slate-400">{{ template.code }}</div>
                                                <div class="mt-3 text-xs text-slate-500">Saved {{ formatDateTime(template.updated_at) }}</div>
                                            </div>

                                            <button
                                                type="button"
                                                :disabled="generateDocumentForm.processing || !canGenerateFromStatus || !isLocked"
                                                class="shrink-0 rounded-xl bg-[var(--brand-600)] px-4 py-2 text-xs font-bold text-white transition hover:bg-[var(--brand-500)] disabled:cursor-not-allowed disabled:opacity-50"
                                                @click="generateDocument(template.id)"
                                            >
                                                {{ generateDocumentForm.processing && generateDocumentForm.template_id === template.id ? 'Generating...' : 'Generate' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>
    </TenantLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import TenantLayout from '../../Layouts/TenantLayout.vue';

const props = defineProps({
    serviceRecord: {
        type: Object,
        default: null,
    },
    operation: {
        type: Object,
        default: null,
    },
    clients: Array,
    timeline: {
        type: Array,
        default: () => [],
    },
    capabilities: {
        type: Object,
        default: () => ({}),
    },
    statusAuthority: {
        type: Array,
        default: () => [],
    },
    serviceStatusOptions: {
        type: Array,
        default: () => [],
    },
    documentTemplates: {
        type: Array,
        default: () => [],
    },
    generatedDocuments: {
        type: Array,
        default: () => [],
    },
});

const record = computed(() => props.serviceRecord || props.operation || {});
const isLocked = computed(() => record.value.status === 'DocumentLocked');
const documentStatusLabel = computed(() => record.value.status === 'DocumentLocked' ? 'Locked' : record.value.status);
const timelineItems = computed(() => props.timeline || []);
const canEditDraft = computed(() => Boolean(props.capabilities?.can_edit_draft));
const canManageServiceStatus = computed(() => Boolean(props.capabilities?.can_manage_service_status));
const canManageDocumentStatus = computed(() => Boolean(props.capabilities?.can_manage_document_status));
const canGenerateDocuments = computed(() => Boolean(props.capabilities?.can_generate_documents));
const expandedTimelineIds = ref([]);
const expandedServiceStatusIds = ref([]);
const documentModalOpen = ref(false);

const serviceRows = computed(() => {
    return record.value.service_rows || record.value.rows || record.value.service_instances || record.value.services || [];
});
const serviceStatusTimelineItems = computed(() => {
    return timelineItems.value.filter((item) => {
        return item.action === 'SERVICE_RECORD.CREATED' || item.action === 'SERVICE_RECORD.SERVICE_STATUS_CHANGED';
    });
});
const canGenerateFromStatus = computed(() => ['Confirmed', 'Delivered'].includes(record.value.service_status || 'Pending'));
const generatedDocuments = computed(() => props.generatedDocuments || []);
const documentTemplates = computed(() => props.documentTemplates || []);
const documentTemplatesByType = computed(() => {
    return documentTemplates.value.reduce((groups, template) => {
        const key = template.document_type || 'document';
        if (!groups[key]) {
            groups[key] = [];
        }

        groups[key].push(template);

        return groups;
    }, {});
});
const serviceStatusBadgeClass = computed(() => {
    return {
        Pending: 'border-amber-200 bg-amber-50 text-amber-700',
        Confirmed: 'border-sky-200 bg-sky-50 text-sky-700',
        Delivered: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        KIV: 'border-purple-200 bg-purple-50 text-purple-700',
        Archived: 'border-slate-200 bg-slate-100 text-slate-600',
    }[record.value.service_status || 'Pending'] || 'border-gray-200 bg-gray-100 text-gray-600';
});
const documentStatusBadgeClass = computed(() => {
    return isLocked.value
        ? 'border-blue-200 bg-blue-50 text-blue-600'
        : 'border-gray-200 bg-gray-100 text-gray-600';
});

const form = useForm({
    client_id: record.value.client_id || null,
    contract_no: record.value.contract_no || null,
});
const unlockForm = useForm({
    action: 'unlock',
});
const serviceStatusForm = useForm({
    service_status: record.value.service_status || 'Pending',
});
const generateDocumentForm = useForm({
    template_id: null,
});

const selectedClient = computed(() => {
    return props.clients.find((client) => client.id === form.client_id) || null;
});

const availableContracts = computed(() => {
    return selectedClient.value ? (selectedClient.value.contracts || []) : [];
});

const selectedContract = computed(() => {
    return availableContracts.value.find((contract) => contract.contract_no === form.contract_no) || null;
});

watch(() => form.client_id, (newClientId) => {
    if (!newClientId || isLocked.value) {
        return;
    }

    form.contract_no = null;

    const client = props.clients.find((currentClient) => currentClient.id === newClientId);
    if (client && client.contracts && client.contracts.length === 1) {
        form.contract_no = client.contracts[0].contract_no;
    }
});

watch(() => record.value.service_status, (status) => {
    serviceStatusForm.service_status = status || 'Pending';
}, { immediate: true });

watch(
    timelineItems,
    (items) => {
        const latestId = items[0]?.id;
        const validIds = new Set(items.map((item) => item.id));

        expandedTimelineIds.value = expandedTimelineIds.value.filter((id) => validIds.has(id));

        if (latestId && !expandedTimelineIds.value.includes(latestId)) {
            expandedTimelineIds.value = [latestId, ...expandedTimelineIds.value];
        }
    },
    { immediate: true }
);

watch(
    serviceStatusTimelineItems,
    (items) => {
        const latestId = items[0]?.id;
        const validIds = new Set(items.map((item) => item.id));

        expandedServiceStatusIds.value = expandedServiceStatusIds.value.filter((id) => validIds.has(id));

        if (latestId && !expandedServiceStatusIds.value.includes(latestId)) {
            expandedServiceStatusIds.value = [latestId, ...expandedServiceStatusIds.value];
        }
    },
    { immediate: true }
);

function lockDocument() {
    form.put(`/service-records/${record.value.id}/document`);
}

function unlockDocument() {
    unlockForm.put(`/service-records/${record.value.id}/document`, {
        preserveScroll: true,
    });
}

function updateServiceStatus() {
    serviceStatusForm.put(`/service-records/${record.value.id}/service-status`, {
        preserveScroll: true,
    });
}

function generateDocument(templateId) {
    generateDocumentForm.template_id = templateId;
    generateDocumentForm.post(`/service-records/${record.value.id}/documents`, {
        preserveScroll: true,
        onSuccess: () => {
            documentModalOpen.value = false;
        },
    });
}

function isTimelineExpanded(id) {
    return expandedTimelineIds.value.includes(id);
}

function toggleTimelineItem(id) {
    if (isTimelineExpanded(id)) {
        expandedTimelineIds.value = expandedTimelineIds.value.filter((currentId) => currentId !== id);
        return;
    }

    expandedTimelineIds.value = [...expandedTimelineIds.value, id];
}

function isServiceStatusExpanded(id) {
    return expandedServiceStatusIds.value.includes(id);
}

function toggleServiceStatusItem(id) {
    if (isServiceStatusExpanded(id)) {
        expandedServiceStatusIds.value = expandedServiceStatusIds.value.filter((currentId) => currentId !== id);
        return;
    }

    expandedServiceStatusIds.value = [...expandedServiceStatusIds.value, id];
}

function entriesOf(value) {
    return Object.entries(value || {});
}

function hasEntries(value) {
    return entriesOf(value).length > 0;
}

function renderValue(value) {
    if (Array.isArray(value)) {
        return value.join(', ');
    }

    if (value && typeof value === 'object') {
        return Object.entries(value).map(([key, nestedValue]) => `${humanize(key)}: ${nestedValue}`).join(' | ');
    }

    return value || 'Not set';
}

function humanize(value) {
    return String(value || '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

function humanizeAction(value) {
    return String(value || '').replace(/[._]/g, ' ');
}

function formatDateTime(value) {
    if (!value) {
        return 'Unknown time';
    }

    return new Date(value).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function timelineSummary(item) {
    const payload = item.new_values || {};
    const summary = [];

    if (payload.service_status) {
        summary.push({ label: 'Service Status', value: payload.service_status });
    }

    if (payload.status) {
        summary.push({ label: 'Status', value: payload.status });
    }

    if (payload.client?.name) {
        summary.push({ label: 'Client', value: payload.client.name });
    }

    if (payload.contract_no) {
        summary.push({ label: 'Contract', value: payload.contract_no });
    }

    if (payload.total_amount !== undefined) {
        summary.push({ label: 'Total', value: `RM ${Number(payload.total_amount || 0).toFixed(2)}` });
    }

    if (payload.rows_count !== undefined) {
        summary.push({ label: 'Rows', value: String(payload.rows_count) });
    }

    if (payload.generated_document?.document_number) {
        summary.push({ label: 'Document', value: payload.generated_document.document_number });
    }

    return summary;
}

function payloadPreview(payload) {
    if (!payload || typeof payload !== 'object') {
        return [];
    }

    const preview = [];

    if (payload.reference_no) {
        preview.push({ label: 'Reference', value: payload.reference_no });
    }

    if (payload.document_no) {
        preview.push({ label: 'Document No', value: payload.document_no });
    }

    if (payload.client?.name) {
        preview.push({ label: 'Client', value: payload.client.name });
    }

    if (payload.contract_no) {
        preview.push({ label: 'Contract', value: payload.contract_no });
    }

    if (payload.remarks) {
        preview.push({ label: 'Remarks', value: payload.remarks });
    }

    if (payload.service_status) {
        preview.push({ label: 'Service Status', value: payload.service_status });
    }

    if (payload.total_amount !== undefined) {
        preview.push({ label: 'Total', value: `RM ${Number(payload.total_amount || 0).toFixed(2)}` });
    }

    if (payload.generated_document?.document_number) {
        preview.push({ label: 'Generated Document', value: payload.generated_document.document_number });
    }

    return preview;
}

function rowSummary(item) {
    const rows = item.new_values?.rows || [];

    return rows.slice(0, 6).map((row, index) => ({
        key: `${item.id}-${index}`,
        name: row.service_name || row.service_code || `Row ${index + 1}`,
        qty: row.qty || 0,
        unit: row.unit_name || 'unit',
        total: `RM ${Number(row.line_total || 0).toFixed(2)}`,
    }));
}

function latestTimelineCardClass(index) {
    if (index === 0) {
        return 'border-emerald-200 bg-gradient-to-r from-emerald-50 via-white to-white shadow-sm shadow-emerald-100/70';
    }

    return 'border-[var(--brand-200)] bg-[var(--brand-50)]/40 shadow-sm';
}

function collapsedTimelineCardClass(index) {
    if (index === 0) {
        return 'border-emerald-100 bg-white hover:border-emerald-200 hover:bg-emerald-50/40';
    }

    return 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50/80';
}

function timelineNodeClass(index, expanded) {
    if (index === 0) {
        return expanded
            ? 'border-emerald-100 bg-emerald-500 text-white'
            : 'border-emerald-100 bg-emerald-50 text-emerald-700';
    }

    return expanded
        ? 'border-[var(--brand-100)] bg-[var(--brand-600)] text-white'
        : 'border-gray-200 bg-white text-gray-500';
}

function statusTransitionSummary(item) {
    const from = item.old_values?.service_status;
    const to = item.new_values?.service_status;

    if (from && to && from !== to) {
        return `${from} -> ${to}`;
    }

    return to || from || '';
}

function prettifyDocumentType(value) {
    return String(value || '')
        .replace(/[_-]/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}
</script>
