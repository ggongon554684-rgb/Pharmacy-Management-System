<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doseas — Pharmacy Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-serif-display:400,400i|dm-sans:300,400,500,600&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #080d18;
            --bg-card: #0f1724;
            --bg-card-alt: #121c2e;
            --border: rgba(255,255,255,0.07);
            --border-md: rgba(255,255,255,0.12);
            --accent: #3b82f6;
            --accent-dim: rgba(59,130,246,0.1);
            --green: #10b981;
            --green-dim: rgba(16,185,129,0.1);
            --amber: #f59e0b;
            --amber-dim: rgba(245,158,11,0.1);
            --purple: #a78bfa;
            --purple-dim: rgba(167,139,250,0.1);
            --red: #f87171;
            --red-dim: rgba(248,113,113,0.1);
            --tp: #e8f0ff;
            --ts: #6b7fa3;
            --tm: #3a4a65;
            --font-d: 'DM Serif Display', Georgia, serif;
            --font-b: 'DM Sans', system-ui, sans-serif;
            --r: 12px;
            --rs: 8px;
        }
        html { scroll-behavior: smooth; }
        body {
            background: var(--bg);
            color: var(--tp);
            font-family: var(--font-b);
            font-size: 15px;
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: linear-gradient(rgba(59,130,246,0.025) 1px, transparent 1px), linear-gradient(90deg, rgba(59,130,246,0.025) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none; z-index: 0;
        }

        /* NAV */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 200;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2.5rem; height: 58px;
            background: rgba(8,13,24,0.88);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border);
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-family: var(--font-d); font-size: 1.15rem; color: var(--tp); text-decoration: none; letter-spacing: -0.02em; }
        .pill-badge { font-family: var(--font-b); font-size: 10px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; background: var(--accent-dim); color: var(--accent); border: 1px solid rgba(59,130,246,0.28); padding: 2px 8px; border-radius: 100px; }
        .nav-links { display: flex; align-items: center; gap: 6px; }
        .nav-links a { font-size: 14px; font-weight: 500; color: var(--ts); text-decoration: none; padding: 6px 14px; border-radius: var(--rs); border: 1px solid transparent; transition: all 0.15s; }
        .nav-links a:hover { color: var(--tp); }
        .nav-links .btn-nav-p { background: var(--accent); color: #fff; border-color: var(--accent); }
        .nav-links .btn-nav-p:hover { background: #2563eb; }
        .nav-links .btn-nav-o { border-color: var(--border-md); color: var(--tp); }
        .nav-links .btn-nav-o:hover { background: rgba(255,255,255,0.05); }

        /* LAYOUT */
        .wrap { position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; padding: 0 2rem; }
        section { padding: 5rem 0; border-top: 1px solid var(--border); }
        .sec-label { font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--tm); margin-bottom: 0.6rem; }
        .sec-title { font-family: var(--font-d); font-size: clamp(1.7rem, 3vw, 2.2rem); font-weight: 400; letter-spacing: -0.025em; color: var(--tp); margin-bottom: 0.5rem; }
        .sec-sub { font-size: 15px; color: var(--ts); max-width: 52ch; margin-bottom: 2.5rem; line-height: 1.7; }

        /* HERO */
        .hero { padding: 140px 0 80px; display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 3.5rem; align-items: center; border: none; }
        .hero-badge { display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 500; letter-spacing: 0.05em; color: var(--green); background: var(--green-dim); border: 1px solid rgba(16,185,129,0.22); padding: 4px 12px; border-radius: 100px; margin-bottom: 1.5rem; }
        .hero-badge-dot { width: 6px; height: 6px; background: var(--green); border-radius: 50%; animation: pulse-dot 2s infinite; }
        @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(0.8)} }
        h1 { font-family: var(--font-d); font-size: clamp(2.3rem, 4.5vw, 3.4rem); font-weight: 400; line-height: 1.12; letter-spacing: -0.03em; color: var(--tp); margin-bottom: 1.25rem; }
        h1 em { font-style: italic; color: var(--accent); }
        .hero-desc { font-size: 16px; color: var(--ts); line-height: 1.75; max-width: 46ch; margin-bottom: 2rem; }
        .hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 22px; border-radius: var(--rs); font-size: 14px; font-weight: 500; text-decoration: none; border: 1px solid transparent; transition: all 0.15s; cursor: pointer; font-family: var(--font-b); }
        .btn-p { background: var(--accent); color: #fff; border-color: var(--accent); }
        .btn-p:hover { background: #2563eb; }
        .btn-g { background: transparent; color: var(--ts); border-color: var(--border-md); }
        .btn-g:hover { background: rgba(255,255,255,0.05); color: var(--tp); }
        .hero-trust { display: flex; gap: 1.5rem; margin-top: 1.75rem; flex-wrap: wrap; }
        .trust-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--tm); }
        .trust-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--green); }

        /* HERO CARD */
        .hero-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; }
        .hero-card-header { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.02); }
        .win-dot { width: 10px; height: 10px; border-radius: 50%; }
        .hero-card-title { font-size: 12px; font-weight: 500; color: var(--tm); margin-left: 4px; }
        .hero-card-body { padding: 16px; }
        .batch-row { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: var(--rs); margin-bottom: 6px; font-size: 13px; transition: opacity 0.4s; }
        .batch-row.expiring { background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.15); }
        .batch-row.fresh { background: rgba(16,185,129,0.05); border: 1px solid var(--border); }
        .batch-tag { font-size: 10px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; padding: 2px 7px; border-radius: 100px; }
        .tag-warn { background: var(--amber-dim); color: var(--amber); }
        .tag-ok { background: var(--green-dim); color: var(--green); }
        .batch-name { flex: 1; color: var(--ts); }
        .batch-qty { font-weight: 500; color: var(--tp); }
        .fefo-arrow { text-align: center; padding: 6px 0; font-size: 11px; color: var(--tm); letter-spacing: 0.06em; text-transform: uppercase; }

        /* STATS */
        .stats-bar { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: var(--border); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; margin-bottom: 5rem; }
        .stat-cell { background: var(--bg-card); padding: 1.75rem 1.5rem; text-align: center; }
        .stat-num { font-family: var(--font-d); font-size: 2.2rem; font-weight: 400; color: var(--tp); letter-spacing: -0.03em; }
        .stat-label { font-size: 12px; color: var(--tm); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }

        /* WORKFLOW */
        .workflow-container { display: grid; grid-template-columns: 1fr 1.3fr; gap: 3rem; align-items: start; }
        .workflow-steps { display: flex; flex-direction: column; }
        .wstep { display: flex; gap: 16px; padding: 14px 12px; border-radius: var(--r); cursor: pointer; border: 1px solid transparent; transition: background 0.2s; }
        .wstep:hover { background: rgba(255,255,255,0.03); }
        .wstep.active { background: var(--bg-card); border-color: var(--border); }
        .wstep-connector { width: 1px; height: 10px; background: var(--border); margin-left: 26px; }
        .wstep-num { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex-shrink: 0; margin-top: 2px; background: var(--border); color: var(--tm); transition: all 0.2s; }
        .wstep.active .wstep-num { background: var(--accent); color: #fff; }
        .wstep-content { flex: 1; }
        .wstep-title { font-size: 14px; font-weight: 500; color: var(--ts); margin-bottom: 2px; transition: color 0.2s; }
        .wstep.active .wstep-title { color: var(--tp); }
        .wstep-desc { font-size: 13px; color: var(--tm); line-height: 1.55; display: none; }
        .wstep.active .wstep-desc { display: block; color: var(--ts); }
        .wpreview-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; min-height: 360px; }
        .wpreview-header { padding: 12px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .wpreview-title { font-size: 13px; font-weight: 500; color: var(--ts); }
        .wpreview-status { font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 100px; }
        .status-pending { background: var(--amber-dim); color: var(--amber); }
        .status-approved { background: var(--green-dim); color: var(--green); }
        .status-fulfilled { background: var(--accent-dim); color: var(--accent); }
        .status-sold { background: var(--purple-dim); color: var(--purple); }
        .wpreview-body { padding: 16px; }
        .preview-panel { display: none; }
        .preview-panel.active { display: block; animation: fadeUp 0.25s ease; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
        .pf-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.07em; color: var(--tm); margin-bottom: 4px; }
        .pf-value { font-size: 14px; color: var(--tp); font-weight: 500; }
        .pf-sub { font-size: 12px; color: var(--ts); margin-top: 2px; }
        .preview-field { margin-bottom: 12px; }
        .preview-divider { border: none; border-top: 1px solid var(--border); margin: 12px 0; }
        .preview-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .preview-table th { font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--tm); padding: 0 0 8px; text-align: left; font-weight: 500; }
        .preview-table td { padding: 7px 0; border-top: 1px solid var(--border); color: var(--ts); }
        .preview-table td:last-child { text-align: right; color: var(--tp); font-weight: 500; }
        .progress-bar-wrap { background: rgba(255,255,255,0.06); border-radius: 4px; height: 6px; overflow: hidden; margin-top: 6px; }
        .progress-bar-fill { height: 100%; border-radius: 4px; background: var(--accent); transition: width 0.6s ease; }

        /* ROLE SWITCHER */
        .role-switcher { display: grid; grid-template-columns: 220px 1fr; gap: 2rem; align-items: start; }
        .role-tabs { display: flex; flex-direction: column; gap: 6px; }
        .rtab { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: var(--r); cursor: pointer; border: 1px solid transparent; transition: all 0.18s; }
        .rtab:hover { background: rgba(255,255,255,0.03); }
        .rtab.active { background: var(--bg-card); border-color: var(--border); }
        .rtab-icon { width: 32px; height: 32px; border-radius: var(--rs); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .rtab-name { font-size: 14px; font-weight: 500; color: var(--ts); transition: color 0.18s; }
        .rtab.active .rtab-name { color: var(--tp); }
        .rtab-sub { font-size: 11px; color: var(--tm); }
        .role-dashboard { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; }
        .rd-topbar { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.02); }
        .rd-topbar-title { font-size: 13px; font-weight: 500; color: var(--ts); }
        .rd-topbar-role { font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 100px; }
        .rd-content { padding: 16px; }
        .role-panel { display: none; animation: fadeUp 0.22s ease; }
        .role-panel.active { display: block; }
        .rd-table-title { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--tm); margin-bottom: 10px; }

        /* TOOL ACCORDION */
        .tool-accordion { display: flex; flex-direction: column; border-radius: var(--rs); overflow: hidden; border: 1px solid var(--border); }
        .tool-item {}
        .tool-trigger { width: 100%; background: rgba(255,255,255,0.02); border: none; border-top: 1px solid var(--border); padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; text-align: left; transition: background 0.15s; gap: 10px; }
        .tool-item:first-child .tool-trigger { border-top: none; }
        .tool-trigger:hover { background: rgba(255,255,255,0.05); }
        .tool-trigger.open { background: rgba(255,255,255,0.04); }
        .tool-trigger-name { font-size: 13px; font-weight: 500; color: var(--tp); }
        .tool-chevron { flex-shrink: 0; width: 16px; height: 16px; stroke: var(--tm); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; transition: transform 0.2s; }
        .tool-trigger.open .tool-chevron { transform: rotate(180deg); }
        .tool-body { display: none; padding: 10px 14px 13px; border-top: 1px solid var(--border); background: rgba(255,255,255,0.015); font-size: 13px; color: var(--ts); line-height: 1.6; }
        .tool-body.open { display: block; animation: fadeUp 0.18s ease; }

        /* SIMULATOR */
        .sim-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 2.5rem; align-items: start; }
        .sim-controls { display: flex; flex-direction: column; gap: 16px; }
        .sim-control-label { font-size: 12px; font-weight: 500; color: var(--ts); margin-bottom: 6px; }
        .sim-slider-row { display: flex; align-items: center; gap: 12px; }
        .sim-slider-row input[type=range] { flex: 1; -webkit-appearance: none; appearance: none; height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; outline: none; }
        .sim-slider-row input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: var(--accent); cursor: pointer; border: 2px solid var(--bg); }
        .sim-val { font-size: 14px; font-weight: 500; color: var(--tp); min-width: 36px; text-align: right; }
        .sim-result { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--r); padding: 1.5rem; }
        .sim-headline { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--tm); margin-bottom: 12px; }
        .sim-stock-bar { margin-bottom: 12px; }
        .sim-bar-label { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px; }
        .sim-bar-name { color: var(--ts); }
        .sim-bar-qty { font-weight: 500; color: var(--tp); }
        .sim-bar-track { background: rgba(255,255,255,0.07); border-radius: 3px; height: 8px; overflow: hidden; }
        .sim-bar-fill { height: 100%; border-radius: 3px; transition: width 0.45s ease; }
        .sim-msg { border-radius: var(--rs); padding: 10px 12px; font-size: 13px; display: none; margin-top: 12px; }
        .sim-msg.visible { display: block; }
        .sim-warn { background: var(--amber-dim); border: 1px solid rgba(245,158,11,0.2); color: var(--amber); }
        .sim-ok { background: var(--green-dim); border: 1px solid rgba(16,185,129,0.2); color: var(--green); }

        /* FEATURES */
        .feat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: var(--border); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; }
        .feat-cell { background: var(--bg-card); padding: 1.5rem; transition: background 0.15s; }
        .feat-cell:hover { background: var(--bg-card-alt); }
        .feat-icon { width: 36px; height: 36px; border-radius: var(--rs); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
        .feat-cell h3 { font-size: 14px; font-weight: 500; color: var(--tp); margin-bottom: 5px; }
        .feat-cell p { font-size: 13px; color: var(--ts); line-height: 1.55; }

        /* SEAT CARDS */
        .seat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
        .seat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--r); padding: 1.5rem; transition: border-color 0.2s, transform 0.2s; }
        .seat-card:hover { border-color: var(--border-md); transform: translateY(-2px); }
        .seat-card h3 { font-size: 15px; font-weight: 500; color: var(--tp); margin-bottom: 4px; }
        .seat-desc { font-size: 12px; color: var(--tm); padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); }
        .seat-feat { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--ts); padding: 3px 0; }
        .seat-feat::before { content: ''; width: 4px; height: 4px; background: var(--tm); border-radius: 50%; flex-shrink: 0; }

        footer { border-top: 1px solid var(--border); padding: 2.5rem 0; text-align: center; font-size: 13px; color: var(--tm); position: relative; z-index: 1; }

        .fade-in { opacity: 0; transform: translateY(22px); transition: opacity 0.55s ease, transform 0.55s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }

        svg.icon { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        @media (max-width: 800px) {
            nav { padding: 0 1.25rem; }
            .wrap { padding: 0 1.25rem; }
            .hero { grid-template-columns: 1fr; padding: 110px 0 60px; }
            .stats-bar { grid-template-columns: repeat(2, 1fr); }
            .workflow-container, .role-switcher, .sim-grid { grid-template-columns: 1fr; }
            .feat-grid { grid-template-columns: 1fr 1fr; }
            .seat-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<nav>
    <a href="/" class="nav-brand">Doseas <span class="pill-badge">v1</span></a>
    <div class="nav-links">
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/home') }}">Dashboard</a>
            @else
                <a href="{{ route('public.kiosk-order') }}" class="btn-nav-o">Open Kiosk</a>
                <a href="{{ route('login') }}" class="btn-nav-p">Log in</a>
            @endauth
        @endif
    </div>
</nav>

<div class="wrap">

    <div class="hero">
        <div>
            <div class="hero-badge"><span class="hero-badge-dot"></span> Inventory-safe · FEFO-compliant</div>
            <h1>Pharmacy ops that<br><em>never oversell.</em></h1>
            <p class="hero-desc">Role-based workflows for inventory batches, purchase orders, FEFO stock releases, POS sales, and an immutable audit trail — all in one system.</p>
            <div class="hero-actions">
                <a href="{{ route('login') }}" class="btn btn-p">
                    <svg class="icon" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Log in to dashboard
                </a>
                <a href="{{ route('public.kiosk-order') }}" class="btn btn-g">Open Kiosk -></a>
            </div>
            <div class="hero-trust">
                <span class="trust-item"><span class="trust-dot"></span> Stock never goes negative</span>
                <span class="trust-item"><span class="trust-dot"></span> FEFO auto-fulfillment</span>
                <span class="trust-item"><span class="trust-dot"></span> Immutable audit trail</span>
            </div>
        </div>
        <div class="hero-card">
            <div class="hero-card-header">
                <span class="win-dot" style="background:#f87171"></span>
                <span class="win-dot" style="background:#fbbf24"></span>
                <span class="win-dot" style="background:#34d399"></span>
                <span class="hero-card-title">Batch inventory — Amoxicillin 500mg</span>
            </div>
            <div class="hero-card-body">
                <div class="fefo-arrow">↓ FEFO fulfillment order</div>
                <div class="batch-row expiring" id="b1">
                    <span class="batch-tag tag-warn">Exp soon</span>
                    <span class="batch-name">Batch #A-0041</span>
                    <span class="batch-qty">120 units</span>
                </div>
                <div class="batch-row fresh">
                    <span class="batch-tag tag-ok">Fresh</span>
                    <span class="batch-name">Batch #A-0052</span>
                    <span class="batch-qty">300 units</span>
                </div>
                <div class="batch-row fresh">
                    <span class="batch-tag tag-ok">Fresh</span>
                    <span class="batch-name">Batch #A-0061</span>
                    <span class="batch-qty">200 units</span>
                </div>
                <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:12px;color:var(--tm)">Total sellable</span>
                    <span style="font-size:16px;font-weight:500;color:var(--tp)">620 units</span>
                </div>
                <div style="margin-top:8px;font-size:12px;color:var(--green);text-align:right">✓ Next sale deducts from Batch #A-0041 first</div>
            </div>
        </div>
    </div>

    <div class="stats-bar fade-in">
        <div class="stat-cell"><div class="stat-num" data-target="100" data-suffix="%">0%</div><div class="stat-label">Stock safety</div></div>
        <div class="stat-cell"><div class="stat-num" data-target="3" data-suffix="">0</div><div class="stat-label">Role tiers</div></div>
        <div class="stat-cell"><div class="stat-num" data-target="0" data-suffix="%">0%</div><div class="stat-label">Negative stock events</div></div>
        <div class="stat-cell"><div class="stat-num" data-target="100" data-suffix="%">0%</div><div class="stat-label">Actions audited</div></div>
    </div>

    <section class="fade-in">
        <div class="sec-label">How it works</div>
        <div class="sec-title">From order to dispensing</div>
        <p class="sec-sub">A complete workflow from purchase order through to patient sale — every step tracked and approved. Click each step to see a preview.</p>
        <div class="workflow-container">
            <div class="workflow-steps" id="wsteps">
                <div class="wstep active" data-step="0">
                    <div class="wstep-num">1</div>
                    <div class="wstep-content">
                        <div class="wstep-title">Staff creates Purchase Order</div>
                        <div class="wstep-desc">Staff lists required medicines, sets supplier, and submits for admin review.</div>
                    </div>
                </div>
                <div class="wstep-connector"></div>
                <div class="wstep" data-step="1">
                    <div class="wstep-num">2</div>
                    <div class="wstep-content">
                        <div class="wstep-title">Admin approves the PO</div>
                        <div class="wstep-desc">Admin reviews line items, confirms budget, and approves or rejects with notes.</div>
                    </div>
                </div>
                <div class="wstep-connector"></div>
                <div class="wstep" data-step="2">
                    <div class="wstep-num">3</div>
                    <div class="wstep-content">
                        <div class="wstep-title">Receiving creates inventory batches</div>
                        <div class="wstep-desc">Staff records batch numbers and expiry dates. Stock added to back inventory automatically.</div>
                    </div>
                </div>
                <div class="wstep-connector"></div>
                <div class="wstep" data-step="3">
                    <div class="wstep-num">4</div>
                    <div class="wstep-content">
                        <div class="wstep-title">Pharmacist requests front stock (FEFO)</div>
                        <div class="wstep-desc">Stock request pulls from back inventory using First Expired, First Out — oldest batches move to front first.</div>
                    </div>
                </div>
                <div class="wstep-connector"></div>
                <div class="wstep" data-step="4">
                    <div class="wstep-num">5</div>
                    <div class="wstep-content">
                        <div class="wstep-title">POS sale deducts from front stock</div>
                        <div class="wstep-desc">Patient record created, prescription optionally linked, payment recorded, stock deducted in real time.</div>
                    </div>
                </div>
            </div>
            <div>
                <div class="wpreview-card">
                    <div class="wpreview-header">
                        <span class="wpreview-title" id="wp-title">Purchase Order #PO-2041</span>
                        <span class="wpreview-status status-pending" id="wp-status">Pending</span>
                    </div>
                    <div class="wpreview-body">
                        <div class="preview-panel active" id="panel-0">
                            <div class="preview-field"><div class="pf-label">Submitted by</div><div class="pf-value">Staff — Maria Santos</div><div class="pf-sub">Today, 9:14 AM</div></div>
                            <div class="preview-field"><div class="pf-label">Supplier</div><div class="pf-value">MedSource Philippines</div></div>
                            <hr class="preview-divider">
                            <table class="preview-table"><thead><tr><th>Medicine</th><th>Qty</th><th>Unit cost</th></tr></thead><tbody>
                                <tr><td>Amoxicillin 500mg</td><td>500</td><td>₱8.50</td></tr>
                                <tr><td>Metformin 500mg</td><td>300</td><td>₱5.20</td></tr>
                                <tr><td>Amlodipine 5mg</td><td>200</td><td>₱7.80</td></tr>
                            </tbody></table>
                            <hr class="preview-divider">
                            <div style="display:flex;justify-content:space-between;font-size:13px"><span style="color:var(--tm)">Total</span><span style="color:var(--tp);font-weight:500">₱9,475.00</span></div>
                        </div>
                        <div class="preview-panel" id="panel-1">
                            <div class="preview-field"><div class="pf-label">Reviewed by</div><div class="pf-value">Admin — Dr. Reyes</div><div class="pf-sub">Today, 10:02 AM</div></div>
                            <div class="preview-field"><div class="pf-label">Decision</div><div class="pf-value" style="color:var(--green)">✓ Approved</div></div>
                            <div class="preview-field"><div class="pf-label">Note</div><div class="pf-value" style="font-weight:400;font-size:13px;color:var(--ts)">All items confirmed in budget. Proceed with delivery.</div></div>
                            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:40%"></div></div>
                            <div style="font-size:11px;color:var(--tm);margin-top:5px">Step 2 of 5 complete</div>
                        </div>
                        <div class="preview-panel" id="panel-2">
                            <div class="preview-field"><div class="pf-label">Received by</div><div class="pf-value">Staff — Ben Cruz</div><div class="pf-sub">Today, 2:30 PM</div></div>
                            <hr class="preview-divider">
                            <table class="preview-table"><thead><tr><th>Batch</th><th>Expiry</th><th>Added</th></tr></thead><tbody>
                                <tr><td>Amox #A-0061</td><td>Mar 2026</td><td>500</td></tr>
                                <tr><td>Met #M-0033</td><td>Jan 2026</td><td>300</td></tr>
                                <tr><td>Aml #C-0019</td><td>Jun 2026</td><td>200</td></tr>
                            </tbody></table>
                            <div class="progress-bar-wrap" style="margin-top:12px"><div class="progress-bar-fill" style="width:60%"></div></div>
                            <div style="font-size:11px;color:var(--tm);margin-top:5px">Step 3 of 5 complete</div>
                        </div>
                        <div class="preview-panel" id="panel-3">
                            <div class="preview-field"><div class="pf-label">Requested by</div><div class="pf-value">Pharmacist — Ana Lim</div><div class="pf-sub">Today, 3:05 PM</div></div>
                            <div class="preview-field"><div class="pf-label">FEFO strategy</div><div class="pf-value" style="color:var(--accent)">Batch #A-0041 -> front (exp. soonest)</div></div>
                            <div class="preview-field"><div class="pf-label">Qty moved</div><div class="pf-value">120 units of Amoxicillin</div></div>
                            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:80%"></div></div>
                            <div style="font-size:11px;color:var(--tm);margin-top:5px">Step 4 of 5 complete</div>
                        </div>
                        <div class="preview-panel" id="panel-4">
                            <div class="preview-field"><div class="pf-label">Patient</div><div class="pf-value">Juan dela Cruz</div><div class="pf-sub">Rx #RX-9901 · Amoxicillin 500mg</div></div>
                            <div class="preview-field"><div class="pf-label">Dispensed</div><div class="pf-value">21 capsules (7-day course)</div></div>
                            <div class="preview-field"><div class="pf-label">Payment</div><div class="pf-value" style="color:var(--green)">₱178.50 — Cash</div></div>
                            <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:100%;background:var(--green)"></div></div>
                            <div style="font-size:11px;color:var(--green);margin-top:5px">✓ Workflow complete — trail recorded immutably</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="fade-in">
        <div class="sec-label">Role-based access</div>
        <div class="sec-title">Each role sees their tools</div>
        <p class="sec-sub">Select a role, then click any tool to see exactly what it does and how to use it.</p>
        <div class="role-switcher">
            <div class="role-tabs">
                <div class="rtab active" data-role="staff">
                    <div class="rtab-icon" style="background:var(--amber-dim)">
                        <svg class="icon" style="stroke:var(--amber)" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div><div class="rtab-name">Staff</div><div class="rtab-sub">Inventory & POs</div></div>
                </div>
                <div class="rtab" data-role="pharmacist">
                    <div class="rtab-icon" style="background:var(--green-dim)">
                        <svg class="icon" style="stroke:var(--green)" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div><div class="rtab-name">Pharmacist</div><div class="rtab-sub">POS & patients</div></div>
                </div>
                <div class="rtab" data-role="admin">
                    <div class="rtab-icon" style="background:var(--accent-dim)">
                        <svg class="icon" style="stroke:var(--accent)" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                    </div>
                    <div><div class="rtab-name">Admin</div><div class="rtab-sub">Governance & audit</div></div>
                </div>
            </div>

            <div class="role-dashboard">
                <div class="rd-topbar">
                    <span class="rd-topbar-title" id="rd-title">Staff Dashboard</span>
                    <span class="rd-topbar-role" id="rd-badge" style="background:var(--amber-dim);color:var(--amber)">Staff</span>
                </div>
                <div class="rd-content">

                    <!-- STAFF -->
                    <div class="role-panel active" id="rpanel-staff">
                        <div class="rd-table-title">Staff tools</div>
                        <div class="tool-accordion">
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Inventory (Products)</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Manage your medicine catalog and batch/expiry details. Go to Products → Create Product, then add batches, quantities, and expiry dates on the product's batches page.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Create PO</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Submit a purchase order request. Go to Purchase Orders → Create PO, choose a product, enter quantity, unit cost, and expected date, add notes, then submit for admin approval.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Purchase Orders</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Track PO statuses end-to-end — pending → approved → received. Open each PO from the list to review its items and totals.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Incoming Deliveries</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Receive approved POs into inventory. Open Incoming Deliveries, pick the approved PO, enter batch number and expiry date, then confirm to create inventory batches and log stock movements.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Approve Release</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Fulfill stock requests by moving FEFO batches from back inventory to front. Open Stock Requests, review the requested quantity, optionally adjust, then approve to transfer stock and update movements.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Inventory Reports</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Review current stock health and batch expiry information. Use Reports → Inventory to search, filter, and export a PDF if needed.</div>
                            </div>
                        </div>
                    </div>

                    <!-- PHARMACIST -->
                    <div class="role-panel" id="rpanel-pharmacist">
                        <div class="rd-table-title">Pharmacist tools</div>
                        <div class="tool-accordion">
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Quick Release (Sales)</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Release medicine at the counter with stock-safe deductions. Go to Sales → New Sale, pick or create the patient, add products, optionally link a prescription, choose payment method, then complete — FEFO deduction happens automatically.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">POS / Sales</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">View transactions and receipts. Open the sales list, then click any sale to see full details, line items, and payment records.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Patients</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Create and manage patient records. Use Patients → Create to add demographics and allergies, then open a patient to view their purchase history and linked prescriptions.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Prescriptions</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Document prescriptions and link them to dispensing. Use Prescriptions → Create to add items and status, then link during a POS sale when applicable.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Prescribers</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Maintain the prescribers and doctors list used for prescriptions. Add or edit license and contact info to keep Rx creation consistent.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Stock Requests</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Request replenishment when front-shop stock is low. Go to Stock Requests → Create New, enter quantity and reason, then submit — staff will fulfill from back inventory using FEFO.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Patient Reports</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Review a patient's purchase history. Use Reports → Patient Purchases to filter by patient or date range, and export a PDF for records.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Kiosk Page</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Public self-service kiosk for customers. The patient selects medicines and quantities, picks a payment method, then shows the QR ticket to the pharmacist for scanning and fulfillment.</div>
                            </div>
                        </div>
                    </div>

                    <!-- ADMIN -->
                    <div class="role-panel" id="rpanel-admin">
                        <div class="rd-table-title">Admin tools</div>
                        <div class="tool-accordion">
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Products</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Review the full product catalog and inventory batch setup. Inspect stock status, reorder levels, and batch or expiry details for any medicine.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Patients</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">View patient records and audit-sensitive history when needed. Open any patient profile to check linked prescriptions and purchase history.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Purchase Orders</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Govern procurement approvals. Review pending POs, inspect line items, then approve or reject with authorization notes.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Incoming Deliveries</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Monitor the delivery and receiving flow. Review approved and received POs to ensure inventory batches are created correctly with accurate expiry data.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Stock Movements</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Audit every inventory movement. Inspect incoming, release, and adjustment history — each entry is tied to a reference and cannot be altered.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Reports</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Run operational views on stock health and expiry status. Use Reports → Inventory and export PDFs for management records.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Audit Logs</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Compliance and accountability timeline. Search actions — sales, stock changes, overrides — and see exactly who performed them and when.</div>
                            </div>
                            <div class="tool-item">
                                <button class="tool-trigger" onclick="toggleTool(this)">
                                    <span class="tool-trigger-name">Trash</span>
                                    <svg class="tool-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="tool-body">Manage system trash with admin permission. Review removed items and take recovery or permanent deletion actions safely.</div>
                            </div>
                        </div>
                        <div style="margin-top:12px;font-size:12px;color:var(--tm);">Stock override requires admin PIN verification and is fully audited.</div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="fade-in">
        <div class="sec-label">Interactive demo</div>
        <div class="sec-title">See FEFO in action</div>
        <p class="sec-sub">Adjust batch quantities and sale size to watch FEFO fulfillment work automatically — oldest batches consumed first.</p>
        <div class="sim-grid">
            <div class="sim-controls">
                <div>
                    <div class="sim-control-label">Batch A — expires in 30 days (oldest)</div>
                    <div class="sim-slider-row"><input type="range" min="0" max="200" value="80" id="slA" step="1"><span class="sim-val" id="valA">80</span></div>
                </div>
                <div>
                    <div class="sim-control-label">Batch B — expires in 90 days</div>
                    <div class="sim-slider-row"><input type="range" min="0" max="200" value="150" id="slB" step="1"><span class="sim-val" id="valB">150</span></div>
                </div>
                <div>
                    <div class="sim-control-label">Batch C — expires in 180 days (newest)</div>
                    <div class="sim-slider-row"><input type="range" min="0" max="200" value="200" id="slC" step="1"><span class="sim-val" id="valC">200</span></div>
                </div>
                <div>
                    <div class="sim-control-label">Sale quantity requested</div>
                    <div class="sim-slider-row"><input type="range" min="1" max="450" value="100" id="slSale" step="1"><span class="sim-val" id="valSale">100</span></div>
                </div>
                <div style="font-size:12px;color:var(--tm);padding-top:4px">
                    Total available: <span id="sim-total" style="color:var(--ts);font-weight:500">430 units</span>
                </div>
            </div>
            <div class="sim-result">
                <div class="sim-headline">Fulfillment breakdown</div>
                <div class="sim-stock-bar">
                    <div class="sim-bar-label"><span class="sim-bar-name">Batch A (exp. soonest)</span><span class="sim-bar-qty" id="simA-val">—</span></div>
                    <div class="sim-bar-track"><div class="sim-bar-fill" id="simA-bar" style="width:0%;background:var(--amber)"></div></div>
                </div>
                <div class="sim-stock-bar">
                    <div class="sim-bar-label"><span class="sim-bar-name">Batch B</span><span class="sim-bar-qty" id="simB-val">—</span></div>
                    <div class="sim-bar-track"><div class="sim-bar-fill" id="simB-bar" style="width:0%;background:var(--accent)"></div></div>
                </div>
                <div class="sim-stock-bar">
                    <div class="sim-bar-label"><span class="sim-bar-name">Batch C (exp. latest)</span><span class="sim-bar-qty" id="simC-val">—</span></div>
                    <div class="sim-bar-track"><div class="sim-bar-fill" id="simC-bar" style="width:0%;background:var(--green)"></div></div>
                </div>
                <div class="sim-msg sim-warn" id="sim-warn">⚠ Insufficient stock — sale cannot proceed</div>
                <div class="sim-msg sim-ok" id="sim-ok">✓ Sale fulfilled — oldest batches used first</div>
            </div>
        </div>
    </section>

    <section class="fade-in">
        <div class="sec-label">Platform</div>
        <div class="sec-title">Everything you need</div>
        <p class="sec-sub">Built specifically for pharmacy workflows — not a generic inventory tool.</p>
        <div class="feat-grid">
            <div class="feat-cell">
                <div class="feat-icon" style="background:var(--accent-dim)"><svg class="icon" style="stroke:var(--accent)" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
                <h3>Inventory by batch & expiry</h3>
                <p>Track every batch with expiry dates and sellable stock across front and back inventory.</p>
            </div>
            <div class="feat-cell">
                <div class="feat-icon" style="background:var(--green-dim)"><svg class="icon" style="stroke:var(--green)" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                <h3>Purchase Orders (PO)</h3>
                <p>Create as staff, approve as admin, receive to auto-generate inventory batches with expiry data.</p>
            </div>
            <div class="feat-cell">
                <div class="feat-icon" style="background:var(--amber-dim)"><svg class="icon" style="stroke:var(--amber)" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
                <h3>Stock Requests (FEFO)</h3>
                <p>Pull from back inventory to front using First Expired, First Out logic — automatically, every time.</p>
            </div>
            <div class="feat-cell">
                <div class="feat-icon" style="background:var(--purple-dim)"><svg class="icon" style="stroke:var(--purple)" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></div>
                <h3>POS / medicine release</h3>
                <p>Patient records + optional prescription linkage. Stock deducted in real time, sale recorded permanently.</p>
            </div>
            <div class="feat-cell">
                <div class="feat-icon" style="background:var(--green-dim)"><svg class="icon" style="stroke:var(--green)" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                <h3>Immutable movement trail</h3>
                <p>Every incoming delivery, release, and adjustment is permanently recorded. No edits, no gaps.</p>
            </div>
            <div class="feat-cell">
                <div class="feat-icon" style="background:var(--accent-dim)"><svg class="icon" style="stroke:var(--accent)" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <h3>Role-based access control</h3>
                <p>Spatie permissions with distinct, granular responsibilities. Staff, Pharmacist, and Admin each see their tools.</p>
            </div>
        </div>
    </section>

    <section class="fade-in">
        <div class="sec-label">Access model</div>
        <div class="sec-title">Three roles, clear boundaries</div>
        <p class="sec-sub">No role can access what it doesn't need.</p>
        <div class="seat-grid">
            <div class="seat-card">
                <h3>Staff</h3>
                <div class="seat-desc">Inventory & purchasing</div>
                <div class="seat-feat">Receive deliveries and create batches</div>
                <div class="seat-feat">Manage products & inventory</div>
                <div class="seat-feat">Create and submit purchase orders</div>
            </div>
            <div class="seat-card">
                <h3>Pharmacist</h3>
                <div class="seat-desc">Patient-safe POS & prescriptions</div>
                <div class="seat-feat">Create FEFO stock requests</div>
                <div class="seat-feat">Record POS sales with patient records</div>
                <div class="seat-feat">Link and dispense prescriptions</div>
            </div>
            <div class="seat-card">
                <h3>Admin</h3>
                <div class="seat-desc">Governance & audit controls</div>
                <div class="seat-feat">Approve or reject purchase orders</div>
                <div class="seat-feat">Override stock with PIN (audited)</div>
                <div class="seat-feat">Full audit logs & reporting access</div>
            </div>
        </div>
    </section>

</div>

<footer>
    <div class="wrap">&copy; {{ now()->year }} Doseas — Inventory safety, POS checkout, and immutable audit trails.</div>
</footer>

<script>
(function(){
    const obs = new IntersectionObserver(es => es.forEach(e => { if(e.isIntersecting){ e.target.classList.add('visible'); obs.unobserve(e.target); }}), {threshold:0.1});
    document.querySelectorAll('.fade-in').forEach(el => obs.observe(el));

    function animCount(el, target, suffix, dur) {
        let start = null;
        function tick(ts) {
            if(!start) start = ts;
            const p = Math.min((ts-start)/dur, 1);
            el.textContent = Math.round(p*target) + suffix;
            if(p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }
    const statsObs = new IntersectionObserver(es => {
        es.forEach(e => {
            if(e.isIntersecting) {
                document.querySelectorAll('[data-target]').forEach(el => animCount(el, +el.dataset.target, el.dataset.suffix, 1600));
                statsObs.disconnect();
            }
        });
    }, {threshold:0.5});
    const sb = document.querySelector('.stats-bar');
    if(sb) statsObs.observe(sb);

    const wsteps = document.querySelectorAll('.wstep');
    const panels = document.querySelectorAll('.preview-panel');
    const wpTitle = document.getElementById('wp-title');
    const wpStatus = document.getElementById('wp-status');
    const wTitles = ['Purchase Order #PO-2041','Purchase Order #PO-2041','Delivery Receipt #DR-0052','Stock Request #SR-0078','POS Sale #TXN-1145'];
    const wStats = [{t:'Pending',c:'status-pending'},{t:'Approved',c:'status-approved'},{t:'Received',c:'status-fulfilled'},{t:'Fulfilled (FEFO)',c:'status-fulfilled'},{t:'Completed',c:'status-sold'}];
    wsteps.forEach((s,i) => s.addEventListener('click', () => {
        wsteps.forEach(x => x.classList.remove('active'));
        panels.forEach(p => p.classList.remove('active'));
        s.classList.add('active');
        document.getElementById('panel-'+i).classList.add('active');
        wpTitle.textContent = wTitles[i];
        wpStatus.className = 'wpreview-status ' + wStats[i].c;
        wpStatus.textContent = wStats[i].t;
    }));

    const rtabs = document.querySelectorAll('.rtab');
    const rpanels = document.querySelectorAll('.role-panel');
    const rdTitle = document.getElementById('rd-title');
    const rdBadge = document.getElementById('rd-badge');
    const rConf = {
        staff:      {title:'Staff Dashboard',      bg:'var(--amber-dim)',  col:'var(--amber)',  lbl:'Staff'},
        pharmacist: {title:'Pharmacist Dashboard', bg:'var(--green-dim)',  col:'var(--green)',  lbl:'Pharmacist'},
        admin:      {title:'Admin Dashboard',      bg:'var(--accent-dim)', col:'var(--accent)', lbl:'Admin'}
    };
    rtabs.forEach(t => t.addEventListener('click', () => {
        const r = t.dataset.role;
        rtabs.forEach(x => x.classList.remove('active'));
        rpanels.forEach(p => p.classList.remove('active'));
        t.classList.add('active');
        document.getElementById('rpanel-' + r).classList.add('active');
        const c = rConf[r];
        rdTitle.textContent = c.title;
        rdBadge.textContent = c.lbl;
        rdBadge.style.background = c.bg;
        rdBadge.style.color = c.col;
    }));

    window.toggleTool = function(btn) {
        const body = btn.nextElementSibling;
        const isOpen = btn.classList.contains('open');
        const accordion = btn.closest('.tool-accordion');
        accordion.querySelectorAll('.tool-trigger').forEach(b => {
            b.classList.remove('open');
            b.nextElementSibling.classList.remove('open');
        });
        if(!isOpen) {
            btn.classList.add('open');
            body.classList.add('open');
        }
    };

    const slA = document.getElementById('slA'), slB = document.getElementById('slB'), slC = document.getElementById('slC'), slSale = document.getElementById('slSale');
    function runSim() {
        const a = +slA.value, b = +slB.value, c = +slC.value, sale = +slSale.value;
        document.getElementById('valA').textContent = a;
        document.getElementById('valB').textContent = b;
        document.getElementById('valC').textContent = c;
        document.getElementById('valSale').textContent = sale;
        document.getElementById('sim-total').textContent = (a+b+c) + ' units';
        let rem = sale;
        const uA = Math.min(rem,a); rem -= uA;
        const uB = Math.min(rem,b); rem -= uB;
        const uC = Math.min(rem,c);
        const mx = Math.max(a+b+c, 1);
        document.getElementById('simA-val').textContent = uA > 0 ? uA+' used' : '—';
        document.getElementById('simB-val').textContent = uB > 0 ? uB+' used' : '—';
        document.getElementById('simC-val').textContent = uC > 0 ? uC+' used' : '—';
        document.getElementById('simA-bar').style.width = Math.round(uA/mx*100)+'%';
        document.getElementById('simB-bar').style.width = Math.round(uB/mx*100)+'%';
        document.getElementById('simC-bar').style.width = Math.round(uC/mx*100)+'%';
        const insuf = rem > 0;
        document.getElementById('sim-warn').classList.toggle('visible', insuf);
        document.getElementById('sim-ok').classList.toggle('visible', !insuf);
    }
    [slA,slB,slC,slSale].forEach(s => s.addEventListener('input', runSim));
    runSim();

    setInterval(() => {
        const b = document.getElementById('b1');
        if(b) b.style.opacity = b.style.opacity === '0.6' ? '1' : '0.6';
    }, 1500);
})();
</script>
</body>
</html>