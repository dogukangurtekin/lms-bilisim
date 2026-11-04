<style>
    .att{display:grid;gap:14px}
    .att .top{display:flex;flex-wrap:wrap;gap:10px 14px;align-items:center;justify-content:space-between}
    .att .top h1{margin:0}
    .att-tools{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .att-tabs{display:flex;gap:6px;flex-wrap:wrap}
    .att-tab{padding:9px 16px;border-radius:12px;border:1px solid var(--app-border,#dbe4ee);background:var(--app-panel,#fff);font-weight:800;text-decoration:none;color:var(--app-text,#16182b)}
    .att-tab.on{background:linear-gradient(135deg,#5b3df5,#7c5cff);color:#fff;border-color:transparent;box-shadow:0 8px 18px rgba(91,61,245,.25)}
    .att-card{padding:16px;border:1px solid var(--app-border,#dbe4ee);border-radius:16px;background:var(--app-panel,#fff);box-shadow:0 2px 8px rgba(22,24,43,.05);min-width:0}
    .att-card h3{margin:0 0 10px;font-size:16px}
    .att-chips{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px}
    .att-chip{padding:12px 14px;border-radius:14px;border:1px solid var(--app-border,#dbe4ee);background:linear-gradient(160deg,#fff,#f1f6ff)}
    .att-chip strong{display:block;font-size:26px;line-height:1.1;color:#1e293b}
    .att-chip small{color:#64748b;font-weight:700}
    .att-chip.bad strong{color:#b91c1c}.att-chip.ok strong{color:#15803d}.att-chip.live strong{color:#1d4ed8}
    .att-badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:800;white-space:nowrap}
    .st-upcoming{background:#f1f5f9;color:#475569}.st-ongoing{background:#dbeafe;color:#1d4ed8}
    .st-closed{background:#dcfce7;color:#166534}.st-pending{background:#fef3c7;color:#92400e}
    .st-present{background:#dcfce7;color:#166534}.st-absent{background:#fee2e2;color:#b91c1c}.st-excused{background:#fef3c7;color:#92400e}
    .att-late{margin-left:6px;font-size:11px;font-weight:800;color:#b45309}
    .att-ok{color:#15803d;font-weight:800}.att-bad{color:#b91c1c;font-weight:800}
    .att-bar{height:8px;border-radius:99px;background:#e2e8f0;overflow:hidden;min-width:70px}
    .att-bar i{display:block;height:100%;background:linear-gradient(90deg,#22c55e,#16a34a);border-radius:99px}
    .att-table{width:100%;border-collapse:collapse;font-size:14px}
    .att-table th{background:#f1f5f9;color:#334155;font-size:12.5px;text-align:left;padding:9px 10px;white-space:nowrap}
    .att-table td{padding:9px 10px;border-bottom:1px solid #eef2f7;vertical-align:middle}
    .att-table tr:hover td{background:#fafcff}
    .att-wrap{overflow:auto;border-radius:12px}
    .att-empty{padding:22px;text-align:center;color:#64748b;border:1.5px dashed #cbd5e1;border-radius:16px;background:#f8fafc;line-height:1.6}
    .att-flash{padding:11px 14px;border-radius:12px;font-weight:700}
    .att-flash.ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
    .att-flash.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
    .att-errors{margin:8px 0 0;padding-left:18px;font-size:13.5px;line-height:1.55;color:#7f1d1d}
    .att-filter{display:flex;gap:10px;flex-wrap:wrap;align-items:end}
    .att-filter label{display:block;font-size:12.5px;font-weight:800;margin-bottom:4px;color:#475569}
    .att-filter input,.att-filter select,.att-form input,.att-form select{margin:0;padding:9px 10px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;font:inherit;min-width:0}
    .att-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;align-items:end}
    .att-form label{display:block;font-size:12.5px;font-weight:800;margin-bottom:4px;color:#475569}
    .att-form input,.att-form select{width:100%}
    .att-grid2{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:14px;align-items:start}
    .att-names{margin:6px 0 0;padding:0;list-style:none;display:flex;flex-wrap:wrap;gap:6px}
    .att-names li{background:#fee2e2;color:#991b1b;border-radius:999px;padding:3px 10px;font-size:12.5px;font-weight:700}
    .att-names.wait li{background:#fef3c7;color:#92400e}
    .att-day{margin:0 0 6px;font-size:14px;font-weight:900;color:#334155}
    .att-select{padding:6px 8px;border-radius:8px;border:1px solid #cbd5e1;font:inherit;font-size:13px}
    @media (max-width:900px){.att-grid2{grid-template-columns:1fr}}
</style>
