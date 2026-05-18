<?php

namespace App\Services\Handlers;

class HandlerDefinition
{
    public function __construct(
        private readonly string $key,
        private readonly array $definition,
    ) {
    }

    public function key(): string
    {
        return $this->key;
    }

    public function name(): string
    {
        return (string) ($this->definition['name'] ?? $this->key);
    }

    public function industry(): ?string
    {
        $industry = $this->definition['industry'] ?? null;

        return is_string($industry) && $industry !== '' ? $industry : null;
    }

    public function status(): string
    {
        return (string) ($this->definition['status'] ?? 'draft');
    }

    public function extractorClass(): ?string
    {
        $extractor = $this->definition['extraction_policy']['extractor'] ?? null;

        return is_string($extractor) && $extractor !== '' ? $extractor : null;
    }

    public function documentTypes(): array
    {
        return array_values(array_filter(array_map(
            'strval',
            (array) ($this->definition['document_policy']['document_types'] ?? [])
        )));
    }

    public function canonicalRoots(): array
    {
        return array_values(array_filter(array_map(
            'strval',
            (array) ($this->definition['document_policy']['canonical_roots'] ?? [])
        )));
    }

    public function runtimeCapabilities(): array
    {
        return array_values(array_filter(array_map(
            'strval',
            (array) ($this->definition['runtime_capabilities'] ?? [])
        )));
    }

    public function toArray(): array
    {
        return [
            'service_group_key' => $this->key(),
            'handler_key' => $this->key(),
            'name' => $this->name(),
            'industry' => $this->industry(),
            'status' => $this->status(),
            'runtime_capabilities' => $this->runtimeCapabilities(),
            'document_types' => $this->documentTypes(),
            'canonical_roots' => $this->canonicalRoots(),
        ];
    }
}
