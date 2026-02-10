<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title></title>
    <style>
        @page { margin: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
        }
        .page {
            width: 105mm;
            height: 148mm;
            display: table;
        }
        .page-cell {
            display: table-cell;
            vertical-align: middle;
            padding: 10mm 10mm 12mm 10mm;
        }
        .ticket {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
        }
        .title {
            text-align: center;
            font-weight: 800;
            font-size: 16px;
            margin: 6px 0 8px 0;
            letter-spacing: 0.4px;
        }
        .subtitle {
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .meta { line-height: 1.35; }
        .line { border-top: 2px solid #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        th {
            text-align: left;
            font-weight: 700;
            padding: 6px 0;
            border-bottom: 1px solid #000;
        }
        td {
            padding: 6px 0;
            border-bottom: 1px dashed #bbb;
            vertical-align: top;
        }
        tr:last-child td { border-bottom: none; }
        .qty { width: 36px; }
        .desc { padding-right: 6px; word-break: break-word; }
        .price, .total { width: 72px; text-align: right; white-space: nowrap; }
        .row { display: table; width: 100%; line-height: 1.4; }
        .row > div { display: table-cell; }
        .row .right { text-align: right; white-space: nowrap; }
        .grand { font-weight: 800; font-size: 13px; margin-top: 6px; }
        .small { font-size: 10px; color: #222; }
        .notes { margin-top: 8px; font-size: 10px; color: #333; line-height: 1.35; }
        .thanks { margin-top: 12px; text-align: center; font-weight: 700; }
    </style>
</head>
<body>
@php
    $subtotal = $invoice->items->sum('subtotal');
    $fmt = fn($n) => '$' . number_format((float) $n, 2);

    $company = config('receipt.company');
    $dian = config('receipt.dian');
@endphp

<div class="page">
    <div class="page-cell">
        <div class="ticket">
            <div class="title">FACTURA (DIAN)</div>
            <div class="subtitle">{{ $company['name'] ?? 'Empresa' }}</div>

            <div class="meta small" style="text-align:center;">
                <div><strong>NIT:</strong> {{ $company['nit'] ?? 'NIT' }}</div>
                @if(!empty($company['owner']))<div><strong>Titular:</strong> {{ $company['owner'] }}</div>@endif
                @if(!empty($company['address']))<div>{{ $company['address'] }}</div>@endif
                @if(!empty($company['city']))<div>{{ $company['city'] }}</div>@endif
                @if(!empty($company['phone']))<div><strong>Tel:</strong> {{ $company['phone'] }}</div>@endif
                @if(!empty($dian['email']))<div><strong>Email:</strong> {{ $dian['email'] }}</div>@endif
                @if(!empty($dian['regime']))<div><strong>Régimen:</strong> {{ $dian['regime'] }}</div>@endif
                @if(!empty($dian['activity']))<div><strong>Actividad:</strong> {{ $dian['activity'] }}</div>@endif
                @if(!empty($dian['resolution']))<div><strong>Resolución:</strong> {{ $dian['resolution'] }}</div>@endif
            </div>

            <div class="line"></div>

            <div class="meta">
                <div><strong>Factura:</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>Fecha:</strong> {{ optional($invoice->invoice_date)->format('d/m/Y h:i A') }}</div>
                <div><strong>Cliente:</strong> CONSUMIDOR FINAL</div>
            </div>

            <div class="line"></div>

            <table>
                <thead>
                    <tr>
                        <th class="qty">Cant.</th>
                        <th class="desc">Descripción</th>
                        <th class="price">Precio</th>
                        <th class="total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="qty">{{ (int) $item->quantity }}</td>
                            <td class="desc">{{ $item->product->name ?? '—' }}</td>
                            <td class="price">{{ $fmt($item->price) }}</td>
                            <td class="total">{{ $fmt($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="line"></div>

            <div class="meta">
                <div class="row"><div>Subtotal:</div><div class="right">{{ $fmt($subtotal) }}</div></div>
                <div class="row grand"><div>Total a Pagar:</div><div class="right">{{ $fmt($invoice->total) }}</div></div>
                <div class="row"><div>IVA:</div><div class="right">{{ $fmt(0) }}</div></div>
            </div>

            @if(!empty(trim((string) $invoice->notes)))
                <div class="notes"><strong>Notas:</strong> {{ $invoice->notes }}</div>
            @endif

            <div class="thanks">Documento generado para control interno.</div>
        </div>
    </div>
</div>
</body>
</html>
