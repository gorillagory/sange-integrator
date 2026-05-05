<?php

namespace App\Services;

use App\Models\DocumentTemplate;

class PdfCompilerService
{
    public function __construct(
        private readonly DocumentTemplateLayoutService $layoutService,
    ) {
    }

    public function compileToHtml(DocumentTemplate $template, array $dataPayload = []): string
    {
        $vector = $this->layoutService->normalize($template->layout_vector);

        $page = $vector['page'] ?? [];
        $header = $vector['header'] ?? [];
        $body = $vector['body'] ?? [];
        $footer = $vector['footer'] ?? [];

        $pageStyles = $this->generatePageStyles($page);
        $headerHtml = $this->compileNodes($header, $dataPayload);
        $bodyHtml = $this->compileNodes($body, $dataPayload);
        $footerHtml = $this->compileNodes($footer, $dataPayload);

        return $this->wrapInMasterTemplate($pageStyles, $headerHtml, $bodyHtml, $footerHtml, $page);
    }

    private function compileNodes(array $nodes, array $dataPayload): string
    {
        $html = '';

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $type = $node['type'] ?? 'unknown';
            $styles = $this->cssArrayToString($node['styles'] ?? []);

            switch ($type) {
                case 'row':
                    $html .= '<table style="width: 100%; border-collapse: collapse; table-layout: fixed; '.$styles.'"><tr>';

                    foreach ($node['columns'] ?? [] as $col) {
                        $span = max(1, min(12, (int) ($col['span'] ?? 12)));
                        $width = round(($span / 12) * 100, 2).'%';

                        $html .= '<td style="width: '.$width.'; vertical-align: top; padding: 0; margin: 0;">';
                        $html .= $this->compileNodes($col['blocks'] ?? [], $dataPayload);
                        $html .= '</td>';
                    }

                    $html .= '</tr></table>';
                    break;

                case 'text':
                    $content = $this->resolveTextContent($node, $dataPayload);
                    $html .= '<div style="'.$styles.'">'.$this->safeNl2Br($content).'</div>';
                    break;

                case 'list':
                    $items = data_get($dataPayload, (string) ($node['data_key'] ?? ''), []);
                    $items = is_array($items) ? $items : [];

                    $html .= '<ul style="'.$styles.'">';

                    foreach ($items as $item) {
                        $value = is_scalar($item) ? (string) $item : json_encode($item);
                        $html .= '<li style="margin-bottom: 6px;">'.$this->escape($value).'</li>';
                    }

                    $html .= '</ul>';
                    break;

                case 'image':
                    $url = $this->resolveImageSource($node, $dataPayload);

                    $html .= '<div style="width: 100%;">';

                    if ($url !== '') {
                        $html .= '<img src="'.$this->escapeAttribute($url).'" style="'.$styles.' max-width: 100%;" />';
                    }

                    $html .= '</div>';
                    break;

                case 'divider':
                case 'spacer':
                    $html .= '<div style="width: 100%; '.$styles.'"></div>';
                    break;

                case 'table':
                    $html .= $this->compileTable($node, $dataPayload);
                    break;

                case 'page_break':
                    $html .= '<div style="page-break-after: always;"></div>';
                    break;
            }
        }

        return $html;
    }

    private function compileTable(array $node, array $dataPayload): string
    {
        $styles = $this->cssArrayToString($node['styles'] ?? []);
        $columns = is_array($node['columns'] ?? null) ? $node['columns'] : [];
        $dataKey = (string) ($node['data_key'] ?? '');

        $loopData = data_get($dataPayload, $dataKey, []);
        $loopData = is_array($loopData) ? $loopData : [];

        $html = '<table style="width: 100%; border-collapse: collapse; '.$styles.'">';
        $html .= '<thead><tr>';

        foreach ($columns as $col) {
            $label = $this->escape((string) ($col['label'] ?? 'Column'));
            $html .= '<th style="border: 1px solid #333; background-color: #f3f4f6; padding: 8px; text-align: left; font-size: 11px; font-weight: bold;">'.$label.'</th>';
        }

        $html .= '</tr></thead><tbody>';

        if ($loopData === []) {
            $colCount = max(1, count($columns));
            $html .= '<tr><td colspan="'.$colCount.'" style="text-align: center; padding: 10px; color: #999; border: 1px solid #ddd;">No data available</td></tr>';
        } else {
            foreach ($loopData as $row) {
                $html .= '<tr>';

                foreach ($columns as $col) {
                    $key = (string) ($col['key'] ?? '');
                    $cellValue = data_get($row, $key, '');

                    $html .= '<td style="border: 1px solid #ddd; padding: 8px; vertical-align: top;">'.$this->renderCellValue($cellValue).'</td>';
                }

                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function resolveTextContent(array $node, array $dataPayload): string
    {
        $content = (string) ($node['content'] ?? '');

        if (! empty($node['data_key'])) {
            $value = data_get($dataPayload, (string) $node['data_key']);

            if (is_scalar($value)) {
                $content = (string) $value;
            }
        }

        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9._]+)\s*\}\}/', function (array $matches) use ($dataPayload) {
            $value = data_get($dataPayload, $matches[1], '');

            return is_scalar($value) ? (string) $value : '';
        }, $content) ?? $content;
    }

    private function resolveImageSource(array $node, array $dataPayload): string
    {
        if (($node['source_mode'] ?? 'static') === 'dynamic' && ! empty($node['data_key'])) {
            $dynamicValue = data_get($dataPayload, (string) $node['data_key']);

            if (is_string($dynamicValue) && $dynamicValue !== '') {
                return $dynamicValue;
            }
        }

        $assetPath = (string) ($node['asset_path'] ?? '');

        if ($assetPath !== '') {
            if (str_starts_with($assetPath, 'http://') || str_starts_with($assetPath, 'https://') || str_starts_with($assetPath, 'data:')) {
                return $assetPath;
            }

            if (str_starts_with($assetPath, '/storage/')) {
                return public_path(ltrim($assetPath, '/'));
            }

            return public_path('storage/'.ltrim($assetPath, '/'));
        }

        return (string) ($node['url'] ?? '');
    }

    private function renderCellValue(mixed $value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_scalar($value)) {
            return $this->allowBasicInlineHtml((string) $value);
        }

        return $this->escape(json_encode($value));
    }

    private function allowBasicInlineHtml(string $value): string
    {
        return strip_tags($value, '<strong><b><em><i><u><br><span>');
    }

    private function cssArrayToString(array $styles): string
    {
        $css = '';

        foreach ($styles as $key => $value) {
            if (! is_scalar($value) || $value === '') {
                continue;
            }

            $kebabKey = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', (string) $key));
            $css .= $kebabKey.': '.$this->escapeAttribute((string) $value).'; ';
        }

        return $css;
    }

    private function generatePageStyles(array $page): string
    {
        $margins = $this->escapeAttribute((string) ($page['margins'] ?? '20mm'));
        $bg = $this->escapeAttribute((string) ($page['backgroundColor'] ?? '#ffffff'));
        $size = $this->escapeAttribute((string) ($page['size'] ?? 'A4'));
        $orientation = $this->escapeAttribute((string) ($page['orientation'] ?? 'portrait'));

        return "
            @page {
                size: {$size} {$orientation};
                margin: {$margins};
            }
            body {
                background-color: {$bg};
                margin: 0;
                padding: 0;
                font-family: Helvetica, Arial, sans-serif;
                font-size: 12px;
                color: #333333;
            }
        ";
    }

    private function wrapInMasterTemplate(string $pageStyles, string $header, string $body, string $footer, array $page): string
    {
        $watermark = '';

        if (! empty($page['watermarkText'])) {
            $text = $this->escape((string) $page['watermarkText']);
            $opacity = (float) ($page['watermarkOpacity'] ?? 0.1);
            $color = $this->escapeAttribute((string) ($page['watermarkColor'] ?? '#e5e7eb'));

            $watermark = '<div class="watermark" style="color: '.$color.'; opacity: '.$opacity.';">'.$text.'</div>';
        }

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Compiled Document</title>
            <style>
                {$pageStyles}
                header { position: fixed; top: 0; left: 0; right: 0; }
                footer { position: fixed; bottom: 0; left: 0; right: 0; }
                main { position: relative; width: 100%; }
                .watermark {
                    position: fixed;
                    top: 40%;
                    left: 10%;
                    right: 10%;
                    text-align: center;
                    font-size: 48px;
                    font-weight: bold;
                    transform: rotate(-30deg);
                    z-index: -1;
                }
            </style>
        </head>
        <body>
            {$watermark}
            <header>{$header}</header>
            <footer>{$footer}</footer>
            <main>{$body}</main>
        </body>
        </html>
        HTML;
    }

    private function safeNl2Br(string $value): string
    {
        return nl2br($this->escape($value));
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function escapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
