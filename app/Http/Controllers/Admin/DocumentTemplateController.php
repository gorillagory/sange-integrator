<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentTemplateRequest;
use App\Http\Requests\Admin\UpdateDocumentTemplateRequest;
use App\Models\DocumentTemplate;
use App\Services\DocumentPreviewPayloadFactory;
use App\Services\DocumentTemplateBindingIndexService;
use App\Services\DocumentTemplateCompatibilityService;
use App\Services\DocumentRenderContextFactory;
use App\Services\DocumentTemplateLayoutService;
use App\Services\DocumentVariableService;
use App\Services\PdfCompilerService;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentTemplateController extends Controller
{
    public function __construct(
        private readonly DocumentTemplateLayoutService $layoutService,
        private readonly DocumentPreviewPayloadFactory $previewPayloadFactory,
        private readonly DocumentTemplateBindingIndexService $bindingIndexService,
        private readonly DocumentTemplateCompatibilityService $compatibilityService,
        private readonly DocumentRenderContextFactory $renderContextFactory,
        private readonly PdfCompilerService $compiler,
    ) {
    }

    public function index(): Response
    {
        $templates = DocumentTemplate::query()
            ->orderBy('name')
            ->get()
            ->map(function (DocumentTemplate $template) {
                $bindingIndex = is_array($template->binding_index)
                    ? $template->binding_index
                    : $this->bindingIndexService->extract($this->layoutService->normalize($template->layout_vector));

                $compatibility = $this->compatibilityService->analyze($template->document_type, $bindingIndex);

                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'code' => $template->code,
                    'document_type' => $template->document_type,
                    'layout_vector' => $template->layout_vector,
                    'binding_index' => $bindingIndex,
                    'compatibility' => $compatibility,
                    'created_at' => optional($template->created_at)?->toDateTimeString(),
                    'updated_at' => optional($template->updated_at)?->toDateTimeString(),
                ];
            })
            ->values();

        return Inertia::render('Admin/Documents/Index', [
            'templates' => $templates,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Documents/Builder', [
            'template' => null,
            'dictionaries' => $this->getAllDictionaries(),
            'documentTypes' => DocumentVariableService::supportedDocumentTypes(),
            'fontOptions' => $this->getFontOptions(),
            'defaultLayoutVector' => $this->layoutService->defaultLayoutVector(),
        ]);
    }

    public function store(StoreDocumentTemplateRequest $request)
    {
        $validated = $request->validated();

        $template = DocumentTemplate::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'document_type' => $validated['document_type'],
            'layout_vector' => $this->layoutService->normalize($validated['layout_vector']),
        ]);

        if ($request->boolean('exit_after_save')) {
            return redirect()
                ->route('admin.documents.index', [
                    'subdomain' => request()->route('subdomain'),
                ])
                ->with('success', 'Template created and saved.');
        }

        return redirect()
            ->route('admin.documents.edit', [
                'subdomain' => request()->route('subdomain'),
                'id' => $template->id,
            ])
            ->with('success', 'Template created successfully.');
    }

    public function edit(string $subdomain, int $id): Response
    {
        $template = DocumentTemplate::query()->findOrFail($id);

        return Inertia::render('Admin/Documents/Builder', [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'code' => $template->code,
                'document_type' => $template->document_type,
                'layout_vector' => $this->layoutService->normalize($template->layout_vector),
                'created_at' => optional($template->created_at)?->toDateTimeString(),
                'updated_at' => optional($template->updated_at)?->toDateTimeString(),
            ],
            'dictionaries' => $this->getAllDictionaries(),
            'documentTypes' => DocumentVariableService::supportedDocumentTypes(),
            'fontOptions' => $this->getFontOptions(),
            'defaultLayoutVector' => $this->layoutService->defaultLayoutVector(),
        ]);
    }

    public function update(UpdateDocumentTemplateRequest $request, string $subdomain, int $id)
    {
        $template = DocumentTemplate::query()->findOrFail($id);
        $validated = $request->validated();

        $template->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'document_type' => $validated['document_type'],
            'layout_vector' => $this->layoutService->normalize($validated['layout_vector']),
        ]);

        if ($request->boolean('exit_after_save')) {
            return redirect()
                ->route('admin.documents.index', [
                    'subdomain' => $subdomain,
                ])
                ->with('success', 'Template saved and closed.');
        }

        return back()->with('success', 'Template synced successfully.');
    }

    public function destroy(string $subdomain, int $id)
    {
        DocumentTemplate::query()->findOrFail($id)->delete();

        return back()->with('success', 'Template deleted successfully.');
    }

    public function preview(string $subdomain, int $id)
    {
        $template = DocumentTemplate::query()->findOrFail($id);

        $normalizedTemplate = new DocumentTemplate([
            'name' => $template->name,
            'code' => $template->code,
            'document_type' => $template->document_type,
            'layout_vector' => $this->layoutService->normalize($template->layout_vector),
        ]);

        $payload = $this->renderContextFactory->makePreview(
            $template->document_type,
            $this->previewPayloadFactory->make($template->document_type)
        );
        $payload = $this->alignPreviewBranding($payload);
        $html = $this->compiler->compileToHtml($normalizedTemplate, $payload);

        $page = $normalizedTemplate->layout_vector['page'] ?? [];
        $size = strtolower((string) ($page['size'] ?? 'a4'));
        $orientation = (string) ($page['orientation'] ?? 'portrait');

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper($size, $orientation);

        return $pdf->stream($template->code.'_preview.pdf', ['Attachment' => false]);
    }

    public function previewHtml(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'in:'.implode(',', DocumentVariableService::supportedDocumentTypes())],
            'layout_vector' => ['required', 'array'],
        ]);

        $template = new DocumentTemplate([
            'name' => $validated['name'] ?? 'Draft Template',
            'code' => $validated['code'] ?? 'draft_template',
            'document_type' => $validated['document_type'],
            'layout_vector' => $this->layoutService->normalize($validated['layout_vector']),
        ]);

        $payload = $this->renderContextFactory->makePreview(
            $template->document_type,
            $this->previewPayloadFactory->make($template->document_type)
        );
        $payload = $this->alignPreviewBranding($payload);

        return response(
            $this->prepareScreenPreviewHtml(
                $this->compiler->compileToHtml($template, $payload, [
                    'asset_mode' => 'browser',
                    'render_mode' => 'browser',
                ]),
                $template->layout_vector['page'] ?? [],
                $request
            ),
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    private function getAllDictionaries(): array
    {
        return collect(DocumentVariableService::supportedDocumentTypes())
            ->mapWithKeys(fn (string $type) => [
                $type => DocumentVariableService::getDictionary($type),
            ])
            ->all();
    }

    private function getFontOptions(): array
    {
        return array_map(static fn (array $preset) => [
            'value' => (string) ($preset['value'] ?? ''),
            'label' => (string) ($preset['label'] ?? ''),
            'css_family' => (string) ($preset['css_family'] ?? ''),
        ], config('documents.font_presets', []));
    }

    private function prepareScreenPreviewHtml(string $html, array $page, ?Request $request = null): string
    {
        $sheet = $this->resolveScreenSheetDimensions(
            (string) ($page['size'] ?? 'A4'),
            (string) ($page['orientation'] ?? 'portrait')
        );
        $margins = (string) ($page['margins'] ?? '10mm');
        $background = (string) ($page['backgroundColor'] ?? '#ffffff');
        $baseHref = $request ? rtrim($request->getSchemeAndHttpHost(), '/').'/' : '/';

        $screenStyles = <<<CSS
            @media screen {
                html, body {
                    min-height: 100%;
                }

                body {
                    background: #1e293b;
                    padding: 24px;
                    box-sizing: border-box;
                }

                .screen-sheet {
                    width: {$sheet['width']};
                    min-height: {$sheet['height']};
                    margin: 0 auto;
                    background: {$background};
                    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
                    box-sizing: border-box;
                    padding: {$margins};
                }

                header,
                footer,
                main {
                    position: static !important;
                    width: 100%;
                }
            }
        CSS;

        $html = str_replace('<head>', '<head><base href="'.$baseHref.'">', $html);
        $html = str_replace('</style>', $screenStyles.'</style>', $html);
        $html = str_replace('<body>', '<body><div class="screen-sheet">', $html);

        return str_replace('</body>', '</div></body>', $html);
    }

    private function resolveScreenSheetDimensions(string $size, string $orientation): array
    {
        $presets = [
            'A5' => ['width' => '148mm', 'height' => '210mm'],
            'A4' => ['width' => '210mm', 'height' => '297mm'],
            'Letter' => ['width' => '215.9mm', 'height' => '279.4mm'],
            'Legal' => ['width' => '215.9mm', 'height' => '355.6mm'],
        ];

        $preset = $presets[$size] ?? $presets['A4'];

        if (strtolower($orientation) === 'landscape') {
            return [
                'width' => $preset['height'],
                'height' => $preset['width'],
            ];
        }

        return $preset;
    }

    private function alignPreviewBranding(array $payload): array
    {
        $currentCompany = view()->shared('currentCompany');

        if ($currentCompany instanceof Company) {
            $payload['company'] = array_merge($payload['company'] ?? [], [
                'name' => $currentCompany->name ?: ($payload['company']['name'] ?? 'Company'),
                'logo_url' => $this->normalizePublicAssetUrl($currentCompany->logo_path) ?: ($payload['company']['logo_url'] ?? ''),
            ]);
        }

        return $payload;
    }

    private function normalizePublicAssetUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        return str_starts_with($path, '/storage/')
            ? $path
            : '/storage/'.ltrim(str_replace('storage/', '', $path), '/');
    }
}
