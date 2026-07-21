<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 15pt;
        size: a4 portrait;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
    }

    .page {
        page-break-after: always;
        width: 100%;
        height: 100%;
    }

    .page:last-child {
        page-break-after: auto;
    }

    .grid-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
    }

    .grid-table td {
        width: 33.33%;
        height: 270pt;
        vertical-align: top;
        padding: 8pt;
        border: 1pt dashed #ccc;
    }

    .label-content {
        width: 100%;
        height: 100%;
        position: relative;
        text-align: center;
    }

    .company {
        font-size: 10pt;
        font-weight: bold;
        margin-bottom: 6pt;
        letter-spacing: 0.3pt;
        text-transform: uppercase;
    }

    .qr-box {
        margin: 0 auto 6pt auto;
        width: 120pt;
        height: 120pt;
    }

    .qr-box img {
        width: 120pt;
        height: 120pt;
    }

    .material-code {
        font-size: 11pt;
        font-weight: bold;
        margin-bottom: 3pt;
    }

    .material-name {
        font-size: 8.5pt;
        color: #333;
        margin-bottom: 4pt;
        max-height: 24pt;
        overflow: hidden;
    }

    .specs {
        font-size: 8.5pt;
        line-height: 1.3;
    }

    .lot-badge {
        display: inline-block;
        background: #f0f0f0;
        border: 1pt solid #ddd;
        padding: 2pt 5pt;
        border-radius: 3pt;
        font-weight: bold;
        margin-top: 3pt;
        font-size: 8pt;
    }
</style>
</head>
<body>
@foreach($pages as $pageItems)
<div class="page">
    <table class="grid-table">
        @foreach($pageItems->chunk(3) as $rowItems)
        <tr>
            @foreach($rowItems as $item)
            @php $r = $item['request']; @endphp
            <td>
                <div class="label-content">
                    <div class="company">{{ $company ?? 'ADASI STO' }}</div>
                    <div class="qr-box">
                        <img src="{{ $item['qrDataUri'] }}" alt="QR">
                    </div>
                    <div class="material-code">{{ $r->material_code }} ({{ $r->shape_code }})</div>
                    <div class="material-name">{{ $r->material_name }}</div>
                    <div class="specs">
                        <div><strong>Dim:</strong> {{ $r->size ?: '-' }}</div>
                        <div><strong>Lokasi:</strong> {{ $r->plant?->name ?? '-' }} / {{ $r->location?->name ?? '-' }}</div>
                    </div>
                    <div class="lot-badge">LOT: {{ $r->lot_number }} | QTY: {{ $r->qty }}</div>
                </div>
            </td>
            @endforeach
            @for($i = $rowItems->count(); $i < 3; $i++)
            <td></td>
            @endfor
        </tr>
        @endforeach
        @for($j = $pageItems->chunk(3)->count(); $j < 3; $j++)
        <tr>
            <td></td><td></td><td></td>
        </tr>
        @endfor
    </table>
</div>
@endforeach
</body>
</html>
