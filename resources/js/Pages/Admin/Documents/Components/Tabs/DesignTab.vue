<template>
    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                Quick Guidance
            </div>
            <div class="mt-2 text-xs leading-5 text-slate-600">
                Use the main inspector for editing properties. Keep this tab light and focused on block hints.
            </div>
        </div>

        <div v-if="activeNode?.type === 'table'" class="rounded-2xl border border-slate-200 p-3">
            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                Table Rules
            </div>
            <ul class="mt-2 space-y-1 text-xs text-slate-600">
                <li>Choose an array data key.</li>
                <li>Each column key should match a child field inside the row item.</li>
                <li>Example: `invoice.line_items` with column keys like `description`, `quantity`, `total`.</li>
            </ul>
        </div>

        <div v-if="activeNode?.type === 'list'" class="rounded-2xl border border-slate-200 p-3">
            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                List Rules
            </div>
            <ul class="mt-2 space-y-1 text-xs text-slate-600">
                <li>List blocks should use an array data key.</li>
                <li>Each array item will render as one bullet line.</li>
            </ul>
        </div>

        <div v-if="activeNode?.type === 'image'" class="rounded-2xl border border-slate-200 p-3">
            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                Image Rules
            </div>
            <ul class="mt-2 space-y-1 text-xs text-slate-600">
                <li>Prefer `asset_path` over large base64 blobs.</li>
                <li>Use `/storage/...` or a relative storage path.</li>
            </ul>
        </div>

        <div v-if="activeNode?.isPage" class="rounded-2xl border border-slate-200 p-3">
            <div class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                Page Rules
            </div>
            <ul class="mt-2 space-y-1 text-xs text-slate-600">
                <li>Supported sizes: A4, Letter, Legal.</li>
                <li>Orientation must be portrait or landscape.</li>
                <li>Margins should use printable CSS values like `10mm`.</li>
            </ul>
        </div>
    </div>
</template>

<script setup>
defineProps({
    activeNode: {
        type: Object,
        default: null,
    },
});
</script>
