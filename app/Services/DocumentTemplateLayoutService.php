<?php

namespace App\Services;

class DocumentTemplateLayoutService
{
    private const SUPPORTED_BLOCK_TYPES = [
        'row',
        'text',
        'image',
        'list',
        'divider',
        'spacer',
        'table',
        'page_break',
    ];

    public function defaultLayoutVector(): array
    {
        return [
            'version' => 1,
            'page' => [
                'isPage' => true,
                'size' => 'A4',
            'orientation' => 'portrait',
            'margins' => '10mm',
            'backgroundColor' => '#ffffff',
            'fontFamily' => (string) config('documents.default_font', 'sans'),
            'watermarkText' => null,
            'watermarkOpacity' => 0.1,
            'watermarkColor' => '#e5e7eb',
            ],
            'header' => [],
            'body' => [],
            'footer' => [],
        ];
    }

    public function normalize(array|string|null $layoutVector): array
    {
        if (is_string($layoutVector)) {
            $decoded = json_decode($layoutVector, true);
            $layoutVector = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($layoutVector)) {
            $layoutVector = [];
        }

        $normalized = array_replace_recursive(
            $this->defaultLayoutVector(),
            $layoutVector
        );

        $normalized['version'] = (int) ($normalized['version'] ?? 1);
        $normalized['header'] = $this->normalizeNodeCollection($normalized['header'] ?? []);
        $normalized['body'] = $this->normalizeNodeCollection($normalized['body'] ?? []);
        $normalized['footer'] = $this->normalizeNodeCollection($normalized['footer'] ?? []);
        $normalized['page'] = $this->normalizePage($normalized['page'] ?? []);

        return $normalized;
    }

    public function validate(array $layoutVector): array
    {
        $layoutVector = $this->normalize($layoutVector);
        $errors = [];

        foreach (['header', 'body', 'footer'] as $zone) {
            if (! is_array($layoutVector[$zone])) {
                $errors[] = "The [{$zone}] zone must be an array.";
                continue;
            }

            $this->validateNodeCollection($layoutVector[$zone], "layout_vector.{$zone}", $errors);
        }

        return array_values(array_unique($errors));
    }

    private function normalizePage(array $page): array
    {
        return [
            'isPage' => true,
            'size' => (string) ($page['size'] ?? 'A4'),
            'orientation' => in_array(($page['orientation'] ?? 'portrait'), ['portrait', 'landscape'], true)
                ? $page['orientation']
                : 'portrait',
            'margins' => (string) ($page['margins'] ?? '10mm'),
            'backgroundColor' => (string) ($page['backgroundColor'] ?? '#ffffff'),
            'fontFamily' => (string) ($page['fontFamily'] ?? config('documents.default_font', 'sans')),
            'watermarkText' => $page['watermarkText'] ?? null,
            'watermarkOpacity' => is_numeric($page['watermarkOpacity'] ?? null) ? (float) $page['watermarkOpacity'] : 0.1,
            'watermarkColor' => (string) ($page['watermarkColor'] ?? '#e5e7eb'),
        ];
    }

    private function normalizeNodeCollection(array $nodes): array
    {
        $normalized = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $type = (string) ($node['type'] ?? '');

            if (! in_array($type, self::SUPPORTED_BLOCK_TYPES, true)) {
                continue;
            }

            $normalized[] = $this->normalizeNode($node);
        }

        return array_values($normalized);
    }

    private function normalizeNode(array $node): array
    {
        $type = (string) ($node['type'] ?? '');
        $base = [
            'id' => (string) ($node['id'] ?? $this->makeNodeId($type)),
            'type' => $type,
            'styles' => is_array($node['styles'] ?? null) ? $node['styles'] : [],
        ];

        return match ($type) {
            'row' => array_merge($base, [
                'layout' => (string) ($node['layout'] ?? 'row_12'),
                'columns' => $this->normalizeColumns($node['columns'] ?? []),
            ]),
            'text' => array_merge($base, [
                'label' => (string) ($node['label'] ?? 'Text Node'),
                'content' => (string) ($node['content'] ?? ''),
                'data_key' => $node['data_key'] ?? null,
            ]),
            'image' => array_merge($base, [
                'label' => (string) ($node['label'] ?? 'Image'),
                'source_mode' => ($node['source_mode'] ?? 'static') === 'dynamic' ? 'dynamic' : 'static',
                'data_key' => (string) ($node['data_key'] ?? ''),
                'url' => (string) ($node['url'] ?? ''),
                'asset_path' => $node['asset_path'] ?? null,
            ]),
            'list' => array_merge($base, [
                'label' => (string) ($node['label'] ?? 'List'),
                'content' => (string) ($node['content'] ?? ''),
                'data_key' => (string) ($node['data_key'] ?? ''),
            ]),
            'table' => array_merge($base, [
                'label' => (string) ($node['label'] ?? 'Data Table'),
                'preset' => (string) ($node['preset'] ?? ''),
                'data_key' => (string) ($node['data_key'] ?? ''),
                'columns' => $this->normalizeTableColumns($node['columns'] ?? []),
            ]),
            'divider' => array_merge($base, [
                'label' => (string) ($node['label'] ?? 'Divider'),
            ]),
            'spacer' => array_merge($base, [
                'label' => (string) ($node['label'] ?? 'Spacer'),
            ]),
            'page_break' => array_merge($base, [
                'label' => (string) ($node['label'] ?? 'Page Break'),
            ]),
            default => $base,
        };
    }

    private function normalizeColumns(array $columns): array
    {
        $normalized = [];

        foreach ($columns as $index => $column) {
            if (! is_array($column)) {
                continue;
            }

            $span = (int) ($column['span'] ?? 12);
            $span = max(1, min(12, $span));

            $normalized[] = [
                'id' => (string) ($column['id'] ?? 'col_'.$index.'_'.time()),
                'span' => $span,
                'blocks' => $this->normalizeNodeCollection($column['blocks'] ?? []),
            ];
        }

        return $normalized === []
            ? [[
                'id' => 'col_default_'.time(),
                'span' => 12,
                'blocks' => [],
            ]]
            : array_values($normalized);
    }

    private function normalizeTableColumns(array $columns): array
    {
        $normalized = [];

        foreach ($columns as $column) {
            if (! is_array($column)) {
                continue;
            }

            $normalized[] = [
                'label' => (string) ($column['label'] ?? 'Column'),
                'key' => (string) ($column['key'] ?? ''),
            ];
        }

        return array_values($normalized);
    }

    private function validateNodeCollection(array $nodes, string $path, array &$errors): void
    {
        foreach ($nodes as $index => $node) {
            if (! is_array($node)) {
                $errors[] = "{$path}.{$index} must be an object.";
                continue;
            }

            $type = (string) ($node['type'] ?? '');

            if (! in_array($type, self::SUPPORTED_BLOCK_TYPES, true)) {
                $errors[] = "{$path}.{$index} has unsupported block type [{$type}].";
                continue;
            }

            if ($type === 'row') {
                if (! is_array($node['columns'] ?? null) || count($node['columns']) === 0) {
                    $errors[] = "{$path}.{$index}.columns must contain at least one column.";
                    continue;
                }

                foreach ($node['columns'] as $columnIndex => $column) {
                    if (! is_array($column)) {
                        $errors[] = "{$path}.{$index}.columns.{$columnIndex} must be an object.";
                        continue;
                    }

                    if (! is_array($column['blocks'] ?? null)) {
                        $errors[] = "{$path}.{$index}.columns.{$columnIndex}.blocks must be an array.";
                        continue;
                    }

                    $this->validateNodeCollection(
                        $column['blocks'],
                        "{$path}.{$index}.columns.{$columnIndex}.blocks",
                        $errors
                    );
                }
            }

            if ($type === 'table') {
                if (empty($node['data_key'])) {
                    $errors[] = "{$path}.{$index}.data_key is required for table blocks.";
                }

                if (! is_array($node['columns'] ?? null) || count($node['columns']) === 0) {
                    $errors[] = "{$path}.{$index}.columns must contain at least one table column.";
                }
            }

            if ($type === 'list' && empty($node['data_key'])) {
                $errors[] = "{$path}.{$index}.data_key is required for list blocks.";
            }
        }
    }

    private function makeNodeId(string $type): string
    {
        return $type.'_'.str_replace('.', '', (string) microtime(true)).random_int(1000, 9999);
    }
}
