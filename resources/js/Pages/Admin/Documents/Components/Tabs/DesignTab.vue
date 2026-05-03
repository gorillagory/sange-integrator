<template>
    <div class="p-4 space-y-6">

        <div v-if="activeNode.isPage" class="space-y-4">
            <h4 class="text-[10px] font-bold text-[var(--brand-600)] uppercase tracking-wider border-b border-[var(--brand-100)] pb-1">Document Properties</h4>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[9px] text-gray-500 mb-1">Page Size</label>
                    <select v-model="activeNode.size" @change="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5">
                        <option value="A4">A4</option>
                        <option value="Letter">Letter</option>
                        <option value="Legal">Legal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] text-gray-500 mb-1">Orientation</label>
                    <select v-model="activeNode.orientation" @change="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5">
                        <option value="portrait">Portrait</option>
                        <option value="landscape">Landscape</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[9px] text-gray-500 mb-1">Page Margins (Top Right Bottom Left)</label>
                <input type="text" v-model="activeNode.margins" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="e.g. 20mm 15mm 20mm 15mm">
            </div>

            <div>
                <label class="block text-[9px] text-gray-500 mb-1">Page Background</label>
                <div class="flex items-center gap-2">
                    <input type="color" :value="safeHex(activeNode.backgroundColor, '#ffffff')" @input="activeNode.backgroundColor = $event.target.value; $emit('update')" class="w-6 h-6 p-0 border-0 rounded cursor-pointer">
                    <input type="text" v-model="activeNode.backgroundColor" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1 font-mono uppercase">
                </div>
            </div>
        </div>

        <template v-else>

            <div v-if="activeNode.type === 'text'" class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider">Content</label>
                    <textarea v-model="activeNode.content" @input="$emit('update')" rows="4" class="w-full text-xs border border-gray-200 rounded-lg p-2 focus:ring-[var(--brand-500)] focus:border-[var(--brand-500)]"></textarea>
                </div>

                <div class="space-y-3" v-if="activeNode.styles">
                    <h4 class="text-[10px] font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-1">Typography</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[9px] text-gray-500 mb-1">Font Size</label>
                            <select v-model="activeNode.styles.fontSize" @change="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5">
                                <option value="8px">8px (Micro)</option>
                                <option value="10px">10px (Smallest)</option>
                                <option value="12px">12px (Small)</option>
                                <option value="14px">14px (Normal)</option>
                                <option value="16px">16px (Large)</option>
                                <option value="20px">20px (Heading 3)</option>
                                <option value="24px">24px (Heading 2)</option>
                                <option value="32px">32px (Heading 1)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] text-gray-500 mb-1">Font Weight</label>
                            <select v-model="activeNode.styles.fontWeight" @change="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5">
                                <option value="normal">Normal</option>
                                <option value="500">Medium</option>
                                <option value="bold">Bold</option>
                                <option value="900">Black</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[9px] text-gray-500 mb-1">Text Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" :value="safeHex(activeNode.styles.color, '#000000')" @input="activeNode.styles.color = $event.target.value; $emit('update')" class="w-6 h-6 p-0 border-0 rounded cursor-pointer">
                                <input type="text" v-model="activeNode.styles.color" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1 font-mono uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[9px] text-gray-500 mb-1">Alignment</label>
                            <div class="flex bg-gray-100 rounded border border-gray-200 overflow-hidden">
                                <button @click="activeNode.styles.textAlign = 'left'; $emit('update')" :class="{'bg-white shadow-sm font-bold text-gray-900': activeNode.styles.textAlign === 'left'}" class="flex-1 py-1 text-xs text-gray-500">L</button>
                                <button @click="activeNode.styles.textAlign = 'center'; $emit('update')" :class="{'bg-white shadow-sm font-bold text-gray-900': activeNode.styles.textAlign === 'center'}" class="flex-1 py-1 text-xs text-gray-500 border-x border-gray-200">C</button>
                                <button @click="activeNode.styles.textAlign = 'right'; $emit('update')" :class="{'bg-white shadow-sm font-bold text-gray-900': activeNode.styles.textAlign === 'right'}" class="flex-1 py-1 text-xs text-gray-500">R</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeNode.type === 'image'" class="space-y-4">
                <h4 class="text-[10px] font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-1">Image Source & Layout</h4>

                <div>
                    <label class="block text-[9px] text-gray-500 mb-1">Upload File (Saved to Template)</label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition cursor-pointer flex flex-col items-center justify-center overflow-hidden bg-gray-50/50">
                        <input type="file" accept="image/png, image/jpeg, image/svg+xml" @change="handleImageUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div v-if="activeNode.url && activeNode.url.startsWith('data:image')" class="absolute inset-0 w-full h-full bg-contain bg-center bg-no-repeat opacity-20" :style="{ backgroundImage: `url(${activeNode.url})` }"></div>
                        <svg class="w-6 h-6 text-[var(--brand-500)] mb-2 z-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <span class="text-[10px] font-bold text-gray-700 z-20">Click or Drag Image Here</span>
                    </div>
                </div>

                <div>
                    <label class="block text-[9px] text-gray-500 mb-1">Or Paste External URL</label>
                    <input type="text" v-model="activeNode.url" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="https://...">
                </div>

                <div class="space-y-2">
                    <label class="block text-[9px] text-gray-500 mb-1">Image Alignment</label>
                    <div class="flex bg-gray-100 rounded border border-gray-200 overflow-hidden mb-3">
                        <button @click="setImageAlignment('left')" :class="{'bg-white shadow-sm font-bold text-gray-900': activeNode.styles.marginRight === 'auto' && activeNode.styles.marginLeft === '0px'}" class="flex-1 py-1.5 text-xs text-gray-500 transition">Left</button>
                        <button @click="setImageAlignment('center')" :class="{'bg-white shadow-sm font-bold text-gray-900': activeNode.styles.marginRight === 'auto' && activeNode.styles.marginLeft === 'auto'}" class="flex-1 py-1.5 text-xs text-gray-500 border-x border-gray-200 transition">Center</button>
                        <button @click="setImageAlignment('right')" :class="{'bg-white shadow-sm font-bold text-gray-900': activeNode.styles.marginLeft === 'auto' && activeNode.styles.marginRight === '0px'}" class="flex-1 py-1.5 text-xs text-gray-500 transition">Right</button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[9px] text-gray-500 mb-1">Width</label>
                            <input type="text" v-model="activeNode.styles.width" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="100%">
                        </div>
                        <div>
                            <label class="block text-[9px] text-gray-500 mb-1">Height</label>
                            <input type="text" v-model="activeNode.styles.height" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="auto">
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeNode.type === 'table'" class="space-y-4">
                <h4 class="text-[10px] font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-1">Table Settings</h4>
                <p class="text-[9px] text-gray-500">Double-click the table directly on the canvas to add/remove rows and edit content.</p>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[9px] text-gray-500 mb-1">Table Width</label>
                        <input type="text" v-model="activeNode.styles.width" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="100%">
                    </div>
                </div>
            </div>

            <div v-if="activeNode.type === 'spacer'" class="space-y-4">
                <h4 class="text-[10px] font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-1">Spacer Settings</h4>
                <div>
                    <label class="block text-[9px] text-gray-500 mb-1">Vertical Height</label>
                    <input type="text" v-model="activeNode.styles.height" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="32px">
                </div>
            </div>

            <div v-if="activeNode.type === 'divider'" class="space-y-4">
                <h4 class="text-[10px] font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-1">Divider Settings</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[9px] text-gray-500 mb-1">Thickness</label>
                        <input type="text" v-model="activeNode.styles.height" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="1px">
                    </div>
                    <div>
                        <label class="block text-[9px] text-gray-500 mb-1">Line Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" :value="safeHex(activeNode.styles.backgroundColor, '#e5e7eb')" @input="activeNode.styles.backgroundColor = $event.target.value; $emit('update')" class="w-6 h-6 p-0 border-0 rounded cursor-pointer">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[9px] text-gray-500 mb-1">Vertical Margin</label>
                    <input type="text" v-model="activeNode.styles.margin" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="16px 0px">
                </div>
            </div>

            <div v-if="['text', 'image', 'table', 'row', 'column'].includes(activeNode.type) && activeNode.styles" class="mt-6 space-y-6 border-t border-gray-100 pt-4">

                <div class="space-y-3">
                    <h4 class="text-[10px] font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-1">Layout & Spacing</h4>
                    <div>
                        <label class="block text-[9px] text-gray-500 mb-1">Background Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" :value="safeHex(activeNode.styles.backgroundColor, '#ffffff')" @input="activeNode.styles.backgroundColor = $event.target.value; $emit('update')" class="w-6 h-6 p-0 border-0 rounded cursor-pointer">
                            <input type="text" v-model="activeNode.styles.backgroundColor" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1 font-mono uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[9px] text-gray-500 mb-1">Padding (Internal)</label>
                            <input type="text" v-model="activeNode.styles.padding" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="e.g. 10px 15px">
                        </div>
                        <div>
                            <label class="block text-[9px] text-gray-500 mb-1">Margin (External)</label>
                            <input type="text" v-model="activeNode.styles.margin" @input="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5" placeholder="e.g. 10px 0px">
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="text-[10px] font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-1">Borders</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[9px] text-gray-500 mb-1">Border Width</label>
                            <select v-model="activeNode.styles.borderWidth" @change="$emit('update')" class="w-full text-xs border border-gray-200 rounded p-1.5">
                                <option value="0px">None (0px)</option>
                                <option value="1px">Thin (1px)</option>
                                <option value="2px">Medium (2px)</option>
                                <option value="4px">Thick (4px)</option>
                            </select>
                        </div>
                        <div v-if="activeNode.styles.borderWidth !== '0px'">
                            <label class="block text-[9px] text-gray-500 mb-1">Border Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" :value="safeHex(activeNode.styles.borderColor, '#e5e7eb')" @input="activeNode.styles.borderColor = $event.target.value; $emit('update')" class="w-6 h-6 p-0 border-0 rounded cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </template>
    </div>
</template>

<script setup>
const props = defineProps({ activeNode: { type: Object, required: true } });
const emit = defineEmits(['update']);

// Base64 Uploader Handler
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 1.5 * 1024 * 1024) {
        alert('File is too large. Please keep images under 1MB for optimal template saving speed.');
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        props.activeNode.url = e.target.result;
        emit('update');
    };
    reader.readAsDataURL(file);
};

// Safe CSS Injector for Image Alignment
const setImageAlignment = (align) => {
    props.activeNode.styles.display = 'block'; // Forces the image to behave like a block so margins work

    if (align === 'center') {
        props.activeNode.styles.marginLeft = 'auto';
        props.activeNode.styles.marginRight = 'auto';
    } else if (align === 'right') {
        props.activeNode.styles.marginLeft = 'auto';
        props.activeNode.styles.marginRight = '0px';
    } else {
        props.activeNode.styles.marginLeft = '0px';
        props.activeNode.styles.marginRight = 'auto';
    }
    emit('update');
};

const safeHex = (val, fallback = '#000000') => {
    if (!val) return fallback;
    const hexRegex = /^#([0-9A-F]{3}){1,2}$/i;
    return hexRegex.test(val) ? val : fallback;
};
</script>
