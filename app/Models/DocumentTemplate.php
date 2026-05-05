<?php

namespace App\Models;

use App\Services\DocumentTemplateLayoutService;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'document_type',
        'layout_vector',
    ];

    protected $casts = [
        'layout_vector' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (DocumentTemplate $template) {
            $template->layout_vector = app(DocumentTemplateLayoutService::class)
                ->normalize($template->layout_vector);
        });
    }
}
