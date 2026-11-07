<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Soohouse Coffee® Masa Kartları</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #0D0D0C;
        }
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8mm;
        }
        .card-cell {
            width: 48%;
            vertical-align: top;
            border-radius: 16px;
            padding: 18px;
            text-align: center;
            background-color: {{ $template['bg'] }};
            color: {{ $template['text'] }};
            border: 2.5px solid {{ $template['border'] }};
            page-break-inside: avoid;
        }
        .brand-box {
            background-color: #0D0D0C;
            color: #FFFFFF;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
            display: inline-block;
            margin-bottom: 6px;
            border: 1px solid {{ $template['accent'] }};
        }
        .branch-title {
            font-size: 10px;
            font-weight: bold;
            color: {{ $template['accent'] }};
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
        }
        .table-pill {
            background-color: {{ $template['accent'] }};
            color: #FFFFFF;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 12px;
        }
        .qr-wrapper {
            background-color: #FFFFFF;
            padding: 12px;
            border-radius: 12px;
            display: inline-block;
            border: 1.5px solid {{ $template['border'] }};
            margin-bottom: 12px;
        }
        .qr-wrapper img {
            width: 170px;
            height: 170px;
            display: block;
        }
        .cta-title {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1.5px;
            margin-top: 4px;
            color: {{ $template['text'] }};
        }
        .cta-sub {
            font-size: 9px;
            margin-top: 4px;
            color: {{ $template['accent'] }};
        }
    </style>
</head>
<body>
    <table class="grid-table">
        @foreach($cards->chunk(2) as $pair)
            <tr>
                @foreach($pair as $card)
                    <td class="card-cell">
                        <div class="brand-box">SOOHOUSECOFFEE®</div>
                        <div class="branch-title">{{ $card['branch']->name }}</div>
                        <div>
                            <span class="table-pill">{{ $card['table']->name }} (#{{ $card['table']->table_number }})</span>
                        </div>
                        <div class="qr-wrapper">
                            <img src="{{ $card['qr_data_uri'] }}" alt="QR">
                        </div>
                        <div class="cta-title">MENÜ İÇİN OKUTUN</div>
                        <div class="cta-sub">DİJİTAL QR MENÜ &amp; MÜŞTERİ GERİ BİLDİRİMİ</div>
                    </td>
                @endforeach
                @if($pair->count() === 1)
                    <td style="width: 48%;"></td>
                @endif
            </tr>
        @endforeach
    </table>
</body>
</html>
