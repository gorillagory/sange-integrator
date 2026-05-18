<?php

namespace App\Services\Handlers;

use App\Models\Company;
use RuntimeException;

class HandlerRegistry
{
    /**
     * @var array<string, array>
     */
    private readonly array $handlers;

    public function __construct(
        ?array $handlers = null,
        private readonly ?string $defaultKey = null,
    )
    {
        $this->handlers = $handlers ?? (array) config('handlers.handlers', []);
    }

    public function keys(): array
    {
        return array_keys($this->handlers);
    }

    public function default(): HandlerDefinition
    {
        $defaultKey = (string) ($this->defaultKey ?? config('handlers.default', ''));

        if ($defaultKey !== '' && isset($this->handlers[$defaultKey])) {
            return $this->make($defaultKey, $this->handlers[$defaultKey]);
        }

        $firstKey = array_key_first($this->handlers);
        if ($firstKey === null) {
            throw new RuntimeException('No operation handlers are configured.');
        }

        return $this->make($firstKey, $this->handlers[$firstKey]);
    }

    public function forKey(?string $handlerKey): HandlerDefinition
    {
        $key = trim((string) $handlerKey);

        if ($key !== '' && isset($this->handlers[$key])) {
            return $this->make($key, $this->handlers[$key]);
        }

        return $this->default();
    }

    public function forCompany(Company $company): HandlerDefinition
    {
        foreach ($this->handlers as $key => $definition) {
            if (($definition['industry'] ?? null) === $company->industry) {
                return $this->make($key, $definition);
            }
        }

        return $this->default();
    }

    public function resolve(?string $handlerKey, ?Company $company = null): HandlerDefinition
    {
        $key = trim((string) $handlerKey);

        if ($key !== '') {
            return $this->forKey($key);
        }

        if ($company) {
            return $this->forCompany($company);
        }

        return $this->default();
    }

    private function make(string $key, array $definition): HandlerDefinition
    {
        return new HandlerDefinition($key, $definition);
    }
}
