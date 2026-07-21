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
        width: 283.46pt;
        height: 119.06pt; /* 4.2cm */
        position: relative;
        overflow: hidden;
        background: #fff;
    }

    .company {
        position: absolute;
        top: 8pt;
        left: 10pt;
        font-size: 11pt;
        font-weight: bold;
        color: #000;
        letter-spacing: 0.2pt;
    }

    .qr-code {
        position: absolute;
        top: 24pt;
        left: 8pt;
        width: 70pt;
        height: 70pt;
    }

    .barcode-text {
        position: absolute;
        top: 30pt;
        left: 85pt;
        font-size: 11pt;
        font-weight: bold;
        color: #000;
        letter-spacing: 0.5pt;
    }

    .lot-text {
        position: absolute;
        top: 52pt;
        left: 85pt;
        font-size: 11pt;
        font-weight: bold;
        color: #000;
        letter-spacing: 0.5pt;
    }

    .detail-text {
        position: absolute;
        bottom: 10pt;
        left: 10pt;
        font-size: 14pt;
        font-weight: bold;
        color: #000;
        letter-spacing: 0.5pt;
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
