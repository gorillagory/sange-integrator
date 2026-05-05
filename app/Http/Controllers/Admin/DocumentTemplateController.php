<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentTemplateRequest;
use App\Http\Requests\Admin\UpdateDocumentTemplateRequest;
use App\Models\DocumentTemplate;
use App\Services\DocumentPreviewPayloadFactory;
use App\Services\DocumentTemplateLayoutService;
use App\Services\DocumentVariableService;
use App\Services\PdfCompilerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Inertia\Response;

class DocumentTemplateController extends Controller
{
    public function __construct(
        private readonly DocumentTemplateLayoutService $layoutService,
        private readonly DocumentPreviewPayloadFactory $previewPayloadFactory,
        private readonly PdfCompilerService $compiler,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Documents/Index', [
            'templates' => DocumentTemplate::query()
                ->orderBy('name')
                ->get()
                ->map(fn (DocumentTemplate $template) => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'code' => $template->code,
                    'document_type' => $template->document_type,
                    'layout_vector' => $template->layout_vector,
                    'created_at' => optional($template->created_at)?->toDateTimeString(),
                    'updated_at' => optional($template->updated_at)?->toDateTimeString(),
                ])
                ->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Documents/Builder', [
            'template' => null,
            'dictionaries' => $this->getAllDictionaries(),
            'documentTypes' => DocumentVariableService::supportedDocumentTypes(),
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

        $payload = $this->previewPayloadFactory->make($template->document_type);
        $html = $this->compiler->compileToHtml($normalizedTemplate, $payload);

        $page = $normalizedTemplate->layout_vector['page'] ?? [];
        $size = strtolower((string) ($page['size'] ?? 'a4'));
        $orientation = (string) ($page['orientation'] ?? 'portrait');

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper($size, $orientation);

        return $pdf->stream($template->code.'_preview.pdf', ['Attachment' => false]);
    }

    private function getAllDictionaries(): array
    {
        return collect(DocumentVariableService::supportedDocumentTypes())
            ->mapWithKeys(fn (string $type) => [
                $type => DocumentVariableService::getDictionary($type),
            ])
            ->all();
    }
}
