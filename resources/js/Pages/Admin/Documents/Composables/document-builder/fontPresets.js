export const DOCUMENT_FONT_PRESETS = [
    {
        value: 'sans',
        label: 'Sans Modern',
        cssFamily: 'Helvetica, Arial, sans-serif',
    },
    {
        value: 'serif',
        label: 'Serif Editorial',
        cssFamily: "'Times New Roman', Times, serif",
    },
    {
        value: 'mono',
        label: 'Mono Clean',
        cssFamily: "'Courier New', Courier, monospace",
    },
];

export function resolveDocumentFontCss(value, presets = DOCUMENT_FONT_PRESETS) {
    const source = Array.isArray(presets) && presets.length ? presets : DOCUMENT_FONT_PRESETS;
    const preset = source.find((item) => item.value === value);

    if (preset) {
        return preset.cssFamily;
    }

    if (typeof value === 'string' && value.trim() !== '') {
        return value;
    }

    return DOCUMENT_FONT_PRESETS[0].cssFamily;
}
