<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>POLO SIM — eSIM Aktivasyon Bilgileriniz</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #0f172a; color: #e2e8f0; margin: 0; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background-color: #1e293b; border-radius: 16px; padding: 30px; border: 1px solid #334155; }
        .brand { text-align: center; margin-bottom: 25px; }
        .brand h1 { color: #ffffff; font-size: 24px; margin: 5px 0 0 0; text-transform: uppercase; font-family: monospace; }
        .tagline { color: #fbbf24; font-size: 11px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .badge { background-color: #1e3a8a; color: #60a5fa; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; }
        .details { background-color: #0f172a; border-radius: 12px; padding: 20px; margin: 20px 0; border: 1px solid #1e293b; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #1e293b; font-size: 14px; }
        .detail-row:last-child { border-bottom: none; }
        .label { color: #94a3b8; }
        .value { font-weight: bold; color: #ffffff; }
        .code-box { background-color: #020617; border: 1px dashed #38bdf8; border-radius: 10px; padding: 15px; text-align: center; word-break: break-all; font-family: monospace; font-size: 13px; color: #38bdf8; margin: 20px 0; }
        .btn { display: block; width: 100%; text-align: center; background: linear-gradient(135deg, #2563eb, #4f46e5); color: #ffffff; text-decoration: none; padding: 14px 0; border-radius: 12px; font-weight: bold; font-size: 15px; margin-top: 20px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4); }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1>POLO SIM</h1>
            <div class="tagline">ONE SIM ONE WORLD</div>
        </div>

        <h2 style="color: #ffffff; text-align: center; margin-bottom: 5px;">eSIM Paketiniz Hazır! 🎉</h2>
        <p style="text-align: center; color: #94a3b8; font-size: 14px; margin-top: 0;">Sayın {{ $order->user->name }}, satın almış olduğunuz eSIM paketinizin aktivasyon detayları aşağıdadır.</p>

        <div class="details">
            <div class="detail-row">
                <span class="label">Sipariş Numarası:</span>
                <span class="value">{{ $order->order_no }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Paket Adı:</span>
                <span class="value">{{ $order->product->product_name ?? 'eSIM Paketi' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Veri Miktarı / Süre:</span>
                <span class="value">{{ $order->product->data_total ?? '' }} {{ $order->product->data_unit ?? '' }} — {{ $order->product->usage_period ?? '' }} Gün</span>
            </div>
            <div class="detail-row">
                <span class="label">ICCID Numarası:</span>
                <span class="value" style="font-family: monospace; color: #a7f3d0;">{{ $order->iccid }}</span>
            </div>
        </div>

        <div style="text-align: center;">
            <span class="badge">Aktivasyon Kodunuz (LPA)</span>
        </div>

        <div class="code-box">
            {{ $order->qr_code }}
        </div>

        @if($order->apple_install_url)
            <a href="{{ $order->apple_install_url }}" class="btn">
                📱 iPhone / iPad'e 1-Tıkla Otomatik Kur
            </a>
        @endif

        <div class="footer">
            POLO SIM — Kesintisiz Dünya Çapında eSIM Altyapısı<br>
            Bu e-posta otomatik olarak gönderilmiştir.
        </div>
    </div>
</body>
</html>
