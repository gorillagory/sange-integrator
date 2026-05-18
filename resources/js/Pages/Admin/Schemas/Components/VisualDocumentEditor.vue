<template>
    <div :class="cardClass" class="p-6 rounded-2xl shadow-sm border">
        <h3 :class="headingClass" class="font-bold mb-2 pb-2 border-b" :style="dividerStyle">3. Document Output</h3>
        <p :class="bodyClass" class="text-[10px] mb-4 leading-relaxed">
            Shape how this vector should render in documents. Click a field key to insert it into the layout.
        </p>

        <div v-if="editor" :class="editorShellClass" class="rounded-lg overflow-hidden shadow-sm transition flex flex-col">

            <div :class="toolbarClass" class="p-2 flex gap-1.5 items-center flex-wrap">
                <div :class="toolbarGroupClass" class="flex rounded shadow-sm overflow-hidden">
                    <button @click.prevent="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="toolbarButtonClass(editor.isActive('heading', { level: 1 }), true)" class="px-2 py-1 text-xs font-black transition" title="Large Heading">H1</button>
                    <button @click.prevent="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="toolbarButtonClass(editor.isActive('heading', { level: 3 }), true)" class="px-2 py-1 text-xs font-bold transition" title="Medium Heading">H3</button>
                    <button @click.prevent="editor.chain().focus().setParagraph().run()" :class="toolbarButtonClass(editor.isActive('paragraph'), false)" class="px-2 py-1 text-xs transition" title="Normal Text">P</button>
                </div>

                <div :class="toolbarGroupClass" class="flex rounded shadow-sm overflow-hidden">
                    <button @click.prevent="editor.chain().focus().toggleBold().run()" :class="toolbarButtonClass(editor.isActive('bold'), true)" class="px-2 py-1 font-bold text-xs transition">B</button>
                    <button @click.prevent="editor.chain().focus().toggleItalic().run()" :class="toolbarButtonClass(editor.isActive('italic'), true)" class="px-2 py-1 italic text-xs transition">I</button>
                    <button @click.prevent="editor.chain().focus().toggleUnderline().run()" :class="toolbarButtonClass(editor.isActive('underline'), false)" class="px-2 py-1 underline text-xs transition">U</button>
                </div>

                <div :class="toolbarGroupClass" class="flex rounded shadow-sm overflow-hidden">
                    <button @click.prevent="editor.chain().focus().setTextAlign('left').run()" :class="toolbarButtonClass(editor.isActive({ textAlign: 'left' }), true)" class="px-2 py-1 transition" title="Align Left">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"></path></svg>
                    </button>
                    <button @click.prevent="editor.chain().focus().setTextAlign('center').run()" :class="toolbarButtonClass(editor.isActive({ textAlign: 'center' }), true)" class="px-2 py-1 transition" title="Align Center">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16"></path></svg>
                    </button>
                    <button @click.prevent="editor.chain().focus().setTextAlign('right').run()" :class="toolbarButtonClass(editor.isActive({ textAlign: 'right' }), false)" class="px-2 py-1 transition" title="Align Right">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M4 18h16"></path></svg>
                    </button>
                </div>

                <button @click.prevent="editor.chain().focus().toggleBulletList().run()" :class="soloToolbarButtonClass(editor.isActive('bulletList'))" class="px-2 py-1 rounded text-xs transition" title="Bullet List">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <button @click.prevent="editor.chain().focus().clearNodes().unsetAllMarks().run()" :class="clearButtonClass" class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider transition ml-auto border">Clear</button>
            </div>

            <editor-content :editor="editor" :class="editorContentClass" class="flex-1 p-4 text-sm" />
        </div>

        <div class="mt-4 pt-3" :style="dividerStyle">
            <p :class="insertLabelClass" class="text-[8px] font-bold uppercase tracking-widest mb-2">Insert Field Keys</p>
            <div class="flex flex-wrap gap-1.5">
                <button
                    v-for="field in schemaFields"
                    :key="field.key"
                    @click.prevent="insertVariable(field.key)"
                    class="text-[9px] px-2 py-1 rounded font-mono border transition shadow-sm"
                    :class="[variableButtonClass, !field.key ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']"
                    :disabled="!field.key"
                >
                    + {{ field.key || 'empty' }}
                </button>
                <span v-if="schemaFields.length === 0" :class="emptyVariablesClass" class="text-[9px] italic">Add fields to unlock variable pills</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import { Node, mergeAttributes } from '@tiptap/core';

const props = defineProps({
    modelValue: { type: String, default: '' },
    schemaFields: { type: Array, default: () => [] },
    surface: { type: String, default: 'light' },
});

const emit = defineEmits(['update:modelValue']);

// TIPTAP CUSTOM NODE FOR DATA PILLS
const VariablePill = Node.create({
    name: 'variablePill',
    group: 'inline',
    inline: true,
    atom: true,

    addAttributes() {
        return { id: { default: null } }
    },

    parseHTML() {
        return [{ tag: 'span[data-variable]' }]
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes, {
            'data-variable': HTMLAttributes.id,
            'class': 'variable-pill'
        }), `{{ ${HTMLAttributes.id} }}`]
    },
});

const editor = useEditor({
    extensions: [
        StarterKit,
        VariablePill,
        TextAlign.configure({ types: ['heading', 'paragraph'] })
    ],
    content: props.modelValue,
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    }
});

// Allow external updates (e.g., if the form is reset)
watch(() => props.modelValue, (newVal) => {
    if (editor.value && editor.value.getHTML() !== newVal) {
        editor.value.commands.setContent(newVal, false);
    }
});

const insertVariable = (key) => {
    if (!key || !editor.value) return;
    editor.value.chain().focus().insertContent({ type: 'variablePill', attrs: { id: key } }).insertContent(' ').run();
};

onBeforeUnmount(() => {
    if (editor.value) editor.value.destroy();
});

const isDark = computed(() => props.surface === 'dark');
const dividerStyle = computed(() => ({ borderColor: isDark.value ? 'rgba(255,255,255,0.08)' : '#f3f4f6' }));
const cardClass = computed(() => isDark.value ? 'bg-[#111827] border-white/10' : 'bg-white border-gray-100');
const headingClass = computed(() => isDark.value ? 'text-white' : 'text-gray-900');
const bodyClass = computed(() => isDark.value ? 'text-gray-400' : 'text-gray-500');
const editorShellClass = computed(() => isDark.value ? 'border border-white/10 focus-within:border-indigo-400 focus-within:ring-1 focus-within:ring-indigo-400' : 'border border-gray-200 focus-within:border-[var(--brand-400)] focus-within:ring-1 focus-within:ring-[var(--brand-400)]');
const toolbarClass = computed(() => isDark.value ? 'bg-[#0f172a] border-b border-white/10' : 'bg-gray-50 border-b border-gray-200');
const toolbarGroupClass = computed(() => isDark.value ? 'bg-[#111827] border border-white/10' : 'bg-white border border-gray-200');
const toolbarButtonClass = (isActive, hasRightBorder) => [
    hasRightBorder ? (isDark.value ? 'border-r border-white/10' : 'border-r border-gray-200') : '',
    isActive
        ? (isDark.value ? 'bg-white/10 text-white' : 'bg-gray-200 text-gray-900')
        : (isDark.value ? 'text-gray-400 hover:bg-white/5' : 'text-gray-600 hover:bg-gray-100'),
];
const soloToolbarButtonClass = (isActive) => isActive
    ? (isDark.value ? 'bg-white/10 text-white' : 'bg-gray-200 text-gray-900')
    : (isDark.value ? 'text-gray-400 hover:bg-white/5' : 'text-gray-600 hover:bg-gray-200');
const clearButtonClass = computed(() => isDark.value ? 'text-gray-400 hover:bg-white/5 border-white/10 bg-[#111827]' : 'text-gray-500 hover:bg-gray-200 border-gray-200 bg-white');
const editorContentClass = computed(() => isDark.value ? 'bg-[#111827] text-gray-100' : 'bg-white text-gray-800');
const insertLabelClass = computed(() => isDark.value ? 'text-gray-500' : 'text-gray-400');
const variableButtonClass = computed(() => isDark.value ? 'bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-200 border-indigo-400/20' : 'bg-blue-50 hover:bg-blue-100 text-blue-700 border-blue-100');
const emptyVariablesClass = computed(() => isDark.value ? 'text-gray-500' : 'text-gray-400');
</script>

<style scoped>
/* TipTap Global Scoped Styles */
:deep(.tiptap) {
    min-height: 160px;
    outline: none;
}
:deep(.tiptap p) { margin-bottom: 0.5rem; }
:deep(.tiptap h1) { font-size: 1.5rem; font-weight: 900; margin-bottom: 0.5rem; line-height: 1.2; }
:deep(.tiptap h3) { font-size: 1.125rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.3; }
:deep(.tiptap ul) { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 0.5rem; }
:deep(.tiptap ol) { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 0.5rem; }

/* The Data Pill inside the Editor */
:deep(.variable-pill) {
    background-color: #eff6ff;
    color: #1d4ed8;
    padding: 0.1rem 0.35rem;
    border-radius: 0.25rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.65rem;
    margin: 0 0.15rem;
    border: 1px solid #bfdbfe;
    display: inline-block;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>
