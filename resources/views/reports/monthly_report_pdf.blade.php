<!DOCTYPE html>
<html>
<head>
    <title>Laporan Bulanan Absensi & Kas</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #f97316; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .summary-box { margin-top: 30px; padding: 20px; background: #fff7ed; border-radius: 8px; border: 1px solid #ffedd5; }
        .footer { margin-top: 50px; text-align: right; font-size: 12px; }
        .chart-placeholder { margin-top: 20px; height: 10px; background: #e5e7eb; border-radius: 5px; overflow: hidden; }
        .chart-bar { height: 100%; background: #f97316; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ORENS ABSENCE & CASH</h1>
        <p>Laporan Rekapitulasi Bulanan - {{ $monthName }} {{ $year }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Divisi</th>
                <th>Presensi Hadir</th>
                <th>Izin/Sakit</th>
                <th>Kas Lunas</th>
                <th>Tunggakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td><strong>{{ $item['name'] }}</strong></td>
                <td>{{ $item['hadir'] }}</td>
                <td>{{ $item['izin_sakit'] }}</td>
                <td>Rp {{ number_format($item['paid'], 0, ',', '.') }}</td>
                <td style="color: #dc2626;">Rp {{ number_format($item['unpaid'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <h3>Ringkasan Keseluruhan</h3>
        <p>Aktivitas paling tinggi bulan ini berada pada divisi dengan tingkat kehadiran terbaik.</p>
        @foreach($data as $item)
            @php
                $total = $item['hadir'] + $item['izin_sakit'] ?: 1;
                $percent = ($item['hadir'] / $total) * 100;
            @endphp
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; font-size: 14px;">
                    <span>{{ $item['name'] }} ({{ round($percent) }}%)</span>
                </div>
                <div class="chart-placeholder">
                    <div class="chart-bar" style="width: {{ $percent }}%;"></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Sistem Absensi Orens - Sekretaris Panel</p>
    </div>
</body>
</html>
