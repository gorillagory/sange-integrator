<?php

namespace App\Console\Commands;

use App\Models\DocumentTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportDocumentTemplateRow extends Command
{
    protected $signature = 'tenant:import-document-template-row
                            {file : Absolute or relative path to the exported row text file}
                            {--update : Update existing template if code already exists}';

    protected $description = 'Import a single document template from a CSV-style exported row text file';

    public function handle(): int
    {
        $file = $this->argument('file');
        $path = $this->resolvePath($file);

        if (! is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $raw = trim((string) file_get_contents($path));

        if ($raw === '') {
            $this->error('The provided file is empty.');
            return self::FAILURE;
        }

        $columns = str_getcsv($raw, ',', '"', '');

        if (count($columns) < 5) {
            $this->error('Invalid row format. Expected at least: id,name,code,document_type,layout_vector');
            return self::FAILURE;
        }

        $name = trim((string) ($columns[1] ?? ''));
        $code = trim((string) ($columns[2] ?? ''));
        $documentType = trim((string) ($columns[3] ?? 'invoice'));
        $layoutVectorRaw = (string) ($columns[4] ?? '');

        if ($name === '') {
            $this->error('Template name is missing.');
            return self::FAILURE;
        }

        if ($code === '') {
            $this->error('Template code is missing.');
            return self::FAILURE;
        }

        if ($layoutVectorRaw === '') {
            $this->error('Layout vector payload is missing.');
            return self::FAILURE;
        }

        $layoutVector = json_decode($layoutVectorRaw, true);

        if (! is_array($layoutVector)) {
            $this->error('Layout vector is not valid JSON: '.json_last_error_msg());
            return self::FAILURE;
        }

        $existing = DocumentTemplate::query()->where('code', $code)->first();

        if ($existing && ! $this->option('update')) {
            $this->warn("Template with code [{$code}] already exists. Re-run with --update to overwrite.");
            return self::INVALID;
        }

        $payload = [
            'name' => $name,
            'code' => $code,
            'document_type' => $documentType,
            'layout_vector' => $layoutVector,
        ];

        if ($existing) {
            $existing->update($payload);
            $this->info("Template [{$code}] updated successfully.");
            return self::SUCCESS;
        }

        DocumentTemplate::create($payload);

        $this->info("Template [{$code}] imported successfully.");
        return self::SUCCESS;
    }

    private function resolvePath(string $file): string
    {
        if (Str::startsWith($file, ['/','\\']) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $file)) {
            return $file;
        }

        return base_path($file);
    }
}
