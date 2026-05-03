<template>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-900 mb-2 pb-2 border-b border-gray-100">3. Document Output Design</h3>
        <p class="text-[10px] text-gray-500 mb-4 leading-relaxed">
            Design how this service appears inside a generated PDF. Click a variable below to insert a dynamic data pill.
        </p>

        <div v-if="editor" class="border border-gray-200 rounded-lg overflow-hidden shadow-sm focus-within:border-[var(--brand-400)] focus-within:ring-1 focus-within:ring-[var(--brand-400)] transition flex flex-col">

            <div class="bg-gray-50 border-b border-gray-200 p-2 flex gap-1.5 items-center flex-wrap">
                <div class="flex bg-white border border-gray-200 rounded shadow-sm overflow-hidden">
                    <button @click.prevent="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive('heading', { level: 1 }), 'text-gray-600 hover:bg-gray-100': !editor.isActive('heading', { level: 1 }) }" class="px-2 py-1 text-xs font-black transition border-r border-gray-200" title="Large Heading">H1</button>
                    <button @click.prevent="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive('heading', { level: 3 }), 'text-gray-600 hover:bg-gray-100': !editor.isActive('heading', { level: 3 }) }" class="px-2 py-1 text-xs font-bold transition border-r border-gray-200" title="Medium Heading">H3</button>
                    <button @click.prevent="editor.chain().focus().setParagraph().run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive('paragraph'), 'text-gray-600 hover:bg-gray-100': !editor.isActive('paragraph') }" class="px-2 py-1 text-xs transition" title="Normal Text">P</button>
                </div>

                <div class="flex bg-white border border-gray-200 rounded shadow-sm overflow-hidden">
                    <button @click.prevent="editor.chain().focus().toggleBold().run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive('bold'), 'text-gray-600 hover:bg-gray-100': !editor.isActive('bold') }" class="px-2 py-1 font-bold text-xs transition border-r border-gray-200">B</button>
                    <button @click.prevent="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive('italic'), 'text-gray-600 hover:bg-gray-100': !editor.isActive('italic') }" class="px-2 py-1 italic text-xs transition border-r border-gray-200">I</button>
                    <button @click.prevent="editor.chain().focus().toggleUnderline().run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive('underline'), 'text-gray-600 hover:bg-gray-100': !editor.isActive('underline') }" class="px-2 py-1 underline text-xs transition">U</button>
                </div>

                <div class="flex bg-white border border-gray-200 rounded shadow-sm overflow-hidden">
                    <button @click.prevent="editor.chain().focus().setTextAlign('left').run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive({ textAlign: 'left' }), 'text-gray-600 hover:bg-gray-100': !editor.isActive({ textAlign: 'left' }) }" class="px-2 py-1 transition border-r border-gray-200" title="Align Left">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"></path></svg>
                    </button>
                    <button @click.prevent="editor.chain().focus().setTextAlign('center').run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive({ textAlign: 'center' }), 'text-gray-600 hover:bg-gray-100': !editor.isActive({ textAlign: 'center' }) }" class="px-2 py-1 transition border-r border-gray-200" title="Align Center">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16"></path></svg>
                    </button>
                    <button @click.prevent="editor.chain().focus().setTextAlign('right').run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive({ textAlign: 'right' }), 'text-gray-600 hover:bg-gray-100': !editor.isActive({ textAlign: 'right' }) }" class="px-2 py-1 transition" title="Align Right">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M4 18h16"></path></svg>
                    </button>
                </div>

                <button @click.prevent="editor.chain().focus().toggleBulletList().run()" :class="{ 'bg-gray-200 text-gray-900': editor.isActive('bulletList'), 'text-gray-600 hover:bg-gray-200': !editor.isActive('bulletList') }" class="px-2 py-1 rounded text-xs transition" title="Bullet List">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <button @click.prevent="editor.chain().focus().clearNodes().unsetAllMarks().run()" class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider text-gray-500 hover:bg-gray-200 transition ml-auto border border-gray-200 bg-white">Clear</button>
            </div>

            <editor-content :editor="editor" class="bg-white flex-1 p-4 text-sm text-gray-800" />
        </div>

        <div class="mt-4 pt-3 border-t border-gray-100">
            <p class="text-[8px] font-bold text-gray-400 uppercase tracking-widest mb-2">Insert Variables</p>
            <div class="flex flex-wrap gap-1.5">
                <button
                    v-for="field in schemaFields"
                    :key="field.key"
                    @click.prevent="insertVariable(field.key)"
                    class="text-[9px] bg-blue-50 hover:bg-blue-100 text-blue-700 px-2 py-1 rounded font-mono border border-blue-100 transition shadow-sm"
                    :disabled="!field.key"
                    :class="!field.key ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                >
                    + {{ field.key || 'empty' }}
                </button>
                <span v-if="schemaFields.length === 0" class="text-[9px] text-gray-400 italic">Add attributes to unlock variables</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import TextAlign from '@tiptap/extension-text-align';
import Underline from '@tiptap/extension-underline';
import { Node, mergeAttributes } from '@tiptap/core';

const props = defineProps({
    modelValue: { type: String, default: '' },
    schemaFields: { type: Array, default: () => [] }
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
        Underline,
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
