export const DOCUMENT_FONT_PRESETS = [
    {
        value: 'sans',
        label: 'Sans Modern',
        cssFamily: 'Helvetica, Arial, sans-serif',
    },
    {
        value: 'humanist',
        label: 'Humanist Sans',
        cssFamily: "'DejaVu Sans', 'Trebuchet MS', Arial, sans-serif",
    },
    {
        value: 'grotesk',
        label: 'Grotesk Clean',
        cssFamily: "'Arial Narrow', Arial, Helvetica, sans-serif",
    },
    {
        value: 'serif',
        label: 'Serif Editorial',
        cssFamily: "'Times New Roman', Times, serif",
    },
    {
        value: 'book',
        label: 'Book Serif',
        cssFamily: "'DejaVu Serif', Georgia, serif",
    },
    {
        value: 'mono',
        label: 'Mono Clean',
        cssFamily: "'Courier New', Courier, monospace",
    },
    {
        value: 'technical',
        label: 'Technical Mono',
        cssFamily: "'DejaVu Sans Mono', 'Courier New', monospace",
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
