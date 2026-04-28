<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $booking->invoice_no }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header-table { w-full: 100%; margin-bottom: 20px; }
        .header-table td { vertical-align: top; }
        .company-name { font-size: 16px; font-weight: bold; }
        .invoice-title { font-size: 24px; font-weight: bold; text-align: center; text-decoration: underline; margin: 30px 0; }
        .contract-title { font-weight: bold; margin-bottom: 15px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 8px; vertical-align: top; }
        .data-table th { background-color: #f3f4f6; font-weight: bold; text-align: left; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }

        .footer-note { font-size: 10px; font-style: italic; margin-top: 10px; }
    </style>
</head>
<body>

<table class="header-table" width="100%">
    <tr>
        <td width="50%">
            <div style="font-size: 24px; font-weight: 900; color: #0f172a; margin-bottom: 10px;">Bayam <span style="font-weight: 300;">HOSPITALITY</span></div>
            <div class="company-name">BAYAM TRAVEL SDN BHD (1037662-X)</div>
            <div>www.bayamgroup.com</div>
        </td>
        <td width="50%" class="text-right" style="font-size: 11px;">
            <div class="bold">Headquarters</div>
            <div>5411-E Wisma Bayam, Jalan Kuala Krai,<br>15150 Kota Bharu, Kelantan.<br>Tel: 09-741 8626 | Fax: 09-741 8526</div>
            <br>
            <div class="bold">Main Office</div>
            <div>Unit B2-05 Megan Ambassy, 225 Jalan Ampang<br>50450 Kuala Lumpur<br>Tel: 03-2181 0030</div>
        </td>
    </tr>
</table>

<div class="invoice-title">INVOICE</div>

<div class="contract-title">
    CONTRACT FOR: {{ strtoupper($contract->title ?? 'GENERAL SERVICES') }} ({{ $booking->contract_no }})
</div>

<table class="data-table">
    <thead>
    <tr>
        <th width="45%">DESCRIPTION</th>
        <th width="15%" class="text-center">NO. OF NIGHT</th>
        <th width="10%" class="text-center">QTY</th>
        <th width="15%" class="text-right">PRICE</th>
        <th width="15%" class="text-right">TOTAL (RM)</th>
    </tr>
    </thead>
    <tbody>
    @foreach($booking->cart_payload ?? [] as $index => $item)
        @php
            $passenger = $booking->passenger_details[$index] ?? 'N/A';
            $qty = $item['qty'] ?? 1;
            $nights = $item['details']['nights'] ?? 1;
            $price = $item['price'] ?? 0;
            $lineTotal = $price * $qty * $nights;
        @endphp
        <tr>
            <td>
                <div class="bold">1. Accommodation</div>
                <div>{{ $passenger }}</div>
                <div style="margin-top: 5px;">
                    Hotel: {{ $item['service_name'] ?? 'Hotel Accommodation' }}<br>
                    @foreach($item['details'] ?? [] as $key => $val)
                        {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $val }}<br>
                    @endforeach
                    Room rate: RM {{ number_format($price, 2) }} Nett per room per night
                </div>
            </td>
            <td class="text-center">{{ $nights }}</td>
            <td class="text-center">{{ $qty }}</td>
            <td class="text-right">{{ number_format($price, 2) }}</td>
            <td class="text-right">{{ number_format($lineTotal, 2) }}</td>
        </tr>
    @endforeach

    <tr>
        <td><div class="bold">Service Fee</div></td>
        <td class="text-center">-</td>
        <td class="text-center">1</td>
        <td class="text-right">18.00</td>
        <td class="text-right">18.00</td>
    </tr>

    <tr>
        <td colspan="4" class="text-right bold">Total Amount:</td>
        <td class="text-right bold">{{ number_format(($booking->total_amount ?? 0) + 18, 2) }}</td>
    </tr>
    </tbody>
</table>

<div class="footer-note">**During the holy month of Ramadhan, breakfast will start at 4.30am</div>

</body>
</html>
