<?php

return [
    'default' => 'travel.services',

    'handlers' => [
        'travel.services' => [
            'name' => 'Travel Services',
            'industry' => 'travel',
            'status' => 'active',
            'runtime_capabilities' => [
                'service_records.capture',
                'documents.invoice',
                'analytics.extraction',
                'analytics.materialization',
            ],
            'schema_policy' => [
                'governed_payload_root' => 'service_details',
                'extension_payload_root' => 'service_details_extra',
                'schema_scope' => [
                    'industry' => 'travel',
                ],
            ],
            'document_policy' => [
                'document_types' => ['invoice', 'quote', 'receipt', 'itinerary'],
                'canonical_roots' => ['service_record', 'service_rows', 'schema_vectors', 'finance', 'client', 'company'],
            ],
            'extraction_policy' => [
                'extractor' => App\Services\ServiceRecords\TravelServiceRecordExtractor::class,
            ],
        ],
    ],
];
