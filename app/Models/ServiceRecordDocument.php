<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRecordDocument extends Model
{
    protected $connection = 'tenant';

    protected $table = 'service_record_documents';

    protected $fillable = [
        'service_record_id',
        'company_id',
        'template_id',
        'document_type',
        'document_number',
        'template_name',
        'template_code',
        'status',
        'generated_by',
        'generated_at',
        'last_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'service_record_id' => 'integer',
            'company_id' => 'integer',
            'template_id' => 'integer',
            'generated_by' => 'integer',
            'generated_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
        ];
    }

    public function serviceRecord(): BelongsTo
    {
        return $this->belongsTo(ServiceRecord::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }
}
