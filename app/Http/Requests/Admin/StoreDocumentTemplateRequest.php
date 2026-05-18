<?php

namespace App\Http\Requests\Admin;

use App\Services\DocumentTemplateLayoutService;
use App\Services\DocumentVariableService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDocumentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:document_templates,code'],
            'document_type' => ['required', 'string', Rule::in(DocumentVariableService::supportedDocumentTypes())],
            'layout_vector' => ['required', 'array'],
            'exit_after_save' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $code = trim((string) $this->input('code', ''));

        if ($code === '' && $name !== '') {
            $code = $this->generateCodeFromName($name);
        }

        $this->merge([
            'name' => $name,
            'code' => $code,
            'exit_after_save' => $this->boolean('exit_after_save'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $layout = $this->input('layout_vector');

            if (! is_array($layout)) {
                return;
            }

            $service = app(DocumentTemplateLayoutService::class);
            $errors = $service->validate($layout);

            foreach ($errors as $error) {
                $validator->errors()->add('layout_vector', $error);
            }
        });
    }

    private function generateCodeFromName(string $name): string
    {
        $normalized = Str::of($name)
            ->lower()
            ->replaceMatches('/[^a-z0-9_-]+/', '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->value();

        return $normalized;
    }
}
