import { computed } from 'vue';

export function useSchemaCompiler(form) {
    // 1. Checks for duplicate JSON keys to prevent deployment errors
    const hasGlobalDuplicates = computed(() => {
        const keys = form.schema_payload.map(f => f.key).filter(k => k);
        return new Set(keys).size !== keys.length;
    });

    // 2. Maps the raw Vue state into the strict structure required by your database/engine
    const compiledJson = computed(() => {
        const fields = [...form.schema_payload].sort((a, b) => a.order - b.order).map(f => {
            const node = {
                key: f.key,
                type: f.type,
                label: f.label,
                ui_component: f.ui_component,
                grid_span: f.grid_span,
                rules: f.rules,
                is_array: f.is_array,
                order: f.order
            };

            if (f.placeholder) node.placeholder = f.placeholder;
            if (f.text_transform !== 'none') node.text_transform = f.text_transform;
            if (f.api_endpoint || f.cascade_parent) {
                node.data_source = {};
                if (f.api_endpoint) node.data_source.endpoint = f.api_endpoint;
                if (f.cascade_parent) node.data_source.cascade_from = f.cascade_parent;
            }
            if (f.type === 'file') {
                node.file_options = {
                    max_size_mb: f.file_max_size,
                    max_count: f.file_max_count,
                    allowed_types: f.file_types || '*',
                    enable_preview: f.file_preview
                };
            }
            return node;
        });

        return JSON.stringify({
            fields,
            document_output: form.document_output,
            pricing_units: form.pricing_units
        }, null, 4);
    });

    return { compiledJson, hasGlobalDuplicates };
}
