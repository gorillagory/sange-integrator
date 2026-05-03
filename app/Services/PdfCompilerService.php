<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Log;

class PdfCompilerService
{
    /**
     * Compiles a Document Template into HTML using the provided data payload.
     */
    public function compileToHtml(DocumentTemplate $template, array $dataPayload = []): string
    {
        // Decode JSON if it's stored as a string
        $vector = is_string($template->layout_vector) ? json_decode($template->layout_vector, true) : $template->layout_vector;

        $page = $vector['page'] ?? [];
        $header = $vector['header'] ?? [];
        $body = $vector['body'] ?? [];
        $footer = $vector['footer'] ?? [];

        // 1. Generate Global Page CSS
        $pageStyles = $this->generatePageStyles($page);

        // 2. Compile Zones
        $headerHtml = $this->compileNodes($header, $dataPayload);
        $bodyHtml = $this->compileNodes($body, $dataPayload);
        $footerHtml = $this->compileNodes($footer, $dataPayload);

        // 3. Wrap in Master HTML Print Template
        return $this->wrapInMasterTemplate($pageStyles, $headerHtml, $bodyHtml, $footerHtml);
    }

    /**
     * Recursively compiles an array of nodes (Rows, Texts, Tables) into HTML.
     */
    private function compileNodes(array $nodes, array $dataPayload): string
    {
        $html = '';

        foreach ($nodes as $node) {
            $type = $node['type'] ?? 'unknown';
            $styles = $this->cssArrayToString($node['styles'] ?? []);

            switch ($type) {
                case 'row':
                    // DomPDF Trick: Use actual <table> tags for perfect grid columns
                    $html .= "<table style=\"width: 100%; border-collapse: collapse; table-layout: fixed; {$styles}\">";
                    $html .= "<tr>";
                    foreach ($node['columns'] ?? [] as $col) {
                        $span = $col['span'] ?? 12;
                        $width = round(($span / 12) * 100, 2) . '%';
                        $html .= "<td style=\"width: {$width}; vertical-align: top; padding: 0; margin: 0;\">";
                        $html .= $this->compileNodes($col['blocks'] ?? [], $dataPayload);
                        $html .= "</td>";
                    }
                    $html .= "</tr></table>";
                    break;

                case 'text':
                    $content = $node['content'] ?? '';

                    // 1. Direct Data Key Replacement (If the block is mapped entirely to a key)
                    if (!empty($node['data_key'])) {
                        $val = data_get($dataPayload, $node['data_key']);
                        if (is_string($val) || is_numeric($val)) {
                            $content = (string) $val;
                        }
                    }

                    // 2. Token Interpolation: Replaces {{ client_name }} with actual payload data
                    $content = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function($matches) use ($dataPayload) {
                        return data_get($dataPayload, $matches[1], '');
                    }, $content);

                    $html .= "<div style=\"{$styles}\">" . nl2br($content) . "</div>";
                    break;

                case 'list':
                    $dataKey = $node['data_key'] ?? '';
                    $items = data_get($dataPayload, $dataKey, []);
                    if (!is_array($items)) $items = [];

                    $html .= "<ul style=\"{$styles}\">";
                    foreach ($items as $item) {
                        $html .= "<li style=\"margin-bottom: 6px;\">{$item}</li>";
                    }
                    $html .= "</ul>";
                    break;

                case 'image':
                    $url = $node['url'] ?? '';
                    $html .= "<div style=\"width: 100%;\">";
                    if ($url) {
                        // Max-width ensures large base64 uploads don't blow out the PDF margins
                        $html .= "<img src=\"{$url}\" style=\"{$styles} max-width: 100%;\" />";
                    }
                    $html .= "</div>";
                    break;

                case 'divider':
                case 'spacer':
                    $html .= "<div style=\"width: 100%; {$styles}\"></div>";
                    break;

                case 'table':
                    $html .= $this->compileTable($node, $dataPayload);
                    break;

                case 'page_break':
                    $html .= "<div style=\"page-break-after: always;\"></div>";
                    break;
            }
        }

        return $html;
    }

    /**
     * Compiles a Dynamic Data Table based on array mapping.
     */
    private function compileTable(array $node, array $dataPayload): string
    {
        $styles = $this->cssArrayToString($node['styles'] ?? []);
        $columns = $node['columns'] ?? [];
        $dataKey = $node['data_key'] ?? '';

        // Fetch the array of data to loop through (e.g., $dataPayload['items'])
        $loopData = data_get($dataPayload, $dataKey, []);
        if (!is_array($loopData)) {
            $loopData = [];
        }

        $html = "<table style=\"width: 100%; border-collapse: collapse; {$styles}\">";

        // Table Header
        $html .= "<thead><tr>";
        foreach ($columns as $col) {
            $html .= "<th style=\"border: 1px solid #333; background-color: #f3f4f6; padding: 8px; text-align: left; font-size: 11px; font-weight: bold;\">{$col['label']}</th>";
        }
        $html .= "</tr></thead>";

        // Table Body (The Loop)
        $html .= "<tbody>";
        if (empty($loopData)) {
            $colCount = count($columns);
            $html .= "<tr><td colspan=\"{$colCount}\" style=\"text-align: center; padding: 10px; color: #999; border: 1px solid #ddd;\">No data available</td></tr>";
        } else {
            foreach ($loopData as $row) {
                $html .= "<tr>";
                foreach ($columns as $col) {
                    $cellValue = data_get($row, $col['key'], '');
                    // Notice we DO NOT escape HTML here. This allows the Schema Vector's HTML (like <strong>) to render!
                    $html .= "<td style=\"border: 1px solid #ddd; padding: 8px; vertical-align: top;\">{$cellValue}</td>";
                }
                $html .= "</tr>";
            }
        }
        $html .= "</tbody></table>";

        return $html;
    }

    /**
     * Converts Vue JSON CSS objects into inline HTML CSS strings.
     */
    private function cssArrayToString(array $styles): string
    {
        $css = '';
        foreach ($styles as $key => $value) {
            // Converts camelCase (backgroundColor) to kebab-case (background-color)
            $kebabKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $key));
            $css .= "{$kebabKey}: {$value}; ";
        }
        return $css;
    }

    /**
     * Calculates CSS for the document margins, orientation, and size.
     */
    private function generatePageStyles(array $page): string
    {
        $margins = $page['margins'] ?? '20mm';
        $bg = $page['backgroundColor'] ?? '#ffffff';
        $size = $page['size'] ?? 'A4';
        $orientation = $page['orientation'] ?? 'portrait';

        return "
            @page {
                size: {$size} {$orientation};
                margin: {$margins};
            }
            body {
                background-color: {$bg};
                margin: 0;
                padding: 0;
                font-family: 'Helvetica', 'Arial', sans-serif;
                font-size: 12px;
                color: #333;
            }
        ";
    }

    /**
     * Wraps the compiled zones into a structured HTML template required by DomPDF.
     */
    private function wrapInMasterTemplate(string $pageStyles, string $header, string $body, string $footer): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Compiled Document</title>
            <style>
                {$pageStyles}
                /* DomPDF Fixed Positioning for Headers/Footers */
                header { position: fixed; top: 0px; left: 0px; right: 0px; }
                footer { position: fixed; bottom: 0px; left: 0px; right: 0px; }
                /* The main body content */
                main { position: relative; width: 100%; }
            </style>
        </head>
        <body>
            <header>{$header}</header>
            <footer>{$footer}</footer>
            <main>{$body}</main>
        </body>
        </html>
        HTML;
    }
}
