<style>
    /* Dark coffee palette (same colours as the Build Functional System inventory).
       Change these variables to re-theme the whole inventory page. */
    .inv {
        --bg: #12090a;
        --surface: #1e1209;
        --card: #271910;
        --card2: #31201a;
        --border: #4a3020;
        --amber: #d97706;
        --amber-light: #f59e0b;
        --amber-dim: #92400e;
        --text: #f5ede0;
        --muted: #a08060;
        --dim: #6b4d30;
        --green: #16a34a;
        --red: #dc2626;
        --blue: #2563eb;

        min-height: 100vh;
        padding: 20px 24px 40px;
        background: var(--bg);
        color: var(--text);
        font-size: 14px;
    }

    .inv * { box-sizing: border-box; }
    .inv h2, .inv h3, .inv p { margin: 0; }
    .inv .mono { font-family: ui-monospace, 'JetBrains Mono', Consolas, monospace; }
    .inv a { text-decoration: none; }

    /* Alerts / flashes */
    .inv-alert { display: flex; gap: 12px; align-items: flex-start; padding: 12px 16px; margin-bottom: 16px; border-radius: 12px; }
    .inv-alert-low { background: #7c2d1220; border: 1px solid #dc262640; }
    .inv-alert-icon { font-size: 18px; }
    .inv-alert-title { font-size: 13px; font-weight: 700; color: #fca5a5; }
    .inv-alert-text { margin-top: 2px; font-size: 12px; color: var(--muted); }
    .inv-flash { padding: 11px 16px; margin-bottom: 16px; border-radius: 8px; font-size: 13px; }
    .inv-flash-ok { background: #16a34a18; border: 1px solid #16a34a55; color: #4ade80; }
    .inv-flash-err { background: #dc262618; border: 1px solid #dc262655; color: #fca5a5; }
    .inv-flash ul { margin: 6px 0 0; padding-left: 20px; }

    /* Tabs */
    .inv-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
    .inv-tab { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--muted); background: var(--card); border: 1px solid var(--border); transition: .15s; }
    .inv-tab:hover { color: var(--text); }
    .inv-tab.is-active { background: var(--amber); border-color: var(--amber); color: #1a0900; }

    /* Generic pieces */
    .inv-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 16px; }
    .inv-title { font-size: 20px; font-weight: 700; color: var(--amber-light); }
    .inv-sub { margin-top: 3px; font-size: 12px; color: var(--muted); }
    .inv-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
    .inv-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; }
    .inv-cards { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
    .inv-stat { padding: 12px 16px; min-width: 150px; }
    .inv-stat-label { font-size: 12px; color: var(--muted); margin-bottom: 2px; }
    .inv-stat-value { font-size: 17px; font-weight: 700; color: var(--amber-light); }

    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 13px; border-radius: 8px; border: 1px solid var(--border); background: var(--card2); color: var(--text); font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit; }
    .btn:hover { filter: brightness(1.15); }
    .btn:disabled { opacity: .4; cursor: not-allowed; }
    .btn-primary { background: var(--amber); border-color: var(--amber); color: #1a0900; font-weight: 700; }
    .btn-blue { background: var(--blue); border-color: var(--blue); color: #fff; font-weight: 700; }
    .btn-ghost { color: var(--muted); }
    .btn-danger { background: #dc262622; border-color: #dc262640; color: #fca5a5; }
    .btn-ok { background: #16a34a22; border-color: #16a34a55; color: #4ade80; }
    .badge { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 10px; font-weight: 700; white-space: nowrap; }
    .badge-ok { background: #16a34a22; color: #4ade80; }
    .badge-low { background: #dc262622; color: #fca5a5; }
    .badge-off { background: var(--card2); color: var(--muted); }

    .inv input[type=text], .inv input[type=number], .inv input[type=date], .inv select, .inv textarea {
        width: 100%; padding: 8px 10px; border-radius: 8px; border: 1px solid var(--border);
        background: var(--surface); color: var(--text); font-size: 13px; font-family: inherit; outline: none;
    }
    .inv input:focus, .inv select:focus, .inv textarea:focus { border-color: var(--amber); }
    .inv input[readonly] { opacity: .55; cursor: not-allowed; }
    .inv label.lbl { display: block; margin-bottom: 4px; font-size: 12px; font-weight: 600; color: var(--amber-light); }

    /* Tables */
    .inv-table-wrap { overflow-x: auto; }
    .inv-table { width: 100%; border-collapse: collapse; }
    .inv-table th { padding: 9px 10px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dim); background: var(--card2); border-bottom: 1px solid var(--border); }
    .inv-table td { padding: 8px 10px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
    .inv-table tr:last-child td { border-bottom: 0; }
    .inv-table .num { text-align: center; }
    .inv-table.is-stock th, .inv-table.is-stock td { border: 1px solid var(--border); padding: 7px 8px; font-size: 12px; }
    .inv-row-click { cursor: pointer; }
    .inv-row-click:hover td { background: #ffffff08; }
    .inv-row-inactive { opacity: .6; }
    .stock-input { width: 100px !important; padding: 5px 6px !important; font-size: 12px !important; }
    .cell-input { max-width: 110px; margin: 0 auto; display: block; text-align: center; font-family: ui-monospace, Consolas, monospace; }

    /* Daily sheet */
    .step-card { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 16px; margin-bottom: 16px; }
    .step-left { display: flex; align-items: center; gap: 12px; }
    .step-pill { padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
    .step-pill.s1 { background: #2563eb30; color: #93c5fd; }
    .step-pill.s2 { background: #14532d40; color: #86efac; }
    .area { margin-bottom: 24px; }
    .area-head { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .area-title { font-size: 15px; font-weight: 800; }
    .area-count { font-size: 11px; color: var(--dim); }
    .group { border-radius: 14px; overflow: hidden; margin-bottom: 12px; border: 1px solid var(--border); background: var(--card); }
    .group > summary { list-style: none; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 11px 18px; cursor: pointer; font-weight: 700; font-size: 13px; }
    .group > summary::-webkit-details-marker { display: none; }
    .group > summary .chev { font-size: 10px; transition: transform .15s; }
    .group[open] > summary .chev { transform: rotate(90deg); }
    .group-meta { font-size: 11px; font-weight: 500; color: var(--muted); }
    .out-pos { color: var(--amber-light); font-weight: 700; }
    .out-neg { color: #fca5a5; font-weight: 700; }
    .hist-chip { padding: 6px 10px; border-radius: 8px; font-size: 12px; background: var(--card2); color: var(--amber); border: 1px solid var(--border); }
    .hist-chip.is-active { background: var(--amber); color: #1a0900; }

    /* Suppliers */
    .sup-grid { display: grid; gap: 16px; grid-template-columns: 320px 1fr; align-items: start; }
    .sup-item { display: block; width: 100%; text-align: left; padding: 12px 14px; border-radius: 12px; background: var(--card); border: 1px solid var(--border); color: var(--text); }
    .sup-item.is-active { border-color: var(--amber); background: var(--card2); }
    .sup-name { font-weight: 700; font-size: 13px; }
    .sup-meta { margin-top: 2px; font-size: 11px; color: var(--muted); }
    .kv { display: grid; grid-template-columns: 140px 1fr; gap: 6px 12px; font-size: 13px; }
    .kv dt { color: var(--muted); }
    .kv dd { margin: 0; }
    .chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .chip { padding: 3px 9px; border-radius: 999px; font-size: 11px; background: var(--card2); border: 1px solid var(--border); color: var(--text); }
    .form-grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
    .check-grid { display: grid; gap: 6px; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); max-height: 260px; overflow-y: auto; padding: 4px; }
    .check-grid label { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text); }
    .inv .check-grid input[type=checkbox] { width: auto; }

    /* Spoilage */
    .spoil-grid { display: grid; gap: 16px; grid-template-columns: 340px 1fr; align-items: start; }

    /* Modal */
    .inv-modal { display: none; position: fixed; inset: 0; z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
    .inv-modal.is-open { display: flex; }
    .inv-modal-bg { position: absolute; inset: 0; background: rgba(0, 0, 0, .68); }
    .inv-modal-box { position: relative; width: 100%; max-width: 440px; max-height: 92vh; overflow-y: auto; border-radius: 14px; background: var(--card); border: 1px solid var(--border); box-shadow: 0 16px 48px rgba(0, 0, 0, .5); color: var(--text); }
    .inv-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 16px 20px; border-bottom: 1px solid var(--border); }
    .inv-modal-head h3 { font-size: 18px; font-weight: 700; }
    .inv-modal-head p { margin-top: 4px; font-size: 12px; color: var(--muted); }
    .inv-modal-x { border: 0; background: transparent; color: var(--muted); font-size: 26px; line-height: 1; cursor: pointer; }
    .inv-modal-body { display: flex; flex-direction: column; gap: 14px; padding: 20px; }
    .modal-sep { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-top: 14px; border-top: 1px solid var(--border); }

    .inv-pager { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 14px; font-size: 12px; color: var(--muted); }
    .inv-pager-btns { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .empty { padding: 32px; text-align: center; color: var(--dim); }

    @media (max-width: 900px) {
        .sup-grid, .spoil-grid { grid-template-columns: 1fr; }
        .inv { padding: 14px; }
        .inv-table { min-width: 720px; }
    }
</style>
