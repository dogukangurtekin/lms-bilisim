@php
    $themeTemplate = (string) ($themeTemplate ?? 'default');
    $globalThemeCss = trim((string) ($globalThemeCss ?? ''));
    $themeMap = [
        'default' => <<<'CSS'
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#eef6ff 100%);color:#0f172a;--theme-accent:#0f766e;--theme-accent-2:#2563eb;--theme-bg:#f8fbff;--theme-panel:#ffffff;--theme-border:#bfdbfe}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.025em;line-height:1.12;font-weight:900;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.82;color:#334155}
.slide-theme :where(strong,b){color:#0f172a;font-weight:800}
.slide-theme :where(a){color:#0f766e;text-decoration:none;border-bottom:1px solid rgba(15,118,110,.2)}
.slide-theme :where(code,pre,kbd,samp){background:#dbeafe;color:#0f172a;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #0f766e;background:#ecfeff;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#dbeafe;color:#0f172a;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #bfdbfe;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#475569;text-align:center}
.slide-theme :where(section,article,aside,main,header,footer,nav,div){border-radius:16px}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(14,116,144,.08);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.9))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#dbeafe;color:#0f172a;border-radius:999px;padding:.15rem .55rem;font-weight:800}
CSS,
        'aurora' => <<<'CSS'
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(135deg,#f0f9ff 0%,#eef2ff 48%,#f5f3ff 100%);color:#1e293b;--theme-accent:#2563eb;--theme-accent-2:#7c3aed;--theme-bg:rgba(255,255,255,.58);--theme-panel:#ffffff;--theme-border:rgba(37,99,235,.16)}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.02em;line-height:1.15;font-weight:800;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.8;color:#334155}
.slide-theme :where(strong,b){color:#111827;font-weight:800}
.slide-theme :where(a){color:#1d4ed8;text-decoration:none;border-bottom:1px solid rgba(29,78,216,.25)}
.slide-theme :where(code,pre,kbd,samp){background:#e0f2fe;color:#0f172a;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #3b82f6;background:#eff6ff;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 10px 24px rgba(15,23,42,.08)}
.slide-theme :where(th){background:#dbeafe;color:#0f172a;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #bfdbfe;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#64748b;text-align:center}
.slide-theme :where(section,article,aside,main,header,footer,nav,div){border-radius:16px}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(37,99,235,.08);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.72))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#dbeafe;color:#1e3a8a;border-radius:999px;padding:.15rem .55rem;font-weight:700}
CSS,
        'paper' => <<<'CSS'
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:"Georgia",serif;background:linear-gradient(180deg,#fffdf8 0%,#fbf7ef 100%);color:#2f2a23;--theme-accent:#7c2d12;--theme-accent-2:#b45309;--theme-bg:#fffaf0;--theme-panel:#fffef8;--theme-border:#e7c89b}
.slide-theme :where(h1,h2,h3,h4,h5,h6){font-family:"Trebuchet MS",system-ui,sans-serif;color:#3b2f2a;letter-spacing:0;line-height:1.12;font-weight:800;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:19px;line-height:1.85;color:#40352e}
.slide-theme :where(a){color:#92400e;text-decoration:underline}
.slide-theme :where(code,pre,kbd,samp){background:#f5e7d6;color:#4b2e1a;border-radius:10px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #b45309;background:#fff4e6;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fffdf9;border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#f7d9b5;color:#4b2e1a;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #e7c89b;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#8a5a2b;text-align:center}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(180,83,9,.08);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.82))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#f5e7d6;color:#7c2d12;border-radius:999px;padding:.15rem .55rem;font-weight:700}
CSS,
        'midnight' => <<<'CSS'
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:Inter,system-ui,sans-serif;background:radial-gradient(circle at top,#1f2937 0,#0f172a 55%,#020617 100%);color:#e5e7eb;--theme-accent:#38bdf8;--theme-accent-2:#a78bfa;--theme-bg:rgba(15,23,42,.8);--theme-panel:rgba(15,23,42,.92);--theme-border:rgba(125,211,252,.22)}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#f8fafc;letter-spacing:-.03em;line-height:1.1;font-weight:900;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.85;color:#cbd5e1}
.slide-theme :where(a){color:#7dd3fc;text-decoration:none;border-bottom:1px solid rgba(125,211,252,.3)}
.slide-theme :where(code,pre,kbd,samp){background:#111827;color:#f8fafc;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #38bdf8;background:rgba(56,189,248,.1);padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:rgba(15,23,42,.85);border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#1e293b;color:#f8fafc;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid rgba(148,163,184,.2);padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#94a3b8;text-align:center}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(0,0,0,.18);background:linear-gradient(180deg,var(--theme-panel),rgba(15,23,42,.74))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:rgba(56,189,248,.14);color:#e0f2fe;border-radius:999px;padding:.15rem .55rem;font-weight:700}
CSS,
        'playful' => <<<'CSS'
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:"Trebuchet MS",system-ui,sans-serif;background:linear-gradient(135deg,#fff7ed 0%,#fef3c7 35%,#ecfeff 100%);color:#1f2937;--theme-accent:#f97316;--theme-accent-2:#06b6d4;--theme-bg:#fff8ed;--theme-panel:#ffffff;--theme-border:#fdba74}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.02em;line-height:1.14;font-weight:900;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.8;color:#334155}
.slide-theme :where(a){color:#ea580c;text-decoration:none;border-bottom:1px dashed rgba(234,88,12,.35)}
.slide-theme :where(code,pre,kbd,samp){background:#ffedd5;color:#7c2d12;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #f97316;background:#fff7ed;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#fed7aa;color:#7c2d12;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #fdba74;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#c2410c;text-align:center}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:20px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(249,115,22,.09);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.82))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#ffedd5;color:#9a3412;border-radius:999px;padding:.15rem .55rem;font-weight:800}
CSS,
        'academy' => <<<'CSS'
.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{font-family:Inter,system-ui,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#eef6ff 100%);color:#0f172a;--theme-accent:#0f766e;--theme-accent-2:#2563eb;--theme-bg:#f8fbff;--theme-panel:#ffffff;--theme-border:#bfdbfe}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.025em;line-height:1.12;font-weight:900;margin:0 0 .75rem}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.82;color:#334155}
.slide-theme :where(a){color:#0f766e;text-decoration:none;border-bottom:1px solid rgba(15,118,110,.2)}
.slide-theme :where(code,pre,kbd,samp){background:#dbeafe;color:#0f172a;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:14px 16px;overflow:auto}
.slide-theme :where(blockquote){border-left:6px solid #0f766e;background:#ecfeff;padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:14px;overflow:hidden}
.slide-theme :where(th){background:#dbeafe;color:#0f172a;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid #bfdbfe;padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:16px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-bg);border:1px solid var(--theme-border);border-radius:18px}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#475569;text-align:center}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right){border-radius:18px;border:1px solid var(--theme-border);box-shadow:0 14px 30px rgba(14,116,144,.08);background:linear-gradient(180deg,var(--theme-panel),rgba(255,255,255,.9))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#dbeafe;color:#0f172a;border-radius:999px;padding:.15rem .55rem;font-weight:800}
CSS,
    ];
    $resolvedThemeCss = $globalThemeCss !== '' ? $globalThemeCss : ($themeMap[$themeTemplate] ?? $themeMap['default']);
@endphp
@if(trim($resolvedThemeCss) !== '')
<style>
{{ $resolvedThemeCss }}
</style>
@endif
