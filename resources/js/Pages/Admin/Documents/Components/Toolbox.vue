<template>
    <div class="bg-white border-r border-gray-200 w-72 flex flex-col h-[800px] rounded-l-3xl shadow-sm z-20">
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 rounded-tl-3xl">
            <h3 class="font-black text-gray-900 text-sm uppercase tracking-wider">Forge Toolbox</h3>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-8">
            <div>
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Layout Rows</h4>
                <VueDraggableNext :list="layoutLibrary" :group="{ name: 'blocks', pull: 'clone', put: false }" :sort="false" :clone="cloneRow" class="grid grid-cols-2 gap-2">
                    <div v-for="layout in layoutLibrary" :key="layout.type" class="p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-move hover:border-[var(--brand-400)] hover:bg-[var(--brand-50)] transition flex flex-col items-center justify-center gap-2">
                        <div class="w-full flex gap-1 h-6">
                            <div v-for="(col, i) in layout.cols" :key="i" class="bg-gray-300 rounded-sm h-full" :style="{ flex: col }"></div>
                        </div>
                        <span class="text-[9px] font-bold text-gray-500">{{ layout.label }}</span>
                    </div>
                </VueDraggableNext>
            </div>

            <div>
                <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Content Elements</h4>
                <VueDraggableNext :list="elementLibrary" :group="{ name: 'blocks', pull: 'clone', put: false }" :sort="false" :clone="cloneElement" class="space-y-2">
                    <div v-for="element in elementLibrary" :key="element.type" class="p-3 bg-gray-50 border border-gray-200 rounded-xl cursor-move hover:border-[var(--brand-400)] hover:bg-[var(--brand-50)] transition flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-[var(--brand-500)] shrink-0" v-html="element.icon"></div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">{{ element.label }}</p>
                            <p class="text-[9px] text-gray-400 leading-tight">{{ element.desc }}</p>
                        </div>
                    </div>
                </VueDraggableNext>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { VueDraggableNext } from 'vue-draggable-next';

const layoutLibrary = ref([
    { type: 'row_12', label: 'Full Width', cols: [12] },
    { type: 'row_6_6', label: 'Half / Half', cols: [6, 6] },
    { type: 'row_4_8', label: '1/3 + 2/3', cols: [4, 8] },
    { type: 'row_8_4', label: '2/3 + 1/3', cols: [8, 4] },
    { type: 'row_4_4_4', label: '3 Columns', cols: [4, 4, 4] },
    { type: 'row_3_3_3_3', label: '4 Columns', cols: [3, 3, 3, 3] },
]);

const elementLibrary = ref([
    { type: 'text', label: 'Text Node', desc: 'Dynamic data or static text', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"></path></svg>', content: 'Sample Text', data_key: null, styles: { fontSize: '14px', fontWeight: 'normal', color: '#1f2937', textAlign: 'left', margin: '0px', backgroundColor: 'transparent', borderRadius: '0px', padding: '0px' } },
    { type: 'image', label: 'Image / Logo', desc: 'Visual assets', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>', url: '', styles: { width: '150px', objectFit: 'contain' } },
    { type: 'table', label: 'Data Table', desc: 'Loopable item arrays', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z\"></path></svg>', data_key: 'items', columns: [{ label: 'Item', key: 'name' }, { label: 'Total', key: 'total' }], styles: { marginTop: '10px' } },
    { type: 'list', label: 'Data List', desc: 'Bulleted loops', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>', data_key: 'list_items', content: 'List Item', styles: { listStyleType: 'disc', paddingLeft: '20px', fontSize: '14px', color: '#374151', margin: '10px 0px' } },
    { type: 'divider', label: 'Divider Line', desc: 'Horizontal separation', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>', styles: { height: '1px', backgroundColor: '#e5e7eb', margin: '16px 0px' } },
    { type: 'spacer', label: 'Spacer', desc: 'Empty vertical space', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path></svg>', styles: { height: '32px' } },
    { type: 'page_break', label: 'Page Break', desc: 'Forces PDF next page', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>', styles: { margin: '20px 0px' } },
]);

const cloneRow = (row) => ({
    id: 'row_' + Date.now(),
    type: 'row',
    layout: row.type,
    styles: { padding: '0px', margin: '0px 0px 16px 0px', gap: '16px', alignItems: 'start', justifyContent: 'space-between', backgroundColor: 'transparent', borderRadius: '0px', border: '0px solid #e5e7eb' },
    columns: row.cols.map((span, i) => ({ id: `col_${Date.now()}_${i}`, span: span, blocks: [] }))
});

const cloneElement = (element) => ({
    ...JSON.parse(JSON.stringify(element)),
    id: 'el_' + Date.now() + Math.floor(Math.random() * 100)
});
</script>
