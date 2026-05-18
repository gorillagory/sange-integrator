// resources/js/Pages/Admin/Documents/Composables/document-builder/builderPreviewData.js
export function makeBuilderPreviewPayload({ pageProps, documentType }) {
    const baseDomain = pageProps?.brand?.base_domain || 'bayam.test';
    const handler = {
        service_group_key: 'travel.services',
        handler_key: 'travel.services',
        name: 'Travel Services',
        industry: 'travel',
        status: 'active',
        runtime_capabilities: ['service_records.capture', 'documents.invoice', 'analytics.extraction'],
        document_types: ['invoice', 'quote', 'receipt', 'itinerary'],
        canonical_roots: ['service_record', 'service_rows', 'schema_vectors', 'finance', 'client', 'company'],
    };

    const services = [
        {
            schema_vector: { id: 1, service_code: 'flight', service_type: 'flight', service_name: 'Flight', version: 3 },
            service: { date: '10 Dec 2026', time: '09:00', title: 'Malaysia Airlines MH070', details: 'Depart KUL to NRT', confirmation: 'PNR-7788' },
            fields: { flight_no: 'MH070', route: 'KUL - NRT' },
            details: { flight_no: 'MH070', route: 'KUL - NRT' },
            details_extra: {},
            finance: { qty: 1, unit_name: 'ticket', base_cost: 'RM 2,500.00', supplier_cost: 'RM 2,500.00', discount_amount: 'RM 0.00', tax_amount: 'RM 0.00', sell_price: 'RM 2,500.00', line_total: 'RM 2,500.00' },
            pricing: { qty: 1, unit_fare: 'RM 2,500.00', tax_amount: 'RM 0.00', line_total: 'RM 2,500.00' },
            snapshot: { captured_at: '2026-05-15T10:00:00+08:00' },
        },
        {
            schema_vector: { id: 2, service_code: 'tour', service_type: 'tour', service_name: 'Tour', version: 1 },
            service: { date: '11 Dec 2026', time: '15:00', title: 'Tokyo City Tour', details: 'Private guided city tour', confirmation: 'TOUR-991' },
            fields: { tour_type: 'Private', duration: 'Half Day' },
            details: { tour_type: 'Private', duration: 'Half Day' },
            details_extra: {},
            finance: { qty: 1, unit_name: 'package', base_cost: 'RM 1,350.00', supplier_cost: 'RM 1,350.00', discount_amount: 'RM 0.00', tax_amount: 'RM 0.00', sell_price: 'RM 1,350.00', line_total: 'RM 1,350.00' },
            pricing: { qty: 1, unit_fare: 'RM 1,350.00', tax_amount: 'RM 0.00', line_total: 'RM 1,350.00' },
            snapshot: { captured_at: '2026-05-15T10:30:00+08:00' },
        },
    ];

    const payload = {
        handler,
        company: {
            name: pageProps?.currentCompany?.name ?? 'Bayam Travel Sdn Bhd',
            logo_url: resolveCompanyLogoUrl(pageProps),
            email: `hello@${baseDomain}`,
            phone: '+60 12-345 6789',
            address: 'Level 12, Bayam Tower, Kuala Lumpur, Malaysia',
        },
        main_group: {
            name: 'Bayam Group',
            logo_url: 'https://dummyimage.com/240x120/e2e8f0/0f172a.png&text=Bayam+Group',
            address: 'Wisma Bayam, Kota Bharu, Kelantan',
        },
        client: {
            name: 'Acme Corporation Sdn Bhd',
            email: 'billing@acme.test',
            address: 'Suite 101, Innovation Tower, Cyberjaya, Selangor',
        },
        invoice: {
            number: 'INV-2026-001',
            issue_date: '01 May 2026',
            due_date: '15 May 2026',
            subtotal: 'RM 3,850.00',
            tax_total: 'RM 231.00',
            grand_total: 'RM 4,081.00',
            line_items: [
                { description: 'Roundtrip Flight (KUL - NRT)', unit: 'ticket', quantity: 1, unit_price: 'RM 2,500.00', total: 'RM 2,500.00' },
                { description: 'Hotel Accommodation (3 Nights)', unit: 'night', quantity: 3, unit_price: 'RM 400.00', total: 'RM 1,200.00' },
                { description: 'Private Airport Transfer', unit: 'trip', quantity: 1, unit_price: 'RM 150.00', total: 'RM 150.00' },
            ],
        },
        quote: {
            number: 'QT-2026-018',
            valid_until: '20 May 2026',
            subtotal: 'RM 5,250.00',
            tax_total: 'RM 315.00',
            grand_total: 'RM 5,565.00',
            line_items: [
                { description: 'Family Package - Tokyo', unit: 'pax', quantity: 4, unit_price: 'RM 1,200.00', total: 'RM 4,800.00' },
                { description: 'Airport Meet And Greet', unit: 'service', quantity: 1, unit_price: 'RM 450.00', total: 'RM 450.00' },
            ],
        },
        receipt: {
            number: 'REC-2026-031',
            payment_date: '02 May 2026',
            amount_paid: 'RM 3,850.00',
            payment_method: 'Bank Transfer',
            reference_id: 'BTX-2026-99118',
        },
        finance: {
            subtotal: 3850,
            tax_total: 231,
            grand_total: 4081,
            formatted_subtotal: 'RM 3,850.00',
            formatted_tax_total: 'RM 231.00',
            formatted_grand_total: 'RM 4,081.00',
        },
        service_record: {
            id: 1,
            service_group_key: handler.service_group_key,
            handler_key: handler.handler_key,
            reference: 'SRV-88992',
            reference_no: 'SRV-88992',
            document_no: 'DOC-2026-001',
            status: 'Draft',
            captured_at: '2026-05-15T10:00:00+08:00',
            start_date: '10 Dec 2026',
            end_date: '24 Dec 2026',
        },
        operation: {
            id: 1,
            handler_key: handler.handler_key,
            reference: 'SRV-88992',
            reference_no: 'SRV-88992',
            document_no: 'DOC-2026-001',
            status: 'Draft',
            captured_at: '2026-05-15T10:00:00+08:00',
            start_date: '10 Dec 2026',
            end_date: '24 Dec 2026',
        },
        service_rows: services,
        schema_vectors: services.map((service) => service.schema_vector),
        services,
        service_instances: services,
        meta: {
            active_document_type: documentType ?? 'invoice',
        },
    };

    return payload;
}

export function resolveCompanyLogoUrl(pageProps) {
    const path = pageProps?.currentCompany?.logo_url || pageProps?.currentCompany?.logo_path;

    if (!path) {
        return 'https://dummyimage.com/240x120/ffffff/e11d48.png&text=Bayam+Travel';
    }

    if (String(path).startsWith('http://') || String(path).startsWith('https://')) {
        return path;
    }

    return String(path).startsWith('/storage/')
        ? path
        : `/storage/${String(path).replace(/^storage\//, '')}`;
}
