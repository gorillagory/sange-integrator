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
        return normalizeImageSource(resolvePath(payload, node.data_key));
    }

    return normalizeImageSource(node.asset_path) || normalizeImageSource(node.url);
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

function normalizeImageSource(value) {
    if (typeof value === 'string') {
        return value;
    }

    if (value && typeof value === 'object') {
        if (typeof value.logo_url === 'string') {
            return value.logo_url;
        }

        if (typeof value.url === 'string') {
            return value.url;
        }

        if (typeof value.asset_path === 'string') {
            return value.asset_path;
        }
    }

    return '';
}
