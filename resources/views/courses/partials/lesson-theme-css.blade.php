.slide-theme, .slide-theme *{box-sizing:border-box}
.slide-theme{
    font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    background:
        radial-gradient(circle at top left, rgba(37,99,235,.16), transparent 32%),
        radial-gradient(circle at top right, rgba(15,23,42,.10), transparent 28%),
        linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
    color:#0f172a;
    --theme-blue:#2563eb;
    --theme-blue-2:#0ea5e9;
    --theme-black:#0f172a;
    --theme-white:#ffffff;
    --theme-muted:#64748b;
    --theme-border:rgba(37,99,235,.16);
    --theme-surface:rgba(255,255,255,.82);
    --theme-card:#ffffff;
    min-height:100%;
}
.slide-theme :where(h1,h2,h3,h4,h5,h6){color:#0f172a;letter-spacing:-.03em;line-height:1.1;font-weight:900;margin:0 0 .75rem}
.slide-theme h1{font-size:clamp(34px,4vw,58px)}
.slide-theme h2{font-size:clamp(28px,3vw,44px)}
.slide-theme h3{font-size:clamp(22px,2.2vw,32px)}
.slide-theme :where(p,li,div,span){font-size:18px;line-height:1.82;color:#334155}
.slide-theme :where(strong,b){color:inherit;font-weight:800}
.slide-theme :where(a){color:#2563eb;text-decoration:none;border-bottom:1px solid rgba(37,99,235,.24)}
.slide-theme :where(code,pre,kbd,samp){background:#0f172a;color:#f8fafc;border-radius:12px;padding:.2rem .5rem;font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
.slide-theme pre{padding:16px 18px;overflow:auto;box-shadow:inset 0 0 0 1px rgba(255,255,255,.05)}
.slide-theme :where(blockquote){border-left:6px solid #2563eb;background:rgba(37,99,235,.08);padding:14px 16px;border-radius:0 16px 16px 0}
.slide-theme :where(table){width:100%;border-collapse:collapse;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 14px 30px rgba(15,23,42,.08)}
.slide-theme :where(th){background:linear-gradient(180deg,#eff6ff,#dbeafe);color:#0f172a;font-weight:800;text-align:left}
.slide-theme :where(td,th){border:1px solid rgba(37,99,235,.14);padding:10px 12px;vertical-align:top}
.slide-theme :where(img,video,iframe){max-width:100%;border-radius:20px;display:block}
.slide-theme :where(figure){margin:16px 0;padding:12px;background:var(--theme-surface);border:1px solid var(--theme-border);border-radius:20px;box-shadow:0 14px 28px rgba(15,23,42,.06)}
.slide-theme :where(figcaption){margin-top:8px;font-size:14px;color:#64748b;text-align:center}
.slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right,.sqz-qcard){border-radius:20px;border:1px solid var(--theme-border);box-shadow:0 16px 34px rgba(37,99,235,.08);background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(248,251,255,.88))}
.slide-theme :where(.highlight,.badge,.pill,.callout){background:#dbeafe;color:#0f172a;border-radius:999px;padding:.15rem .55rem;font-weight:800}
.slide-theme :where(.slide-title,.lesson-title,.lesson-subtitle){letter-spacing:-.03em}
.slide-theme :where(.lesson-paragraph,.slide-render p){line-height:1.55}
.slide-theme :where(.lesson-rich-text){
    display:grid;
    gap:14px;
}
.slide-theme :where(.lesson-rich-text h1,.lesson-rich-text h2,.lesson-rich-text h3,.lesson-rich-text h4){
    margin:0;
    font-weight:900;
    color:#0f172a;
}
.slide-theme :where(.lesson-rich-text h1){font-size:clamp(28px,3vw,46px)}
.slide-theme :where(.lesson-rich-text h2){font-size:clamp(24px,2.4vw,36px)}
.slide-theme :where(.lesson-rich-text h3){font-size:clamp(20px,2vw,28px)}
.slide-theme :where(.lesson-rich-text p){
    margin:0;
    font-size:18px;
    line-height:1.5;
    color:#334155;
}
.slide-theme :where(.lesson-rich-text ul,.lesson-rich-text ol){
    margin:0;
    padding-left:1.2rem;
    display:grid;
    gap:6px;
}
.slide-theme :where(.lesson-rich-text li){
    font-size:18px;
    line-height:1.45;
    color:#334155;
}
.slide-theme :where(.lesson-rich-text li::marker){
    color:#2563eb;
    font-weight:900;
}
.slide-theme :where(.lesson-rich-text blockquote){
    margin:0;
    padding:14px 16px;
    border-left:5px solid #2563eb;
    background:rgba(37,99,235,.08);
    border-radius:0 16px 16px 0;
}
.slide-theme :where(.lesson-list,ul,ol){padding-left:1.2rem}
.slide-theme :where(.lesson-divider,hr){border:0;height:1px;background:linear-gradient(90deg,transparent,rgba(37,99,235,.22),transparent);margin:18px 0}
.slide-theme :where(.lesson-image){border-radius:20px;overflow:hidden;box-shadow:0 18px 34px rgba(15,23,42,.08)}
.slide-theme :where(.lesson-card){padding:18px;border:1px solid rgba(37,99,235,.14);border-radius:20px;background:linear-gradient(180deg,#fff,#f8fbff)}
.slide-theme :where(.lesson-slide-shell){display:grid;gap:18px}
.slide-theme :where(.lesson-slide-eyebrow){font-size:12px;font-weight:900;letter-spacing:.16em;text-transform:uppercase;color:#2563eb}
.slide-theme :where(.lesson-slide-title){margin:0;font-size:clamp(30px,3.6vw,54px);line-height:1.05}
.slide-theme :where(.lesson-slide-subtitle){margin:0;font-size:20px;color:#475569}
.slide-theme :where(.lesson-hero-card){padding:28px;border-radius:28px;background:linear-gradient(135deg,#eff6ff 0%,#ffffff 100%);border:1px solid rgba(37,99,235,.14);box-shadow:0 24px 48px rgba(37,99,235,.10)}
.slide-theme :where(.lesson-split){
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(0,1fr);
    gap:18px;
    align-items:center;
    justify-items:stretch;
    width:min(100%,82vw);
    max-width:1800px;
    margin:0 auto;
    min-height:auto;
    padding:clamp(12px,4vh,32px) 0;
}
.slide-theme :where(.lesson-split-card){
    display:flex;
    flex-direction:column;
    justify-content:center;
    min-height:clamp(300px,48vh,560px);
    padding:12px;
}
.slide-theme :where(.lesson-split-tag){
    display:inline-flex;
    align-self:flex-start;
    margin-bottom:12px;
    padding:8px 12px;
    border-radius:999px;
    font-size:12px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
    background:rgba(37,99,235,.10);
    color:#1d4ed8;
    border:1px solid rgba(37,99,235,.12);
}
.slide-theme :where(.lesson-split-tag--right){
    background:rgba(124,58,237,.10);
    color:#6d28d9;
    border-color:rgba(124,58,237,.14);
}
.slide-theme :where(.lesson-split-body){
    display:grid;
    gap:10px;
    min-height:0;
    align-content:center;
}
.slide-theme :where(.lesson-split-body--center){
    justify-items:center;
}
.slide-theme :where(.lesson-split-media){
    width:100%;
    object-fit:cover;
    min-height:180px;
    max-height:min(38vh,420px);
}
.slide-theme :where(.lesson-image){width:100%;object-fit:cover;min-height:180px;max-height:min(38vh,420px)}
.slide-theme :where(.lesson-code-frame){width:100%;min-height:420px;border:1px solid rgba(37,99,235,.14);border-radius:20px;background:#fff}
.slide-theme :where(.lesson-grid-cards){display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.slide-theme :where(.lesson-list){margin:0}
.slide-theme :where(.lesson-list li){margin:0 0 10px}
.slide-theme :where(.lesson-link){display:inline-flex;align-items:center;justify-content:center;margin-top:10px;padding:10px 14px;border-radius:999px;background:#2563eb;color:#fff!important;font-weight:800;border:0}
.slide-theme :where(.lesson-interactive-panel){margin-top:12px}
.slide-theme :where(.lesson-code){background:#0b1220;color:#e2e8f0;border-radius:18px;padding:18px;overflow:auto}
.slide-theme :where(.lesson-quote){padding:18px 20px;border-left:6px solid #2563eb;background:rgba(37,99,235,.08);border-radius:0 18px 18px 0}
.slide-theme :where(.lesson-video){border-radius:20px;overflow:hidden;box-shadow:0 18px 34px rgba(15,23,42,.08)}
.slide-theme :where(.glass){backdrop-filter:blur(14px);background:rgba(255,255,255,.72)}
.slide-theme :where(.slide-render){animation:lessonFadeIn .38s ease both}
@keyframes lessonFadeIn{
    from{opacity:0;transform:translateY(10px) scale(.985);filter:blur(2px)}
    to{opacity:1;transform:translateY(0) scale(1);filter:blur(0)}
}
@media (prefers-reduced-motion: reduce){
    .slide-theme :where(.slide-render){animation:none}
}
@media (max-width: 900px){
    .slide-theme :where(.lesson-split),
    .slide-theme :where(.lesson-grid-cards){
        grid-template-columns:1fr;
        width:100%;
        max-width:none;
        min-height:auto;
    }
    .slide-theme :where(.lesson-split-card){
        min-height:auto;
        padding:10px;
    }
    .slide-theme :where(.lesson-split-body){
        min-height:auto;
    }
    .slide-theme :where(.lesson-hero-card){
        padding:18px;
        border-radius:22px;
    }
    .slide-theme :where(.lesson-card){
        padding:14px;
        border-radius:18px;
    }
    .slide-theme :where(.lesson-image){
        min-height:180px;
    }
    .slide-theme :where(.lesson-code-frame){
        min-height:280px;
        border-radius:16px;
    }
    .slide-theme :where(.lesson-slide-title){
        font-size:clamp(24px,8vw,36px);
    }
    .slide-theme :where(.lesson-slide-subtitle){
        font-size:16px;
    }
}
@media (prefers-color-scheme: dark){
    .slide-theme{
        background:
            radial-gradient(circle at top left, rgba(56,189,248,.10), transparent 32%),
            radial-gradient(circle at top right, rgba(255,255,255,.06), transparent 30%),
            linear-gradient(180deg, #07111f 0%, #0f172a 100%);
        color:#e5eefb;
        --theme-border:rgba(125,211,252,.16);
        --theme-surface:rgba(15,23,42,.72);
        --theme-card:rgba(15,23,42,.9);
    }
    .slide-theme :where(h1,h2,h3,h4,h5,h6){color:#f8fafc}
    .slide-theme :where(p,li,div,span){color:#cbd5e1}
    .slide-theme :where(strong,b){color:inherit}
    .slide-theme :where(code,pre,kbd,samp){background:#020617;color:#f8fafc}
    .slide-theme :where(table){background:rgba(15,23,42,.88)}
    .slide-theme :where(th){background:linear-gradient(180deg,#1e293b,#0f172a);color:#f8fafc}
    .slide-theme :where(td,th){border-color:rgba(125,211,252,.14)}
    .slide-theme :where(.card,.sqz-wrap,.dc-q,.dc-review-card,.builder-panel,.lesson-builder-top,.builder-left,.builder-center,.builder-right,.sqz-qcard,.lesson-card){background:linear-gradient(180deg,rgba(15,23,42,.96),rgba(2,6,23,.86))}
    .slide-theme :where(.lesson-hero-card,.lesson-card){background:linear-gradient(180deg,rgba(15,23,42,.96),rgba(2,6,23,.86))}
}
