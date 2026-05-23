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
        document_types: ['invoice', 'quote', 'receipt', 'itinerary', 'letter', 'memo', 'reply'],
        canonical_roots: ['service_record', 'service_rows', 'schema_vectors', 'finance', 'client', 'company', 'main_group', 'author', 'user', 'assigned_user', 'document_links', 'remarks'],
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
            name: pageProps?.brand?.tenant?.main_group?.name ?? 'Bayam Group',
            logo_url: resolveMainGroupLogoUrl(pageProps),
            address: 'Wisma Bayam, Kota Bharu, Kelantan',
        },
        client: {
            name: 'Acme Corporation Sdn Bhd',
            email: 'billing@acme.test',
            address: 'Suite 101, Innovation Tower, Cyberjaya, Selangor',
            profile: 'Regional energy client with controlled approvals, traveler identifiers, and billing-code governance.',
            remarks: 'Patient EID: EID-77821\nTravel Code: AZFA\nBill under Mubadala operations allocation.',
        },
        author: {
            id: 7,
            name: 'Muhammad Faizal Abdul',
            email: 'faizal@bayam.test',
        },
        user: {
            id: 7,
            name: 'Muhammad Faizal Abdul',
            email: 'faizal@bayam.test',
        },
        assigned_user: {
            id: 12,
            name: 'Nur Amalina Rahim',
            email: 'amalina@bayam.test',
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
            remarks: 'Patient EID: EID-77821\nTravel Code: AZFA\nBill under Mubadala operations allocation.',
            author_name: 'Muhammad Faizal Abdul',
            author_email: 'faizal@bayam.test',
            assigned_user_name: 'Nur Amalina Rahim',
            assigned_user_email: 'amalina@bayam.test',
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
        remarks: 'Patient EID: EID-77821\nTravel Code: AZFA\nBill under Mubadala operations allocation.',
        document_links: {
            reference_value: 'DOC-2026-001',
            reference_label: 'Document Reference',
            reference_qr_data_uri: makeReferenceQrPlaceholder('DOC-2026-001'),
        },
        letter: {
            reference_no: 'LTR-2026-014',
            date: '19 May 2026',
            recipient_name: 'Ms. Farah Azlan',
            recipient_title: 'Corporate Travel Lead',
            recipient_company: 'Acme Corporation Sdn Bhd',
            recipient_address: 'Suite 101, Innovation Tower\nCyberjaya, Selangor',
            subject: 'Travel Arrangement Confirmation',
            salutation: 'Dear Ms. Farah,',
            body: 'We are pleased to confirm the travel arrangements for the requested movement.\n\nAll services have been secured under the approved travel code and are attached for your reference.',
            closing: 'Yours faithfully,',
            signature_name: 'Bayam Travel Operations',
            signature_title: 'Corporate Travel Desk',
        },
        memo: {
            reference_no: 'MEMO-2026-009',
            date: '19 May 2026',
            to: 'All Travel Operations Staff',
            from: 'Agency Admin Office',
            subject: 'Updated Service Record Capture Standard',
            body: 'Please ensure every service record includes the client-specific remarks snapshot before locking the document.',
            footer_note: 'This memo is effective immediately.',
        },
        reply: {
            reference_no: 'RPL-2026-003',
            date: '19 May 2026',
            to: 'Procurement Unit',
            attention: 'Mr. Hafiz Rahman',
            subject: 'Reply to Document Clarification Request',
            opening: 'We refer to your clarification request regarding the submitted invoice package.',
            body: 'The attached service record and invoice reflect the final approved scope, including traveler identifiers and the validated travel code.',
            closing: 'Thank you.',
            signature_name: 'Document Control Desk',
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
        return makeLogoPlaceholder('Bayam Travel', '#ffffff', '#e11d48');
    }

    if (String(path).startsWith('http://') || String(path).startsWith('https://')) {
        return path;
    }

    return String(path).startsWith('/storage/')
        ? path
        : `/storage/${String(path).replace(/^storage\//, '')}`;
}

export function resolveMainGroupLogoUrl(pageProps) {
    const path = pageProps?.brand?.tenant?.main_group?.logo_url;

    if (!path) {
        return makeLogoPlaceholder('Bayam Group', '#ffffff', '#0f172a');
    }

    return path;
}

function makeLogoPlaceholder(text, background, foreground) {
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 120"><rect width="240" height="120" rx="18" fill="${background}"/><text x="120" y="66" text-anchor="middle" font-family="Helvetica, Arial, sans-serif" font-size="24" font-weight="700" fill="${foreground}">${escapeXml(text)}</text></svg>`;

    return `data:image/svg+xml;base64,${btoa(svg)}`;
}

function makeReferenceQrPlaceholder(referenceValue) {
    const safeValue = escapeXml(referenceValue);
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 160"><rect width="160" height="160" fill="#ffffff"/><rect x="12" y="12" width="42" height="42" fill="#0f172a"/><rect x="22" y="22" width="22" height="22" fill="#ffffff"/><rect x="106" y="12" width="42" height="42" fill="#0f172a"/><rect x="116" y="22" width="22" height="22" fill="#ffffff"/><rect x="12" y="106" width="42" height="42" fill="#0f172a"/><rect x="22" y="116" width="22" height="22" fill="#ffffff"/><rect x="72" y="18" width="12" height="12" fill="#0f172a"/><rect x="90" y="18" width="12" height="12" fill="#0f172a"/><rect x="72" y="36" width="30" height="12" fill="#0f172a"/><rect x="66" y="66" width="18" height="18" fill="#0f172a"/><rect x="90" y="66" width="12" height="12" fill="#0f172a"/><rect x="108" y="72" width="18" height="18" fill="#0f172a"/><rect x="66" y="96" width="12" height="12" fill="#0f172a"/><rect x="84" y="96" width="30" height="12" fill="#0f172a"/><rect x="72" y="114" width="12" height="12" fill="#0f172a"/><rect x="96" y="114" width="12" height="12" fill="#0f172a"/><text x="80" y="154" text-anchor="middle" font-family="Helvetica, Arial, sans-serif" font-size="10" font-weight="700" fill="#0f172a">${safeValue}</text></svg>`;

    return `data:image/svg+xml;base64,${btoa(svg)}`;
}

function escapeXml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}
