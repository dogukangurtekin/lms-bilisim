<style>
.rp-actions{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
.rp-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-top:14px}
.rp-kpi{padding:12px 14px;border:1px solid var(--cx-line);border-radius:14px;background:rgba(255,255,255,.06)}
.rp-kpi small{display:block;color:var(--cx-mute);font-weight:700;font-size:12px}
.rp-kpi strong{display:block;font-size:24px;color:#fff;margin-top:2px}
.rp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr));gap:14px}
.rp-bar{display:grid;grid-template-columns:minmax(80px,150px) minmax(0,1fr) auto;gap:8px;align-items:center;margin:6px 0;font-size:13px}
.rp-bar .nm{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#e2e8f0}
.rp-bar .tr{height:14px;border-radius:99px;background:rgba(148,163,184,.2);overflow:hidden}
.rp-bar .fl{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#48dbfb,#54a0ff)}
.rp-bar .fl.g{background:linear-gradient(90deg,#1dd1a1,#10b981)}
.rp-bar .fl.o{background:linear-gradient(90deg,#feca57,#ff9f43)}
.rp-bar .v{color:#fff;font-weight:800;min-width:44px;text-align:right}
.rp-hist{display:flex;align-items:flex-end;gap:10px;height:170px;padding-top:8px}
.rp-hist .col{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;font-size:12px;color:var(--cx-mute)}
.rp-hist .b{width:100%;max-width:56px;border-radius:8px 8px 0 0;background:linear-gradient(180deg,#a29bfe,#54a0ff);min-height:2px}
.rp-hist .n{color:#fff;font-weight:800;margin-bottom:3px}
.rp-donut{width:150px;height:150px;border-radius:50%;display:grid;place-items:center;margin:0 auto;background:conic-gradient(#1dd1a1 calc(var(--p,0) * 1%), rgba(148,163,184,.3) 0)}
.rp-donut span{display:grid;place-items:center;width:104px;height:104px;border-radius:50%;background:#0f172a;color:#fff;font-size:26px;font-weight:800}
.rp-leg{text-align:center;color:var(--cx-mute);font-size:13px;margin-top:8px}
.rp-table{width:100%;border-collapse:collapse;font-size:14px;background:transparent}
.rp-table th,.rp-table td{padding:8px 10px;border-bottom:1px solid var(--cx-line);text-align:left;color:#e2e8f0}
.rp-table th{color:var(--cx-mute);font-size:12px}
.rp-ok{color:#1dd1a1;font-weight:800}.rp-no{color:#feca57;font-weight:800}
@media print{
  body{background:#fff!important}.sidebar,.navbar,.rp-actions,header{display:none!important}
  .cx{background:#fff!important;color:#0f172a;border:0;padding:0}.cx::before{display:none}
  .cx h1,.cx h2,.cx h3,.rp-kpi strong,.rp-bar .v,.rp-hist .n{color:#0f172a!important}
  .cx p,.rp-kpi small,.rp-table th,.rp-table td,.rp-bar .nm{color:#334155!important}
  .rp-donut span{background:#fff;color:#0f172a}
  .rp-kpi,.cx-panel{box-shadow:none;background:#fff;border-color:#cbd5e1;break-inside:avoid}
}
</style>
