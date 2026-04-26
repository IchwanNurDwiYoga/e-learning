<!DOCTYPE html>
<html lang="id" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sedang Maintenance – E-PASTE</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #f7f1e8;
            --surface:   #ffffff;
            --border:    #e2d9cc;
            --text:      #0f172a;
            --muted:     #64748b;
            --accent:    #0f766e;
            --accent-h:  #115e59;
            --bubble1:   rgba(245,158,11,.18);
            --bubble2:   rgba(15,118,110,.18);
            --bubble3:   rgba(37,99,235,.10);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg:      #020617;
                --surface: #0f172a;
                --border:  #1e293b;
                --text:    #f1f5f9;
                --muted:   #94a3b8;
                --bubble1: rgba(245,158,11,.10);
                --bubble2: rgba(15,118,110,.10);
                --bubble3: rgba(37,99,235,.07);
            }
        }

        html.dark {
            --bg:      #020617;
            --surface: #0f172a;
            --border:  #1e293b;
            --text:    #f1f5f9;
            --muted:   #94a3b8;
            --bubble1: rgba(245,158,11,.10);
            --bubble2: rgba(15,118,110,.10);
            --bubble3: rgba(37,99,235,.07);
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            overflow: hidden;
            transition: background-color .3s, color .3s;
        }

        /* ── decorative bubbles ── */
        .bubbles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }
        .bubble {
            position: absolute;
            border-radius: 9999px;
            filter: blur(80px);
        }
        .b1 { width: 420px; height: 420px; top: -100px; left: -80px;  background: var(--bubble1); }
        .b2 { width: 380px; height: 380px; top: 60px;   right: -60px; background: var(--bubble2); }
        .b3 { width: 350px; height: 350px; bottom: -80px; left: 33%;  background: var(--bubble3); }

        /* ── card ── */
        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 540px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 2rem;
            padding: 3rem 2.5rem;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,.08);
            transition: background .3s, border-color .3s;
        }

        /* ── logo badge ── */
        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px; height: 64px;
            background: #0f172a;
            border-radius: 1.25rem;
            font-size: 1.1rem;
            font-weight: 900;
            color: #f7f1e8;
            letter-spacing: -.02em;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
        }

        /* ── gear animation ── */
        .gear-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-bottom: 1.75rem;
        }
        .gear {
            animation: spin 4s linear infinite;
            color: var(--accent);
        }
        .gear-sm {
            animation: spin-rev 3s linear infinite;
            color: #f59e0b;
            width: 28px; height: 28px;
        }
        @keyframes spin     { to { transform: rotate(360deg);  } }
        @keyframes spin-rev { to { transform: rotate(-360deg); } }

        /* ── pulse dot ── */
        .status-row {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(245,158,11,.12);
            border: 1px solid rgba(245,158,11,.3);
            border-radius: 9999px;
            padding: .35rem .9rem;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #b45309;
            margin-bottom: 1.5rem;
        }
        .dot {
            width: 8px; height: 8px;
            border-radius: 9999px;
            background: #f59e0b;
            animation: pulse-dot 1.6s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .4; transform: scale(.7); }
        }

        h1 {
            font-size: 2rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: .75rem;
            letter-spacing: -.02em;
        }
        p.lead {
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        /* ── progress bar ── */
        .progress-wrap {
            background: var(--border);
            border-radius: 9999px;
            height: 6px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .progress-bar {
            height: 100%;
            border-radius: 9999px;
            background: linear-gradient(90deg, var(--accent), #059669);
            animation: progress-move 2.5s ease-in-out infinite alternate;
            width: 65%;
        }
        @keyframes progress-move {
            from { width: 30%; }
            to   { width: 85%; }
        }

        /* ── info chips ── */
        .chips {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: .6rem;
            margin-bottom: 2rem;
        }
        .chip {
            display: flex;
            align-items: center;
            gap: .4rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: .75rem;
            padding: .5rem .9rem;
            font-size: .8rem;
            font-weight: 600;
            color: var(--muted);
            transition: background .3s, border-color .3s;
        }
        .chip svg { flex-shrink: 0; }

        /* ── footer note ── */
        .footer {
            margin-top: 1.5rem;
            font-size: .75rem;
            color: var(--muted);
            line-height: 1.6;
        }

        /* ── theme toggle ── */
        .theme-btn {
            position: fixed;
            top: 1.25rem; right: 1.25rem;
            z-index: 10;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 9999px;
            width: 2.5rem; height: 2.5rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--muted);
            transition: background .2s, border-color .2s, color .2s;
        }
        .theme-btn:hover { color: var(--accent); }
    </style>
</head>
<body>

    <!-- theme toggle -->
    <button class="theme-btn" id="themeBtn" title="Toggle tema" aria-label="Toggle dark/light mode">
        <svg id="iconSun" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24" style="display:none">
            <path d="M12 3a1 1 0 011 1v1a1 1 0 11-2 0V4a1 1 0 011-1zm0 15a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm9-6a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM6 12a1 1 0 01-1 1H4a1 1 0 110-2h1a1 1 0 011 1zm11.364 6.95a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM8.05 8.05a1 1 0 010 1.414l-.707.707A1 1 0 115.93 8.757l.707-.707a1 1 0 011.414 0zm9.314-2.12a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM8.757 15.243a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM12 8a4 4 0 100 8 4 4 0 000-8z"/>
        </svg>
        <svg id="iconMoon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
            <path d="M21 14.5A8.5 8.5 0 119.5 3a1 1 0 01.79 1.613A6.5 6.5 0 1019.387 13.71 1 1 0 0121 14.5z"/>
        </svg>
    </button>

    <!-- decorative bubbles -->
    <div class="bubbles" aria-hidden="true">
        <div class="bubble b1"></div>
        <div class="bubble b2"></div>
        <div class="bubble b3"></div>
    </div>

    <!-- main card -->
    <div class="card" role="main">

        <!-- logo -->
        <div class="logo" aria-label="E-PASTE Logo">EL</div>

        <!-- gears -->
        <div class="gear-wrap" aria-hidden="true">
            <svg class="gear" xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 15a3 3 0 100-6 3 3 0 000 6zm8.94-3a8.001 8.001 0 00-.16-1.57l1.8-1.24a.5.5 0 00.14-.64l-1.9-3.3a.5.5 0 00-.61-.22l-2.12.85a7.94 7.94 0 00-1.36-.78l-.32-2.25A.5.5 0 0015.9 3h-3.8a.5.5 0 00-.5.43l-.32 2.25a7.94 7.94 0 00-1.36.78L7.8 5.61a.5.5 0 00-.61.22l-1.9 3.3a.5.5 0 00.14.64l1.8 1.24A8.16 8.16 0 007.06 12a8.16 8.16 0 00.17 1.57l-1.8 1.24a.5.5 0 00-.14.64l1.9 3.3a.5.5 0 00.61.22l2.12-.85c.43.29.89.54 1.36.78l.32 2.25c.06.25.28.43.5.43h3.8c.22 0 .44-.18.5-.43l.32-2.25a7.94 7.94 0 001.36-.78l2.12.85c.23.09.49 0 .61-.22l1.9-3.3a.5.5 0 00-.14-.64l-1.8-1.24c.09-.51.14-1.04.14-1.57z"/>
            </svg>
            <svg class="gear-sm" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 15a3 3 0 100-6 3 3 0 000 6zm8.94-3a8.001 8.001 0 00-.16-1.57l1.8-1.24a.5.5 0 00.14-.64l-1.9-3.3a.5.5 0 00-.61-.22l-2.12.85a7.94 7.94 0 00-1.36-.78l-.32-2.25A.5.5 0 0015.9 3h-3.8a.5.5 0 00-.5.43l-.32 2.25a7.94 7.94 0 00-1.36.78L7.8 5.61a.5.5 0 00-.61.22l-1.9 3.3a.5.5 0 00.14.64l1.8 1.24A8.16 8.16 0 007.06 12a8.16 8.16 0 00.17 1.57l-1.8 1.24a.5.5 0 00-.14.64l1.9 3.3a.5.5 0 00.61.22l2.12-.85c.43.29.89.54 1.36.78l.32 2.25c.06.25.28.43.5.43h3.8c.22 0 .44-.18.5-.43l.32-2.25a7.94 7.94 0 001.36-.78l2.12.85c.23.09.49 0 .61-.22l1.9-3.3a.5.5 0 00-.14-.64l-1.8-1.24c.09-.51.14-1.04.14-1.57z"/>
            </svg>
        </div>

        <!-- status badge -->
        <div class="status-row" role="status" aria-live="polite">
            <div class="dot"></div>
            Sedang Maintenance
        </div>

        <h1>Kami Sedang<br>Melakukan Perbaikan</h1>

        <p class="lead">
            E-PASTE sedang dalam proses pemeliharaan terjadwal untuk memberikan pengalaman yang lebih baik.
            Sistem akan kembali online dalam waktu dekat.
        </p>

        <!-- progress bar -->
        <div class="progress-wrap" role="progressbar" aria-label="Progress pemeliharaan">
            <div class="progress-bar"></div>
        </div>

        <!-- info chips -->
        <div class="chips">
            <div class="chip">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm.5 5v6l4 2.4-.8 1.4-4.7-2.8V7h1.5z"/>
                </svg>
                Estimasi selesai segera
            </div>
            <div class="chip">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 1a11 11 0 100 22A11 11 0 0012 1zm0 2a9 9 0 110 18A9 9 0 0112 3zm-.5 4v5.5l4.3 2.5-.8 1.3-4.8-2.8V7h1.3z" style="display:none"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
                Data Anda aman
            </div>
            <div class="chip">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15v-4H7l5-8v4h4l-5 8z"/>
                </svg>
                Update sistem aktif
            </div>
        </div>

        @if($exception->getMessage())
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:.75rem;padding:.75rem 1rem;font-size:.8rem;color:#b91c1c;margin-bottom:1.5rem;text-align:left;">
            <strong>Pesan teknis:</strong> {{ $exception->getMessage() }}
        </div>
        @endif

        <p class="footer">
            E-PASTE &mdash; Electronic Performance Assessment Berbasis SETS Bermuatan Ethnoscience<br>
            Terima kasih atas kesabaran Anda.
        </p>
    </div>

    <script>
        (function () {
            const KEY = 'theme-preference';
            const root = document.documentElement;
            const stored = localStorage.getItem(KEY);

            const isDark = stored === 'dark'
                || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);

            if (isDark) root.classList.add('dark');

            const btn     = document.getElementById('themeBtn');
            const iconSun  = document.getElementById('iconSun');
            const iconMoon = document.getElementById('iconMoon');

            function applyIcons() {
                const dark = root.classList.contains('dark');
                iconSun.style.display  = dark ? 'block' : 'none';
                iconMoon.style.display = dark ? 'none'  : 'block';
            }

            applyIcons();

            btn.addEventListener('click', function () {
                const nowDark = root.classList.toggle('dark');
                localStorage.setItem(KEY, nowDark ? 'dark' : 'light');
                applyIcons();
            });
        })();
    </script>
</body>
</html>
