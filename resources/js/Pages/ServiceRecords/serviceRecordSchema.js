export function parseSchemaPayload(schema) {
    const payload = schema?.schema_payload;

    if (!payload) {
        return {};
    }

    if (typeof payload === 'string') {
        try {
            return JSON.parse(payload);
        } catch {
            return {};
        }
    }

    return typeof payload === 'object' ? payload : {};
}

export function normalizeSchemaFields(schema) {
    const payload = parseSchemaPayload(schema);
    const source = Array.isArray(payload)
        ? payload
        : Array.isArray(payload?.fields)
            ? payload.fields
            : [];

    return source.map((field, index) => {
        if (typeof field === 'string') {
            return {
                key: field,
                label: humanize(field),
                type: 'string',
                ui_component: 'input',
                is_array: false,
                rules: [],
                grid_span: 1,
                order: index,
                placeholder: '',
                text_transform: 'none',
                options: [],
            };
        }

        return {
            key: field?.key || `field_${index + 1}`,
            label: field?.label || humanize(field?.key || `Field ${index + 1}`),
            type: field?.type || 'string',
            ui_component: normalizeUiComponent(field?.ui_component || field?.component || defaultUiComponentForType(field?.type)),
            is_array: Boolean(field?.is_array),
            rules: Array.isArray(field?.rules) ? field.rules : [],
            grid_span: Number(field?.grid_span || 1),
            order: Number(field?.order || index),
            placeholder: field?.placeholder || '',
            text_transform: field?.text_transform || 'none',
            options: Array.isArray(field?.options)
                ? field.options
                : (Array.isArray(field?.allowed_values) ? field.allowed_values : []),
        };
    }).sort((a, b) => a.order - b.order);
}

export function createServiceDetails(schema) {
    const details = {};

    normalizeSchemaFields(schema).forEach((field) => {
        details[field.key] = field.is_array ? [''] : defaultValueForField(field);
    });

    return details;
}

export function getPricingUnits(schema) {
    const payload = parseSchemaPayload(schema);
    const pricingUnits = Array.isArray(payload?.pricing_units) ? payload.pricing_units : [];

    return pricingUnits
        .map((unit) => String(unit || '').trim())
        .filter(Boolean);
}

export function resolveDefaultUnitName(schema) {
    const payload = parseSchemaPayload(schema);
    const directUnit = payload?.commercial?.unit || payload?.unit || null;

    if (typeof directUnit === 'string' && directUnit.trim() !== '') {
        return directUnit.trim();
    }

    return getPricingUnits(schema)[0] || '';
}

export function fieldIsRequired(field) {
    return Array.isArray(field?.rules) && field.rules.includes('required');
}

export function defaultValueForField(field) {
    if (field.ui_component === 'file') {
        return null;
    }

    if (field.type === 'boolean') {
        return false;
    }

    return '';
}

export function defaultUiComponentForType(type) {
    switch (String(type || '').toLowerCase()) {
        case 'text':
            return 'textarea';
        case 'date':
            return 'date';
        case 'datetime':
            return 'datetime-local';
        case 'time':
            return 'time';
        case 'file':
            return 'file';
        case 'email':
            return 'email';
        case 'number':
        case 'integer':
        case 'float':
        case 'decimal':
            return 'number';
        default:
            return 'input';
    }
}

export function normalizeUiComponent(component) {
    switch (String(component || '').toLowerCase()) {
        case 'text_input':
            return 'input';
        case 'select_dropdown':
            return 'select';
        case 'date_picker':
            return 'date';
        case 'datetime_picker':
            return 'datetime-local';
        case 'time_picker':
            return 'time';
        default:
            return component || 'input';
    }
}

export function humanize(value) {
    return String(value || '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}
