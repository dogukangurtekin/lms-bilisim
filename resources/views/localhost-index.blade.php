<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Localhost Proje Seçici</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #182230;
            --muted: #64748b;
            --border: #dbe3ef;
            --accent: #2563eb;
            --accent-soft: #eff6ff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Verdana, Arial, sans-serif;
            background: linear-gradient(180deg, #eef4ff 0%, var(--bg) 100%);
            color: var(--text);
        }
        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 56px;
        }
        .hero {
            background: rgba(255,255,255,.82);
            border: 1px solid rgba(219,227,239,.9);
            backdrop-filter: blur(8px);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 18px 50px rgba(15,23,42,.08);
        }
        h1 {
            margin: 0 0 10px;
            font-size: clamp(28px, 4vw, 42px);
        }
        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
            margin-top: 22px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 12px 30px rgba(15,23,42,.05);
        }
        .name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .path {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 16px;
            word-break: break-word;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        a.btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            border: 1px solid transparent;
        }
        a.primary {
            background: var(--accent);
            color: white;
        }
        a.secondary {
            background: var(--accent-soft);
            color: var(--accent);
            border-color: #bfdbfe;
        }
        .footer {
            margin-top: 18px;
            font-size: 13px;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="hero">
            <h1>Yerel Projeler</h1>
            <p>localhost altında bulunan projelerden birini seçip doğrudan açabilirsin.</p>

            <div class="grid">
                @foreach($projects as $project)
                    <div class="card">
                        <div class="name">{{ $project['name'] }}</div>
                        <div class="path">{{ $project['path'] }}</div>
                        <div class="actions">
                            <a class="btn primary" href="{{ $project['path'] }}">Projeyi Aç</a>
                            <a class="btn secondary" href="{{ $project['login'] }}">Login</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="footer">
                İpucu: Bir proje içinden çıkıp başka projeyi açmak için önce bu sayfaya dön.
            </div>
        </div>
    </div>
</body>
</html>
