<?php

namespace App\Services;

use App\Models\DocumentTemplate;

class PdfCompilerService
{
    public function __construct(
        private readonly DocumentTemplateLayoutService $layoutService,
    ) {
    }

    public function compileToHtml(DocumentTemplate $template, array $dataPayload = [], array $options = []): string
    {
        $vector = $this->layoutService->normalize($template->layout_vector);
        $renderMode = $options['render_mode'] ?? 'pdf';

        $page = $vector['page'] ?? [];
        $header = $vector['header'] ?? [];
        $body = $vector['body'] ?? [];
        $footer = $vector['footer'] ?? [];

        $pageStyles = $this->generatePageStyles($page, $renderMode);
        $headerHtml = $this->compileNodes($header, $dataPayload, $options);
        $bodyHtml = $this->compileNodes($body, $dataPayload, $options);
        $footerHtml = $this->compileNodes($footer, $dataPayload, $options);

        return $this->wrapInMasterTemplate($pageStyles, $headerHtml, $bodyHtml, $footerHtml, $page, $renderMode);
    }

    private function compileNodes(array $nodes, array $dataPayload, array $options = []): string
    {
        $html = '';
        $renderMode = $options['render_mode'] ?? 'pdf';

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $type = $node['type'] ?? 'unknown';
            $styles = $this->cssArrayToString($node['styles'] ?? [], $renderMode);

            switch ($type) {
                case 'row':
                    $html .= '<table style="width: 100%; border-collapse: collapse; table-layout: fixed; '.$styles.'"><tr>';

                    foreach ($node['columns'] ?? [] as $col) {
                        $span = max(1, min(12, (int) ($col['span'] ?? 12)));
                        $width = round(($span / 12) * 100, 2).'%';

                        $html .= '<td style="width: '.$width.'; vertical-align: top; padding: 0; margin: 0;">';
                        $html .= $this->compileNodes($col['blocks'] ?? [], $dataPayload, $options);
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
                    $url = $this->resolveImageSource($node, $dataPayload, $options);
                    $wrapperStyles = $this->cssArrayToString($this->filterStyles(
                        $node['styles'] ?? [],
                        ['margin', 'padding', 'backgroundColor', 'borderRadius', 'border', 'textAlign']
                    ), $renderMode);
                    $imageStyles = $this->cssArrayToString($this->filterStyles(
                        $node['styles'] ?? [],
                        ['width', 'height', 'objectFit']
                    ), $renderMode);

                    $html .= '<div style="width: 100%; '.$wrapperStyles.'">';

                    if ($url !== '') {
                        $html .= '<img src="'.$this->escapeAttribute($url).'" style="'.$imageStyles.' max-width: 100%; display: inline-block;" />';
                    }

                    $html .= '</div>';
                    break;

                case 'divider':
                case 'spacer':
                    $html .= '<div style="width: 100%; '.$styles.'"></div>';
                    break;

                case 'table':
                    $html .= $this->compileTable($node, $dataPayload, $renderMode);
                    break;

                case 'page_break':
                    $html .= '<div style="page-break-after: always;"></div>';
                    break;
            }
        }

        return $html;
    }

    private function compileTable(array $node, array $dataPayload, string $renderMode = 'pdf'): string
    {
        if (($node['preset'] ?? '') === 'invoice_line_items') {
            return $this->compileInvoiceLineItemsTable($node, $dataPayload, $renderMode);
        }

        $styles = $this->cssArrayToString($node['styles'] ?? [], $renderMode);
        $columns = is_array($node['columns'] ?? null) ? $node['columns'] : [];
        $dataKey = (string) ($node['data_key'] ?? '');
        $fontSize = $this->escapeAttribute((string) ($node['styles']['fontSize'] ?? '12px'));
        $fontFamily = $this->escapeAttribute($this->resolveFontFamily((string) ($node['styles']['fontFamily'] ?? 'inherit'), $renderMode));
        $headerFontSize = $this->escapeAttribute($this->scaleCssSize((string) ($node['styles']['fontSize'] ?? '12px'), 0.85, '10px'));
        $textColor = $this->escapeAttribute((string) ($node['styles']['color'] ?? '#0f172a'));
        $mutedColor = $this->escapeAttribute($this->mutedColor((string) ($node['styles']['color'] ?? '#0f172a')));

        $loopData = data_get($dataPayload, $dataKey, []);
        $loopData = is_array($loopData) ? $loopData : [];

        $html = '<table style="width: 100%; border-collapse: collapse; '.$styles.'">';
        $html .= '<thead><tr>';

        foreach ($columns as $col) {
            $label = $this->escape((string) ($col['label'] ?? 'Column'));
            $html .= '<th style="border: 1px solid #cbd5e1; background-color: #f8fafc; padding: 8px; text-align: left; font-size: '.$headerFontSize.'; font-family: '.$fontFamily.'; font-weight: 700; color: '.$mutedColor.'; text-transform: uppercase; letter-spacing: 0.12em;">'.$label.'</th>';
        }

        $html .= '</tr></thead><tbody>';

        if ($loopData === []) {
            $colCount = max(1, count($columns));
            $html .= '<tr><td colspan="'.$colCount.'" style="text-align: center; padding: 10px; color: '.$mutedColor.'; border: 1px solid #cbd5e1; font-size: '.$fontSize.'; font-family: '.$fontFamily.';">No data available</td></tr>';
        } else {
            foreach ($loopData as $row) {
                $html .= '<tr>';

                foreach ($columns as $col) {
                    $key = (string) ($col['key'] ?? '');
                    $cellValue = data_get($row, $key, '');

                    $html .= '<td style="border: 1px solid #e2e8f0; padding: 8px; vertical-align: top; font-size: '.$fontSize.'; font-family: '.$fontFamily.'; color: '.$textColor.';">'.$this->renderCellValue($cellValue).'</td>';
                }

                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function compileInvoiceLineItemsTable(array $node, array $dataPayload, string $renderMode = 'pdf'): string
    {
        $styles = $this->cssArrayToString($node['styles'] ?? [], $renderMode);
        $columns = is_array($node['columns'] ?? null) ? $node['columns'] : [];
        $dataKey = (string) ($node['data_key'] ?? 'invoice.line_items');
        $fontSize = $this->escapeAttribute((string) ($node['styles']['fontSize'] ?? '12px'));
        $fontFamily = $this->escapeAttribute($this->resolveFontFamily((string) ($node['styles']['fontFamily'] ?? 'inherit'), $renderMode));
        $headerFontSize = $this->escapeAttribute($this->scaleCssSize((string) ($node['styles']['fontSize'] ?? '12px'), 0.84, '10px'));
        $summaryFontSize = $this->escapeAttribute((string) ($node['styles']['fontSize'] ?? '12px'));
        $grandTotalFontSize = $this->escapeAttribute($this->scaleCssSize((string) ($node['styles']['fontSize'] ?? '12px'), 1.16, '14px'));
        $textColor = $this->escapeAttribute((string) ($node['styles']['color'] ?? '#0f172a'));
        $mutedColor = $this->escapeAttribute($this->mutedColor((string) ($node['styles']['color'] ?? '#0f172a')));

        $loopData = data_get($dataPayload, $dataKey, []);
        $loopData = is_array($loopData) ? $loopData : [];
        $summary = $this->resolveTableSummary($dataKey, $dataPayload);

        $html = '<div style="width: 100%; '.$styles.'">';
        $html .= '<table style="width: 100%; border-collapse: collapse; table-layout: fixed;">';
        $html .= '<thead><tr>';

        foreach ($columns as $col) {
            $key = (string) ($col['key'] ?? '');
            $label = $this->escape((string) ($col['label'] ?? 'Column'));
            $width = $this->invoiceTableColumnWidth($key);
            $align = $this->invoiceTableColumnAlign($key);
            $padding = $key === 'description' ? '0 16px 10px 0' : '0 0 10px 0';

            $html .= '<th style="width: '.$width.'; border-bottom: 1px solid #cbd5e1; padding: '.$padding.'; text-align: '.$align.'; font-size: '.$headerFontSize.'; font-family: '.$fontFamily.'; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: '.$mutedColor.';">'.$label.'</th>';
        }

        $html .= '</tr></thead><tbody>';

        if ($loopData === []) {
            $html .= '<tr><td colspan="'.max(1, count($columns)).'" style="padding: 12px 0; text-align: center; color: '.$mutedColor.'; font-size: '.$fontSize.'; font-family: '.$fontFamily.';">No items yet</td></tr>';
        } else {
            foreach ($loopData as $row) {
                $html .= '<tr>';

                foreach ($columns as $col) {
                    $key = (string) ($col['key'] ?? '');
                    $cellValue = data_get($row, $key, '');
                    $align = $this->invoiceTableColumnAlign($key);
                    $padding = $key === 'description' ? '12px 16px 12px 0' : '12px 0';
                    $fontWeight = $key === 'description' ? '600' : '400';
                    $color = $key === 'description' ? $textColor : $mutedColor;
                    $whiteSpace = $key === 'description' ? 'pre-wrap' : 'normal';

                    $html .= '<td style="border-bottom: 1px solid #e2e8f0; padding: '.$padding.'; vertical-align: top; text-align: '.$align.'; font-size: '.$fontSize.'; font-family: '.$fontFamily.'; font-weight: '.$fontWeight.'; color: '.$color.'; white-space: '.$whiteSpace.';">'.$this->renderCellValue($cellValue).'</td>';
                }

                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';
        $html .= '<div style="margin-top: 14px; width: 100%; text-align: right;">';
        $html .= '<table style="width: 260px; margin-left: auto; border-collapse: collapse;">';
        $html .= '<tr><td style="padding: 4px 0; font-size: '.$summaryFontSize.'; font-family: '.$fontFamily.'; color: '.$mutedColor.';">Subtotal</td><td style="padding: 4px 0; font-size: '.$summaryFontSize.'; font-family: '.$fontFamily.'; font-weight: 600; color: '.$textColor.'; text-align: right;">'.$this->escape((string) $summary['subtotal']).'</td></tr>';
        $html .= '<tr><td style="padding: 4px 0; font-size: '.$summaryFontSize.'; font-family: '.$fontFamily.'; color: '.$mutedColor.';">Tax</td><td style="padding: 4px 0; font-size: '.$summaryFontSize.'; font-family: '.$fontFamily.'; font-weight: 600; color: '.$textColor.'; text-align: right;">'.$this->escape((string) $summary['tax_total']).'</td></tr>';
        $html .= '<tr><td style="padding: 10px 0 0; border-top: 1px solid #cbd5e1; font-size: '.$grandTotalFontSize.'; font-family: '.$fontFamily.'; font-weight: 700; color: '.$textColor.';">Grand Total</td><td style="padding: 10px 0 0; border-top: 1px solid #cbd5e1; font-size: '.$grandTotalFontSize.'; font-family: '.$fontFamily.'; font-weight: 700; color: '.$textColor.'; text-align: right;">'.$this->escape((string) $summary['grand_total']).'</td></tr>';
        $html .= '</table></div></div>';

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

    private function resolveImageSource(array $node, array $dataPayload, array $options = []): string
    {
        $assetMode = $options['asset_mode'] ?? 'pdf';

        if (($node['source_mode'] ?? 'static') === 'dynamic' && ! empty($node['data_key'])) {
            $dynamicValue = data_get($dataPayload, (string) $node['data_key']);

            if (is_string($dynamicValue) && $dynamicValue !== '') {
                return $this->normalizeResolvedImageSource($dynamicValue, $assetMode);
            }

            if (is_array($dynamicValue)) {
                foreach (['logo_url', 'url', 'asset_path'] as $candidate) {
                    $candidateValue = $dynamicValue[$candidate] ?? null;

                    if (is_string($candidateValue) && $candidateValue !== '') {
                        return $this->normalizeResolvedImageSource($candidateValue, $assetMode);
                    }
                }
            }
        }

        $assetPath = (string) ($node['asset_path'] ?? '');

        if ($assetPath !== '') {
            if (str_starts_with($assetPath, 'http://') || str_starts_with($assetPath, 'https://') || str_starts_with($assetPath, 'data:')) {
                return $assetPath;
            }

            if ($assetMode === 'browser') {
                return str_starts_with($assetPath, '/storage/')
                    ? $assetPath
                    : '/storage/'.ltrim(str_replace('storage/', '', $assetPath), '/');
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

    private function resolveTableSummary(string $dataKey, array $dataPayload): array
    {
        $rootPath = str_ends_with($dataKey, '.line_items')
            ? substr($dataKey, 0, -1 * strlen('.line_items'))
            : '';

        $subtotal = $rootPath !== '' ? data_get($dataPayload, $rootPath.'.subtotal') : null;
        $taxTotal = $rootPath !== '' ? data_get($dataPayload, $rootPath.'.tax_total') : null;
        $grandTotal = $rootPath !== '' ? data_get($dataPayload, $rootPath.'.grand_total') : null;

        return [
            'subtotal' => is_scalar($subtotal) ? (string) $subtotal : (string) (data_get($dataPayload, 'finance.formatted_subtotal', '') ?: ''),
            'tax_total' => is_scalar($taxTotal) ? (string) $taxTotal : (string) (data_get($dataPayload, 'finance.formatted_tax_total', '') ?: ''),
            'grand_total' => is_scalar($grandTotal) ? (string) $grandTotal : (string) (data_get($dataPayload, 'finance.formatted_grand_total', '') ?: ''),
        ];
    }

    private function invoiceTableColumnWidth(string $key): string
    {
        return match ($key) {
            'description' => '42%',
            'unit' => '12%',
            'quantity' => '10%',
            'unit_price' => '18%',
            'total' => '18%',
            default => 'auto',
        };
    }

    private function invoiceTableColumnAlign(string $key): string
    {
        return in_array($key, ['quantity', 'unit_price', 'total'], true)
            ? 'right'
            : 'left';
    }

    private function cssArrayToString(array $styles, string $renderMode = 'pdf'): string
    {
        $css = '';

        foreach ($styles as $key => $value) {
            if (! is_scalar($value) || $value === '') {
                continue;
            }

            $kebabKey = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', (string) $key));
            $resolvedValue = $key === 'fontFamily'
                ? $this->resolveFontFamily((string) $value, $renderMode)
                : (string) $value;

            $css .= $kebabKey.': '.$this->escapeAttribute($resolvedValue).'; ';
        }

        return $css;
    }

    private function generatePageStyles(array $page, string $renderMode = 'pdf'): string
    {
        $margins = $this->escapeAttribute((string) ($page['margins'] ?? '20mm'));
        $bg = $this->escapeAttribute((string) ($page['backgroundColor'] ?? '#ffffff'));
        $size = $this->escapeAttribute((string) ($page['size'] ?? 'A4'));
        $orientation = $this->escapeAttribute((string) ($page['orientation'] ?? 'portrait'));
        $fontFamily = $this->escapeAttribute($this->resolveFontFamily((string) ($page['fontFamily'] ?? config('documents.default_font', 'sans')), $renderMode));

        return "
            @page {
                size: {$size} {$orientation};
                margin: {$margins};
            }
            body {
                background-color: {$bg};
                margin: 0;
                padding: 0;
                font-family: {$fontFamily};
                font-size: 12px;
                color: #333333;
            }
        ";
    }

    private function wrapInMasterTemplate(string $pageStyles, string $header, string $body, string $footer, array $page, string $renderMode = 'pdf'): string
    {
        $watermark = '';
        $contentHeight = $this->resolvePrintableContentHeight($page);
        $footerReserve = trim($footer) !== '' ? '18mm' : '0mm';
        $shellStyles = $renderMode === 'browser'
            ? '
                .document-shell {
                    min-height: '.$contentHeight.';
                    display: flex;
                    flex-direction: column;
                    width: 100%;
                }
                .document-header,
                .document-main,
                .document-footer {
                    width: 100%;
                }
                .document-header {
                    flex: 0 0 auto;
                }
                .document-main {
                    flex: 1 0 auto;
                    min-height: 0;
                }
                .document-footer {
                    flex: 0 0 auto;
                    margin-top: auto;
                }
            '
            : '
                .document-shell {
                    width: 100%;
                    min-height: '.$contentHeight.';
                }
                .document-header,
                .document-main,
                .document-footer {
                    width: 100%;
                }
                .document-main {
                    padding-bottom: '.$footerReserve.';
                }
                .document-footer {
                    position: fixed;
                    left: 0;
                    right: 0;
                    bottom: 0;
                }
                .document-section-inner {
                    width: 100%;
                }
            ';

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
                html, body { height: 100%; }
                {$shellStyles}
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
            <div class="document-shell" data-render-mode="{$this->escapeAttribute($renderMode)}">
                <div class="document-header"><div class="document-section-inner">{$header}</div></div>
                <div class="document-main"><div class="document-section-inner">{$body}</div></div>
                <div class="document-footer"><div class="document-section-inner">{$footer}</div></div>
            </div>
        </body>
        </html>
        HTML;
    }

    private function resolvePrintableContentHeight(array $page): string
    {
        [$width, $height] = $this->resolvePageSizeMm(
            (string) ($page['size'] ?? 'A4'),
            (string) ($page['orientation'] ?? 'portrait')
        );

        $verticalMargins = $this->resolveVerticalMarginExpression((string) ($page['margins'] ?? '20mm'));

        return 'calc('.$height.' - ('.$verticalMargins.'))';
    }

    private function resolvePageSizeMm(string $size, string $orientation): array
    {
        $normalizedSize = strtoupper(trim($size));
        $dimensions = match ($normalizedSize) {
            'A5' => ['148mm', '210mm'],
            'LETTER' => ['215.9mm', '279.4mm'],
            'LEGAL' => ['215.9mm', '355.6mm'],
            default => ['210mm', '297mm'],
        };

        if (strtolower(trim($orientation)) === 'landscape') {
            return [$dimensions[1], $dimensions[0]];
        }

        return $dimensions;
    }

    private function resolveVerticalMarginExpression(string $margins): string
    {
        $parts = preg_split('/\s+/', trim($margins)) ?: [];
        $parts = array_values(array_filter($parts, fn ($part) => $part !== ''));

        if ($parts === []) {
            return '40mm';
        }

        return match (count($parts)) {
            1 => $parts[0].' + '.$parts[0],
            2 => $parts[0].' + '.$parts[0],
            3 => $parts[0].' + '.$parts[2],
            default => $parts[0].' + '.$parts[2],
        };
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

    private function normalizeResolvedImageSource(string $value, string $assetMode): string
    {
        if ($value === '' || str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:')) {
            return $value;
        }

        if ($assetMode === 'browser') {
            return str_starts_with($value, '/storage/')
                ? $value
                : '/storage/'.ltrim(str_replace('storage/', '', $value), '/');
        }

        if (str_starts_with($value, '/storage/')) {
            return public_path(ltrim($value, '/'));
        }

        return public_path('storage/'.ltrim(str_replace('storage/', '', $value), '/'));
    }

    private function resolveFontFamily(string $value, string $renderMode): string
    {
        foreach (config('documents.font_presets', []) as $preset) {
            if (($preset['value'] ?? null) === $value) {
                return (string) ($renderMode === 'browser'
                    ? ($preset['css_family'] ?? 'Helvetica, Arial, sans-serif')
                    : ($preset['pdf_family'] ?? 'Helvetica'));
            }
        }

        return $value !== '' ? $value : ($renderMode === 'browser' ? 'Helvetica, Arial, sans-serif' : 'Helvetica');
    }

    private function filterStyles(array $styles, array $allowedKeys): array
    {
        $filtered = [];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $styles) && is_scalar($styles[$key]) && $styles[$key] !== '') {
                $filtered[$key] = $styles[$key];
            }
        }

        return $filtered;
    }

    private function scaleCssSize(string $value, float $ratio, string $fallback): string
    {
        if (preg_match('/^\s*([0-9]*\.?[0-9]+)\s*(px|pt|rem|em|mm|cm|in)?\s*$/i', $value, $matches) !== 1) {
            return $fallback;
        }

        $number = (float) $matches[1];
        $unit = $matches[2] ?? 'px';

        return rtrim(rtrim(number_format($number * $ratio, 2, '.', ''), '0'), '.').$unit;
    }

    private function mutedColor(string $baseColor): string
    {
        $normalized = strtolower(trim($baseColor));

        return match ($normalized) {
            '#000000', '#0f172a', '#111827', '#1f2937', '#020617' => '#64748b',
            default => '#475569',
        };
    }
}
