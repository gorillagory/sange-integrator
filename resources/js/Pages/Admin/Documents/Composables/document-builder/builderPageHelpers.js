// resources/js/Pages/Admin/Documents/Composables/document-builder/builderPageHelpers.js
export function cloneLayout(layout) {
    return JSON.parse(JSON.stringify(layout));
}

export function normalizeCode(value) {
    return String(value || '')
        .toLowerCase()
        .replace(/[^a-z0-9_-]+/g, '_')
        .replace(/_+/g, '_')
        .replace(/^_+|_+$/g, '');
}

export function prettyType(value) {
    return String(value || '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}

export function documentStoreUrl() {
    return '/admin/documents';
}

export function documentUpdateUrl(id) {
    return `/admin/documents/${id}`;
}

export function documentPreviewUrl({ pageProps, templateId }) {
    const subdomain = pageProps?.currentCompany?.subdomain;
    return `/admin/documents/${subdomain}/${templateId}/preview`;
}
