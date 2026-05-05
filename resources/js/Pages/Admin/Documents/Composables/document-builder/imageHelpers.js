// resources/js/Pages/Admin/Documents/Composables/document-builder/imageHelpers.js
export function isDynamicImageNode(node) {
    return node?.type === 'image' && node?.source_mode === 'dynamic';
}

export function isStaticImageNode(node) {
    return node?.type === 'image' && node?.source_mode !== 'dynamic';
}

export function resolveImageSource(node, payload = {}) {
    if (!node || node.type !== 'image') {
        return '';
    }

    if (isDynamicImageNode(node)) {
        return resolvePath(payload, node.data_key) || '';
    }

    return node.asset_path || node.url || '';
}

export function hasImageSource(node, payload = {}) {
    return !!resolveImageSource(node, payload);
}

export function resolvePath(source, path) {
    if (!source || !path) {
        return null;
    }

    return String(path)
        .split('.')
        .filter(Boolean)
        .reduce((carry, segment) => {
            if (carry == null) {
                return null;
            }

            return carry[segment];
        }, source);
}
