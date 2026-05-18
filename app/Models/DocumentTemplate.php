<?php

namespace App\Models;

use App\Services\DocumentTemplateLayoutService;
use App\Services\DocumentTemplateBindingIndexService;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'document_type',
        'layout_vector',
        'binding_index',
    ];

    protected $casts = [
        'layout_vector' => 'array',
        'binding_index' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (DocumentTemplate $template) {
            $template->layout_vector = app(DocumentTemplateLayoutService::class)
                ->normalize($template->layout_vector);

            $template->binding_index = app(DocumentTemplateBindingIndexService::class)
                ->extract($template->layout_vector);
        });
    }
}
