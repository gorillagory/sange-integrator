<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('document_templates')) {
            $this->command?->warn('Skipped DocumentTemplateSeeder: table [document_templates] does not exist on active connection.');
            return;
        }

        $path = database_path('seeders/data/document_templates/master_bt_1.txt');

        if (! is_file($path)) {
            $this->command?->warn("Skipped DocumentTemplateSeeder: file not found at [{$path}]");
            return;
        }

        $raw = trim((string) file_get_contents($path));

        if ($raw === '') {
            $this->command?->warn('Skipped DocumentTemplateSeeder: empty file.');
            return;
        }

        $columns = str_getcsv($raw, ',', '"', '');

        if (count($columns) < 5) {
            $this->command?->warn('Skipped DocumentTemplateSeeder: invalid row format.');
            return;
        }

        $name = trim((string) ($columns[1] ?? ''));
        $code = trim((string) ($columns[2] ?? ''));
        $documentType = trim((string) ($columns[3] ?? 'invoice'));
        $layoutVectorRaw = (string) ($columns[4] ?? '');

        if ($name === '' || $code === '' || $layoutVectorRaw === '') {
            $this->command?->warn('Skipped DocumentTemplateSeeder: missing required fields.');
            return;
        }

        $layoutVector = json_decode($layoutVectorRaw, true);

        if (! is_array($layoutVector)) {
            $this->command?->warn('Skipped DocumentTemplateSeeder: invalid JSON payload. '.json_last_error_msg());
            return;
        }

        DocumentTemplate::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'document_type' => $documentType,
                'layout_vector' => $layoutVector,
            ]
        );

        $this->command?->info("Seeded document template [{$code}]");
    }
}
