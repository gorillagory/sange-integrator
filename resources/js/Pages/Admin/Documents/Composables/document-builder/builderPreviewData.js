// resources/js/Pages/Admin/Documents/Composables/document-builder/builderPreviewData.js
export function makeBuilderPreviewPayload({ pageProps, documentType }) {
    return {
        company: {
            name: pageProps?.currentCompany?.name ?? 'Bayam Travel Sdn Bhd',
            logo_url: resolveCompanyLogoUrl(pageProps),
            email: 'hello@bayam.test',
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
            tax_total: 'RM 0.00',
            grand_total: 'RM 3,850.00',
            line_items: [
                { description: 'Roundtrip Flight (KUL - NRT)', quantity: 1, unit_price: 'RM 2,500.00', total: 'RM 2,500.00' },
                { description: 'Hotel Accommodation (3 Nights)', quantity: 1, unit_price: 'RM 1,200.00', total: 'RM 1,200.00' },
                { description: 'Private Airport Transfer', quantity: 1, unit_price: 'RM 150.00', total: 'RM 150.00' },
            ],
        },
        quote: {
            number: 'QT-2026-018',
            valid_until: '20 May 2026',
            grand_total: 'RM 5,250.00',
            line_items: [
                { description: 'Family Package - Tokyo', quantity: 4, unit_price: 'RM 1,312.50', total: 'RM 5,250.00' },
            ],
        },
        receipt: {
            number: 'REC-2026-031',
            payment_date: '02 May 2026',
            amount_paid: 'RM 3,850.00',
            payment_method: 'Bank Transfer',
            reference_id: 'BTX-2026-99118',
        },
        booking: {
            reference: 'BKG-88992',
            start_date: '10 Dec 2026',
            end_date: '24 Dec 2026',
            pax_count: 4,
            passengers: [
                { passenger: { full_name: 'Adam Iskandar', passport: 'A12345678', type: 'Adult' } },
                { passenger: { full_name: 'Nur Alya', passport: 'A87654321', type: 'Adult' } },
            ],
            services: [
                { service: { date: '10 Dec 2026', time: '09:00', title: 'Malaysia Airlines MH070', details: 'Depart KUL to NRT', confirmation: 'PNR-7788' } },
                { service: { date: '11 Dec 2026', time: '15:00', title: 'Tokyo City Tour', details: 'Private guided city tour', confirmation: 'TOUR-991' } },
            ],
        },
        meta: {
            active_document_type: documentType ?? 'invoice',
        },
    };
}

export function resolveCompanyLogoUrl(pageProps) {
    const path = pageProps?.currentCompany?.logo_path;

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
