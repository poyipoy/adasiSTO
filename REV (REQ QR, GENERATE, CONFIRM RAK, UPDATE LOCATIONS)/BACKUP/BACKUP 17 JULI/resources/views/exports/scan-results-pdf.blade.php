<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #252a31; }
        h1 { font-size: 14px; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f0f0f0; }
        th, td { border: 1px solid #bfc4ce; padding: 3px 4px; text-align: left; }
    </style>
</head>
<body>
    <h1>STO Scan Results</h1>
    <table>
        <thead>
            <tr>
                <th>No</th><th>Barcode</th><th>Material</th><th>Shape</th><th>T</th><th>W</th><th>D</th><th>L</th><th>Lot</th><th>Qty</th><th>User</th><th>Plant</th><th>Location/Rack</th><th>STO</th><th>Time</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->barcode_material }}</td>
                    <td>{{ $row->material_name }}</td>
                    <td>{{ $row->shape_name }}</td>
                    <td>{{ $row->thickness }}</td>
                    <td>{{ $row->width }}</td>
                    <td>{{ $row->diameter }}</td>
                    <td>{{ $row->length }}</td>
                    <td>{{ $row->lot_number }}</td>
                    <td>{{ $row->qty }}</td>
                    <td>{{ $row->user->name ?? '-' }}</td>
                    <td>{{ $row->plant->name ?? '-' }}</td>
                    <td>{{ $row->location->name ?? '-' }}</td>
                    <td>{{ $row->sto_code }}</td>
                    <td>{{ $row->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $row->keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
