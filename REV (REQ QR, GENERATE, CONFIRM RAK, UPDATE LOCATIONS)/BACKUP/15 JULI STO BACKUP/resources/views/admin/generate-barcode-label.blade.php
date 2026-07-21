<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 0;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: Arial, Helvetica, sans-serif;
    }

    .label {
        width: 141.73pt;
        height: 56.69pt; /* 50mm × 20mm */
        position: relative;
        overflow: hidden;
        background: #fff;
    }

    .company {
        position: absolute;
        top: 3pt;
        left: 12pt;
        font-size: 6pt;
        font-weight: bold;
        color: #000;
        letter-spacing: 0.1pt;
    }

    .qr-code {
        position: absolute;
        top: 11pt;
        left: 12pt;
        width: 38pt;
        height: 38pt;
    }

    .barcode-text {
        position: absolute;
        top: 13pt;
        left: 54pt;
        font-size: 6.5pt;
        font-weight: bold;
        color: #000;
        letter-spacing: 0.2pt;
    }

    .lot-text {
        position: absolute;
        top: 24pt;
        left: 54pt;
        font-size: 6pt;
        font-weight: bold;
        color: #000;
        letter-spacing: 0.2pt;
    }

    .detail-text {
        position: absolute;
        top: 35pt;
        left: 54pt;
        width: 84pt;
        height: 18pt;
        font-size: 5.5pt;
        font-weight: bold;
        color: #000;
        line-height: 1.2;
        overflow: hidden;
    }
</style>
</head>
<body>
    @php
        // Construct the detail string: "HP4MA FLAT 45 X 30 X 405"
        $detail = trim(strtoupper($request->material_name . ' ' . $request->label_description));
    @endphp
    <div class="label">
        <div class="company">{{ $company }}</div>
        <img class="qr-code" src="{{ $qrDataUri }}" alt="QR">
        <div class="barcode-text">{{ $request->generated_barcode_material }}</div>
        <div class="lot-text">{{ $request->lot_number }}</div>
        <div class="detail-text">{{ $detail }}</div>
    </div>
</body>
</html>
