<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something went wrong</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            color: #e2e8f0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 24px;
        }
        .card {
            max-width: 460px;
            width: 100%;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 36px 32px;
            text-align: center;
        }
        h1 { font-size: 1.375rem; margin: 16px 0 8px; color: #f8fafc; }
        p { color: #94a3b8; font-size: 0.9rem; line-height: 1.5; margin: 0 0 20px; }
        .code {
            display: inline-block;
            font-family: 'SF Mono', Consolas, monospace;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            background: #0f172a;
            border: 1px solid #334155;
            color: #fbbf24;
            padding: 10px 18px;
            border-radius: 10px;
        }
        .hint { margin-top: 18px; font-size: 0.75rem; color: #64748b; }
        a.back {
            display: inline-block;
            margin-top: 22px;
            color: #fbbf24;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        a.back:hover { text-decoration: underline; }
        svg { color: #f87171; }
    </style>
</head>
<body>
    <div class="card">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        <h1>Something went wrong</h1>
        <p>An unexpected error occurred. Our team has been notified. If you need help, please share the reference code below with support.</p>
        <span class="code">{{ $code }}</span>
        <p class="hint">Quote this reference code when contacting support so we can find the exact issue.</p>
        <a class="back" href="{{ url('/') }}">&larr; Back to home</a>
    </div>
</body>
</html>
