<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Authorized — Shopee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --orange:     #ff5722;
            --orange-dim: rgba(255, 87, 34, 0.15);
            --orange-glow:rgba(255, 87, 34, 0.35);
            --bg:         #0d0d0d;
            --surface:    #141414;
            --border:     rgba(255,255,255,0.07);
            --text:       #e8e0d8;
            --muted:      rgba(232,224,216,0.4);
            --mono:       'JetBrains Mono', monospace;
            --display:    'Syne', sans-serif;
        }

        html, body {
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: var(--mono);
            font-size: 14px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Noise grain overlay ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
            background-size: 200px;
            pointer-events: none;
            z-index: 0;
            opacity: 0.6;
        }

        /* ── Orange ambient glow ── */
        body::after {
            content: '';
            position: fixed;
            top: -20vh;
            left: 50%;
            transform: translateX(-50%);
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, rgba(255,87,34,0.12) 0%, transparent 65%);
            pointer-events: none;
            z-index: 0;
            animation: pulse 8s ease-in-out infinite alternate;
        }

        @keyframes pulse {
            from { opacity: 0.6; transform: translateX(-50%) scale(1); }
            to   { opacity: 1;   transform: translateX(-50%) scale(1.1); }
        }

        /* ── Layout ── */
        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            gap: 0;
        }

        /* ── Card ── */
        .card {
            width: 100%;
            max-width: 560px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 2px;
            overflow: hidden;
            animation: rise 0.6s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes rise {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Card header ── */
        .card-header {
            padding: 2rem 2rem 1.5rem;
            border-bottom: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--orange-dim) 0%, transparent 60%);
            pointer-events: none;
        }

        .status-dot {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--orange);
            margin-bottom: 1rem;
            animation: rise 0.5s 0.1s cubic-bezier(0.22,1,0.36,1) both;
        }

        .status-dot::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--orange);
            box-shadow: 0 0 8px var(--orange);
            animation: blink 2.5s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        .card-title {
            font-family: var(--display);
            font-size: clamp(1.6rem, 5vw, 2.1rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #fff;
            line-height: 1.1;
            animation: rise 0.5s 0.15s cubic-bezier(0.22,1,0.36,1) both;
        }

        .card-title span {
            color: var(--orange);
        }

        /* ── Data rows ── */
        .card-body {
            padding: 0;
        }

        .row {
            display: grid;
            grid-template-columns: 140px 1fr;
            align-items: baseline;
            padding: 0.85rem 2rem;
            border-bottom: 1px solid var(--border);
            animation: rise 0.5s cubic-bezier(0.22,1,0.36,1) both;
            transition: background 0.15s;
        }

        .row:last-child { border-bottom: none; }
        .row:hover { background: rgba(255,255,255,0.025); }

        .row:nth-child(1) { animation-delay: 0.2s; }
        .row:nth-child(2) { animation-delay: 0.25s; }
        .row:nth-child(3) { animation-delay: 0.3s; }
        .row:nth-child(4) { animation-delay: 0.35s; }
        .row:nth-child(5) { animation-delay: 0.4s; }
        .row:nth-child(6) { animation-delay: 0.45s; }
        .row:nth-child(7) { animation-delay: 0.5s; }

        .row-key {
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 500;
            padding-right: 1rem;
        }

        .row-val {
            font-size: 13px;
            color: var(--text);
            word-break: break-all;
            font-weight: 400;
        }

        .row-val.highlight {
            color: var(--orange);
        }

        .row-val.token {
            font-size: 11.5px;
            color: rgba(232,224,216,0.65);
            letter-spacing: 0.01em;
        }

        /* ── No-token state ── */
        .no-token {
            padding: 1.75rem 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--muted);
            font-size: 12.5px;
            border-top: 1px solid var(--border);
        }

        .no-token::before {
            content: '!';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border: 1px solid rgba(255,87,34,0.4);
            border-radius: 50%;
            color: var(--orange);
            font-size: 11px;
            flex-shrink: 0;
        }

        /* ── Back button ── */
        .card-actions {
            padding: 1.25rem 2rem;
            border-top: 1px solid var(--border);
            animation: rise 0.5s 0.55s cubic-bezier(0.22,1,0.36,1) both;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: var(--mono);
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--muted);
            text-decoration: none;
            padding: 0.55rem 1rem;
            border: 1px solid var(--border);
            border-radius: 2px;
            transition: color 0.15s, border-color 0.15s, background 0.15s;
        }

        .btn-back:hover {
            color: var(--orange);
            border-color: rgba(255,87,34,0.4);
            background: var(--orange-dim);
        }

        .btn-back svg {
            width: 12px;
            height: 12px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .row { grid-template-columns: 1fr; gap: 0.2rem; }
            .row-key { padding-right: 0; }
            .card-header { padding: 1.5rem; }
            .row { padding: 0.75rem 1.5rem; }
            .card-actions { padding: 1rem 1.5rem; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="card">

        <div class="card-header">
            <div class="status-dot">Authorization complete</div>
            <h1 class="card-title">Seller<span>.</span><br>Authorized</h1>
        </div>

        <div class="card-body">

            @if($shop)
                <div class="row">
                    <span class="row-key">Shop ID</span>
                    <span class="row-val highlight">{{ $shop->id }}</span>
                </div>

                @if($shop->name)
                <div class="row">
                    <span class="row-key">Shop Name</span>
                    <span class="row-val">{{ $shop->name }}</span>
                </div>
                @endif

                @if($shop->region)
                <div class="row">
                    <span class="row-key">Region</span>
                    <span class="row-val">{{ $shop->region }}</span>
                </div>
                @endif

                <div class="row">
                    <span class="row-key">Auth Code</span>
                    <span class="row-val token">{{ $code }}</span>
                </div>

                @if($shop->accessToken)
                    <div class="row">
                        <span class="row-key">Access Token</span>
                        <span class="row-val token">{{ $shop->accessToken->access_token }}</span>
                    </div>

                    <div class="row">
                        <span class="row-key">Refresh Token</span>
                        <span class="row-val token">{{ $shop->accessToken->refresh_token }}</span>
                    </div>

                    <div class="row">
                        <span class="row-key">Expires At</span>
                        <span class="row-val">{{ $shop->accessToken->expires_at?->toDateTimeString() }}</span>
                    </div>
                @else
                    <div class="no-token">
                        Access token could not be generated. Please retry the authorization flow.
                    </div>
                @endif

            @endif

        </div>

        <div class="card-actions">
            <a href="{{ config('shopee.home_url') ?? config('app.url') }}" class="btn-back">
                <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                Back
            </a>
        </div>

    </div>
</div>
</body>
</html>
