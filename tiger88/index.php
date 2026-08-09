<?php
require_once __DIR__ . '/auth.php';
require_login();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🐯 THE TIGER 88 — รายงานค่าใช้จ่าย</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js" crossorigin="anonymous"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap');

:root {
  --tiger: #d97706;
  --tiger-dark: #92400e;
  --tiger-light: #fef3c7;
  --red: #dc2626;
  --green: #059669;
  --bg: #faf7f2;
  --card: #fff;
  --border: #e8dfd0;
  --text: #1c1917;
  --text2: #78716c;
  --radius: 14px;
  --shadow: 0 2px 12px rgba(0,0,0,0.08);
}

* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Sarabun', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

/* HEADER */
.header {
  background: linear-gradient(135deg, #92400e 0%, #d97706 60%, #f59e0b 100%);
  padding: 0 24px;
  display: flex; align-items: center; justify-content: space-between;
  height: 60px; position: sticky; top: 0; z-index: 100;
  box-shadow: 0 2px 16px rgba(146,64,14,0.3);
}
.header-logo { color: white; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
.header-logo span { font-size: 13px; font-weight: 400; opacity: 0.8; }
.header-actions { display: flex; gap: 8px; align-items: center; }
.sync-btn {
  background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
  color: white; padding: 6px 14px; border-radius: 8px; cursor: pointer;
  font-family: inherit; font-size: 13px; transition: all 0.2s;
}
.sync-btn:hover { background: rgba(255,255,255,0.25); }
.sync-status { color: rgba(255,255,255,0.7); font-size: 11px; }
.hidden { display: none !important; }

/* NAV TABS */
.nav-tabs { background: var(--card); border-bottom: 1px solid var(--border); padding: 0 24px; display: flex; gap: 0; overflow-x: auto; }
.nav-tab {
  padding: 14px 20px; cursor: pointer; font-size: 14px; font-weight: 500;
  color: var(--text2); border-bottom: 3px solid transparent;
  white-space: nowrap; transition: all 0.2s; background: none; border-top: none; border-left: none; border-right: none;
  font-family: inherit;
}
.nav-tab.active { color: var(--tiger); border-bottom-color: var(--tiger); font-weight: 600; }
.nav-tab:hover:not(.active) { color: var(--text); background: var(--bg); }

/* MAIN */
.main { padding: 20px 24px; max-width: 1400px; margin: 0 auto; }
.tab-content { display: none; }
.tab-content.active { display: block; }

/* KPI CARDS */
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px; }
.kpi-card {
  background: var(--card); border-radius: var(--radius); padding: 18px 20px;
  box-shadow: var(--shadow); border: 1px solid var(--border);
}
.kpi-label { font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
.kpi-value { font-size: 26px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.kpi-sub { font-size: 12px; color: var(--text2); }
.kpi-card.highlight { background: linear-gradient(135deg, #92400e, #d97706); color: white; }
.kpi-card.highlight .kpi-label, .kpi-card.highlight .kpi-sub { color: rgba(255,255,255,0.75); }
.kpi-card.highlight .kpi-value { color: white; }

/* CHART GRID */
.chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.chart-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.chart-card {
  background: var(--card); border-radius: var(--radius); padding: 18px 20px;
  box-shadow: var(--shadow); border: 1px solid var(--border);
}
.chart-card.full { grid-column: 1 / -1; }
.chart-title { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 14px; display: flex; align-items: center; gap: 6px; }
.chart-card canvas { max-height: 260px; }

/* FILTERS */
.filter-bar { background: var(--card); border-radius: var(--radius); padding: 14px 18px; margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; box-shadow: var(--shadow); border: 1px solid var(--border); }
.filter-group { display: flex; align-items: center; gap: 6px; }
.filter-group label { font-size: 12px; color: var(--text2); white-space: nowrap; }
.filter-group select, .filter-group input {
  padding: 6px 10px; border: 1px solid var(--border); border-radius: 8px;
  background: var(--bg); font-family: inherit; font-size: 13px; color: var(--text);
  min-width: 120px;
}
.filter-group input[type="text"] { min-width: 180px; }
.filter-clear { margin-left: auto; padding: 6px 14px; background: var(--tiger-light); color: var(--tiger-dark); border: 1px solid #fcd34d; border-radius: 8px; cursor: pointer; font-size: 12px; font-family: inherit; font-weight: 500; }

/* TABLE */
.table-card { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; }
.table-header { padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); }
.table-header h3 { font-size: 14px; font-weight: 600; }
.table-actions { display: flex; gap: 8px; }
.btn { padding: 7px 14px; border-radius: 8px; cursor: pointer; font-size: 13px; font-family: inherit; font-weight: 500; border: none; transition: all 0.2s; }
.btn-primary { background: var(--tiger); color: white; }
.btn-primary:hover { background: var(--tiger-dark); }
.btn-secondary { background: var(--bg); color: var(--text); border: 1px solid var(--border); }
.btn-secondary:hover { background: var(--border); }
.btn-green { background: #059669; color: white; }
.btn-green:hover { background: #047857; }
.btn-sm { padding: 4px 10px; font-size: 12px; }

.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead th { background: #faf7f2; padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; color: var(--text2); text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; border-bottom: 1px solid var(--border); cursor: pointer; user-select: none; }
thead th:hover { color: var(--tiger); }
thead th.sorted { color: var(--tiger); }
tbody td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #f5f0e8; }
tbody tr:hover { background: #faf7f2; }
tbody tr:last-child td { border-bottom: none; }
.amount { font-weight: 600; color: var(--red); }
.badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-orange { background: #fef3c7; color: #92400e; }
.badge-blue { background: #dbeafe; color: #1d4ed8; }
.badge-green { background: #dcfce7; color: #15803d; }
.badge-purple { background: #f3e8ff; color: #7e22ce; }
.badge-red { background: #fee2e2; color: #b91c1c; }

.table-footer { padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border); font-size: 12px; color: var(--text2); }
.pagination { display: flex; gap: 4px; }
.page-btn { padding: 4px 10px; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; background: var(--card); font-size: 12px; font-family: inherit; }
.page-btn.active { background: var(--tiger); color: white; border-color: var(--tiger); }
.page-btn:hover:not(.active) { background: var(--bg); }

/* ADD FORM MODAL */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 200; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal { background: var(--card); border-radius: 18px; padding: 24px; width: 90%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; color: var(--tiger-dark); }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group.full { grid-column: 1 / -1; }
.form-group label { font-size: 12px; font-weight: 600; color: var(--text2); }
.form-group input, .form-group select, .form-group textarea {
  padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 10px;
  font-family: inherit; font-size: 14px; color: var(--text); background: var(--bg);
  transition: border-color 0.2s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
  outline: none; border-color: var(--tiger); background: white;
}
.form-group textarea { resize: vertical; min-height: 70px; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

/* EXPORT SECTION */
.export-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 20px; }
.export-card { background: var(--card); border-radius: var(--radius); padding: 20px 22px; box-shadow: var(--shadow); border: 1px solid var(--border); }
.export-card h3 { font-size: 15px; font-weight: 600; margin-bottom: 6px; }
.export-card p { font-size: 13px; color: var(--text2); margin-bottom: 16px; line-height: 1.5; }
.export-options { display: flex; flex-direction: column; gap: 10px; }
.export-option { display: flex; align-items: center; gap: 8px; }
.export-option label { font-size: 13px; color: var(--text2); }
.export-option select { padding: 5px 8px; border: 1px solid var(--border); border-radius: 6px; font-family: inherit; font-size: 12px; }

/* TOAST */
.toast { position: fixed; bottom: 24px; right: 24px; background: #1c1917; color: white; padding: 12px 20px; border-radius: 10px; font-size: 14px; z-index: 9999; opacity: 0; transform: translateY(8px); transition: all 0.3s; pointer-events: none; }
.toast.show { opacity: 1; transform: translateY(0); }

/* RESPONSIVE */
@media (max-width: 768px) {
  .main { padding: 14px 14px; }
  .chart-grid { grid-template-columns: 1fr; }
  .chart-grid-3 { grid-template-columns: 1fr; }
  .form-grid { grid-template-columns: 1fr; }
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .header { padding: 0 14px; }
}

/* SUMMARY TAB */
.summary-month-header { font-size: 16px; font-weight: 700; color: var(--tiger-dark); padding: 12px 0 8px; border-bottom: 2px solid var(--tiger-light); margin-bottom: 12px; }
.payer-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.loading { text-align: center; padding: 40px; color: var(--text2); font-size: 14px; }
</style>
</head>
<body>

<div class="header">
  <div class="header-logo">🐯 <div><div>เสือกินเส้น 88</div><span>ระบบรายงานค่าใช้จ่าย</span></div></div>
  <div class="header-actions">
    <span class="sync-status" id="syncStatus">☁️ เชื่อมต่อฐานข้อมูล</span>
    <button class="sync-btn" onclick="syncFromSheet()">🔄 Sync Sheet</button>
    <button class="sync-btn" onclick="openAddModal()">+ เพิ่มรายการ</button>
    <a href="logout.php" class="sync-btn" style="text-decoration:none;display:inline-block">🚪 ออกจากระบบ</a>
  </div>
</div>

<div id="loadingOverlay" style="position:fixed;inset:0;background:rgba(250,247,242,0.9);z-index:500;display:flex;align-items:center;justify-content:center;font-size:15px;color:var(--tiger-dark)">
  ⏳ กำลังโหลดข้อมูล...
</div>

<div class="nav-tabs">
  <button class="nav-tab active" onclick="switchTab('dashboard', this)">📊 Dashboard</button>
  <button class="nav-tab" onclick="switchTab('expenses', this)">📋 รายการค่าใช้จ่าย</button>
  <button class="nav-tab" onclick="switchTab('breakdown', this)">👥 แยกตามผู้จ่าย</button>
  <button class="nav-tab" onclick="switchTab('supply', this)">🥩 หมวดวัตถุดิบ</button>
  <button class="nav-tab" onclick="switchTab('fixcost', this)">🏢 ค่าใช้จ่ายคงที่</button>
  <button class="nav-tab" onclick="switchTab('export', this)">📤 Export</button>
</div>

<!-- ========== DASHBOARD TAB ========== -->
<div id="tab-dashboard" class="tab-content active">
<div class="main">

  <!-- Filter bar for dashboard -->
  <div class="filter-bar" style="margin-bottom:16px">
    <div class="filter-group">
      <label>เดือน</label>
      <select id="dash-month" onchange="renderDashboard()">
        <option value="all">ทุกเดือน</option>
        <option>มกราคม</option><option>กุมภาพันธ์</option><option>มีนาคม</option>
        <option>เมษายน</option><option>พฤษภาคม</option><option>มิถุนายน</option>
        <option>กรกฎาคม</option><option>สิงหาคม</option>
      </select>
    </div>
    <div class="filter-group">
      <label>ผู้จ่าย</label>
      <select id="dash-payer" onchange="renderDashboard()">
        <option value="all">ทุกคน</option>
        <option>รมณ</option><option>ฝรั่ง</option><option>พี่คิว</option><option>พ่อ</option>
      </select>
    </div>
  </div>

  <div class="kpi-grid" id="kpi-grid"></div>

  <div class="chart-grid">
    <div class="chart-card">
      <div class="chart-title">📈 ค่าใช้จ่ายรายเดือน</div>
      <canvas id="chartMonthly"></canvas>
    </div>
    <div class="chart-card">
      <div class="chart-title">🍩 สัดส่วนหมวดหมู่</div>
      <canvas id="chartCategory"></canvas>
    </div>
    <div class="chart-card">
      <div class="chart-title">👥 ค่าใช้จ่ายตามผู้จ่าย</div>
      <canvas id="chartPayer"></canvas>
    </div>
    <div class="chart-card">
      <div class="chart-title">📅 Top 10 รายการใหญ่สุด</div>
      <canvas id="chartTop10"></canvas>
    </div>
  </div>

</div>
</div>

<!-- ========== EXPENSES TAB ========== -->
<div id="tab-expenses" class="tab-content">
<div class="main">

  <div class="filter-bar">
    <div class="filter-group">
      <label>ค้นหา</label>
      <input type="text" id="searchInput" placeholder="ชื่อรายการ / ผู้ขาย..." oninput="applyFilters()">
    </div>
    <div class="filter-group">
      <label>เดือน</label>
      <select id="filterMonth" onchange="applyFilters()">
        <option value="">ทุกเดือน</option>
        <option>มกราคม</option><option>กุมภาพันธ์</option><option>มีนาคม</option>
        <option>เมษายน</option><option>พฤษภาคม</option><option>มิถุนายน</option>
        <option>กรกฎาคม</option><option>สิงหาคม</option>
      </select>
    </div>
    <div class="filter-group">
      <label>หมวดหมู่</label>
      <select id="filterCat" onchange="applyFilters()">
        <option value="">ทุกหมวด</option>
        <option>ต้นทุนสินค้า</option><option>fixcos</option><option>บุคลากร & ค่าจ้าง</option>
        <option>การตลาด & โฆษณา</option><option>ไอที & ซอฟต์แวร์</option>
        <option>อาหาร & รับรอง</option><option>เดินทาง</option>
        <option>การเงิน</option><option>รายจ่ายอื่นๆ</option>
        <option>ตลาด</option><option>ไม่แน่ใจ</option>
      </select>
    </div>
    <div class="filter-group">
      <label>ผู้จ่าย</label>
      <select id="filterPayer" onchange="applyFilters()">
        <option value="">ทุกคน</option>
        <option>รมณ</option><option>ฝรั่ง</option><option>พี่คิว</option><option>พ่อ</option>
      </select>
    </div>
    <button class="filter-clear" onclick="clearFilters()">✕ ล้าง</button>
  </div>

  <div class="table-card">
    <div class="table-header">
      <h3>📋 รายการค่าใช้จ่าย (<span id="rowCount">-</span> รายการ | รวม <span id="rowTotal" style="color:var(--red)">-</span>)</h3>
      <div class="table-actions">
        <button class="btn btn-secondary btn-sm" onclick="switchTab('export', null)">📤 Export</button>
        <button class="btn btn-primary btn-sm" onclick="openAddModal()">+ เพิ่มรายการ</button>
      </div>
    </div>
    <div class="table-wrap">
      <table id="expenseTable">
        <thead>
          <tr>
            <th onclick="sortBy('d')" id="th-d">วันที่ ↕</th>
            <th onclick="sortBy('r')" id="th-r">รายการ ↕</th>
            <th onclick="sortBy('c')" id="th-c">หมวดหมู่ ↕</th>
            <th onclick="sortBy('v')" id="th-v">ผู้ขาย ↕</th>
            <th onclick="sortBy('payer')" id="th-payer">ผู้จ่าย ↕</th>
            <th onclick="sortBy('a')" id="th-a" style="text-align:right">จำนวนเงิน ↕</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody id="tableBody"></tbody>
      </table>
    </div>
    <div class="table-footer">
      <span id="pageInfo">-</span>
      <div class="pagination" id="pagination"></div>
    </div>
  </div>

</div>
</div>

<!-- ========== BREAKDOWN TAB ========== -->
<div id="tab-breakdown" class="tab-content">
<div class="main">

  <div class="filter-bar">
    <div class="filter-group">
      <label>เดือน</label>
      <select id="bd-month" onchange="renderBreakdown()">
        <option value="all">ทุกเดือน</option>
        <option>มกราคม</option><option>กุมภาพันธ์</option><option>มีนาคม</option>
        <option>เมษายน</option><option>พฤษภาคม</option><option>มิถุนายน</option>
        <option>กรกฎาคม</option><option>สิงหาคม</option>
      </select>
    </div>
  </div>

  <div class="kpi-grid" id="bd-payer-kpis"></div>

  <div class="chart-grid" style="margin-bottom:20px">
    <div class="chart-card">
      <div class="chart-title">👥 สัดส่วนค่าใช้จ่ายตามผู้จ่าย</div>
      <canvas id="chartBdPayer"></canvas>
    </div>
    <div class="chart-card">
      <div class="chart-title">📊 หมวดหมู่ x ผู้จ่าย (Stacked)</div>
      <canvas id="chartBdStacked"></canvas>
    </div>
  </div>

  <div class="table-card">
    <div class="table-header">
      <h3>📑 ตารางไขว้: หมวดหมู่ × ผู้จ่าย</h3>
      <div class="table-actions">
        <button class="btn btn-green btn-sm" onclick="exportBreakdownExcel()">⬇️ Export ตารางนี้</button>
      </div>
    </div>
    <div class="table-wrap">
      <table id="breakdownTable">
        <thead id="breakdownHead"></thead>
        <tbody id="breakdownBody"></tbody>
      </table>
    </div>
  </div>

  <div style="height:20px"></div>

  <div class="table-card">
    <div class="table-header"><h3>💰 สรุปแยกตามผู้จ่าย (รายละเอียด)</h3></div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ผู้จ่าย</th><th>หมวดหมู่</th><th style="text-align:right">จำนวนรายการ</th><th style="text-align:right">รวม (บาท)</th><th style="text-align:right">% ของยอดผู้จ่าย</th></tr>
        </thead>
        <tbody id="payerDetailBody"></tbody>
      </table>
    </div>
  </div>

</div>
</div>

<!-- ========== SUPPLY CATEGORY TAB ========== -->
<div id="tab-supply" class="tab-content">
<div class="main">

  <div class="filter-bar">
    <div class="filter-group">
      <label>เดือน</label>
      <select id="sup-month" onchange="renderSupply()">
        <option value="all">ทุกเดือน</option>
        <option>มกราคม</option><option>กุมภาพันธ์</option><option>มีนาคม</option>
        <option>เมษายน</option><option>พฤษภาคม</option><option>มิถุนายน</option>
        <option>กรกฎาคม</option><option>สิงหาคม</option>
      </select>
    </div>
    <div class="filter-group">
      <label>ผู้จ่าย</label>
      <select id="sup-payer" onchange="renderSupply()">
        <option value="all">ทุกคน</option>
        <option>รมณ</option><option>ฝรั่ง</option><option>พี่คิว</option><option>พ่อ</option>
      </select>
    </div>
  </div>

  <div class="kpi-grid" id="sup-kpis"></div>

  <div class="chart-grid" style="margin-bottom:20px">
    <div class="chart-card">
      <div class="chart-title">🍩 สัดส่วนหมวดวัตถุดิบ/ซัพพลาย</div>
      <canvas id="chartSupply"></canvas>
    </div>
    <div class="chart-card">
      <div class="chart-title">📈 แนวโน้มรายเดือนตามหมวด</div>
      <canvas id="chartSupplyTrend"></canvas>
    </div>
  </div>

  <div class="table-card">
    <div class="table-header">
      <h3>📑 สรุปหมวดวัตถุดิบ/ซัพพลาย</h3>
      <div class="table-actions">
        <button class="btn btn-green btn-sm" onclick="exportSupplyExcel()">⬇️ Export ตารางนี้</button>
      </div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>หมวดหมู่</th><th style="text-align:right">จำนวนรายการ</th><th style="text-align:right">รวม (บาท)</th><th style="text-align:right">% ของยอดรวม</th><th style="text-align:right">เฉลี่ย/รายการ</th></tr>
        </thead>
        <tbody id="supplySummaryBody"></tbody>
      </table>
    </div>
  </div>

  <div style="height:20px"></div>

  <div class="table-card">
    <div class="table-header">
      <h3>📋 รายการแยกตามหมวด</h3>
    </div>
    <div class="filter-bar" style="border-radius:0;box-shadow:none;border:none;border-bottom:1px solid var(--border)">
      <div class="filter-group">
        <label>กรองหมวด</label>
        <select id="sup-catfilter" onchange="renderSupplyTable()">
          <option value="">ทุกหมวด</option>
          <option>ตลาด</option><option>หมูสด</option><option>เนื้อ</option><option>แม็คโคร</option>
          <option>แก๊ส</option><option>แกร็ป</option><option>ช้อปปี้</option><option>ไทยน้ำทิพย์</option>
          <option>วัสดุสิ้นเปลือง</option><option>รายจ่ายอื่นๆ</option>
        </select>
      </div>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>วันที่</th><th>รายการ</th><th>หมวดวัตถุดิบ</th><th>ผู้ขาย</th><th>ผู้จ่าย</th><th style="text-align:right">จำนวนเงิน</th><th>จัดการ</th></tr></thead>
        <tbody id="supplyTableBody"></tbody>
      </table>
    </div>
  </div>

  <div style="height:20px"></div>

  <div class="table-card">
    <div class="table-header">
      <h3>📅 แยกรายเดือน: หมวดวัตถุดิบ × เดือน</h3>
      <div class="table-actions">
        <button class="btn btn-green btn-sm" onclick="exportSupplyMonthlyExcel()">⬇️ Export ตารางนี้</button>
      </div>
    </div>
    <div class="table-wrap">
      <table id="supplyMonthlyTable">
        <thead id="supplyMonthlyHead"></thead>
        <tbody id="supplyMonthlyBody"></tbody>
      </table>
    </div>
  </div>

</div>
</div>

<!-- ========== FIXED COST TAB ========== -->
<div id="tab-fixcost" class="tab-content">
<div class="main">

  <div class="filter-bar">
    <div class="filter-group">
      <label>เดือน</label>
      <select id="fc-month" onchange="renderFixcost()">
        <option value="all">ทุกเดือน</option>
        <option>มกราคม</option><option>กุมภาพันธ์</option><option>มีนาคม</option>
        <option>เมษายน</option><option>พฤษภาคม</option><option>มิถุนายน</option>
        <option>กรกฎาคม</option><option>สิงหาคม</option>
      </select>
    </div>
  </div>

  <div class="kpi-grid" id="fc-kpis"></div>

  <div class="chart-grid" style="margin-bottom:20px">
    <div class="chart-card">
      <div class="chart-title">🍩 สัดส่วนค่าใช้จ่ายคงที่</div>
      <canvas id="chartFixcost"></canvas>
    </div>
    <div class="chart-card">
      <div class="chart-title">📈 แนวโน้มรายเดือน</div>
      <canvas id="chartFixcostTrend"></canvas>
    </div>
  </div>

  <div class="table-card">
    <div class="table-header">
      <h3>📑 สรุปค่าใช้จ่ายคงที่</h3>
      <div class="table-actions">
        <button class="btn btn-green btn-sm" onclick="exportFixcostExcel()">⬇️ Export ตารางนี้</button>
      </div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>หมวดหมู่</th><th style="text-align:right">จำนวนรายการ</th><th style="text-align:right">รวม (บาท)</th><th style="text-align:right">% ของยอดรวม</th><th style="text-align:right">เฉลี่ย/เดือน</th></tr>
        </thead>
        <tbody id="fixcostSummaryBody"></tbody>
      </table>
    </div>
  </div>

  <div style="height:20px"></div>

  <div class="table-card">
    <div class="table-header">
      <h3>📅 แยกรายเดือน: ค่าใช้จ่ายคงที่ × เดือน</h3>
      <div class="table-actions">
        <button class="btn btn-green btn-sm" onclick="exportFixcostMonthlyExcel()">⬇️ Export ตารางนี้</button>
      </div>
    </div>
    <div class="table-wrap">
      <table id="fixcostMonthlyTable">
        <thead id="fixcostMonthlyHead"></thead>
        <tbody id="fixcostMonthlyBody"></tbody>
      </table>
    </div>
  </div>

  <div style="height:20px"></div>

  <div class="table-card">
    <div class="table-header"><h3>📋 รายการแยกตามหมวด</h3></div>
    <div class="filter-bar" style="border-radius:0;box-shadow:none;border:none;border-bottom:1px solid var(--border)">
      <div class="filter-group">
        <label>กรองหมวด</label>
        <select id="fc-catfilter" onchange="renderFixcostTable()">
          <option value="">ทุกหมวด</option>
          <option>ค่าเช่า</option><option>ค่าไฟ</option><option>ค่าน้ำ</option>
          <option>ค่าเนต</option><option>เงินเดือนเด็ก</option><option>อื่นๆ</option>
        </select>
      </div>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>วันที่</th><th>รายการ</th><th>หมวด</th><th>ผู้ขาย</th><th>ผู้จ่าย</th><th style="text-align:right">จำนวนเงิน</th><th>จัดการ</th></tr></thead>
        <tbody id="fixcostTableBody"></tbody>
      </table>
    </div>
  </div>

</div>
</div>

<!-- ========== EXPORT TAB ========== -->
<div id="tab-export" class="tab-content">
<div class="main">

  <div class="export-grid">
    <div class="export-card">
      <h3>📊 Export Excel (.xlsx)</h3>
      <p>ส่งออกข้อมูลค่าใช้จ่ายเป็นไฟล์ Excel พร้อมจัดรูปแบบ แยกตามเดือน และสรุปหมวดหมู่</p>
      <div class="export-options">
        <div class="export-option">
          <label>เดือน:</label>
          <select id="xlsxMonth">
            <option value="all">ทุกเดือน</option>
            <option>มกราคม</option><option>กุมภาพันธ์</option><option>มีนาคม</option>
            <option>เมษายน</option><option>พฤษภาคม</option><option>มิถุนายน</option>
        <option>กรกฎาคม</option><option>สิงหาคม</option>
          </select>
        </div>
        <div class="export-option">
          <label>หมวดหมู่:</label>
          <select id="xlsxCat">
            <option value="all">ทุกหมวด</option>
            <option>ต้นทุนสินค้า</option><option>fixcos</option><option>บุคลากร & ค่าจ้าง</option>
            <option>การตลาด & โฆษณา</option><option>ไอที & ซอฟต์แวร์</option>
            <option>อาหาร & รับรอง</option><option>เดินทาง</option><option>การเงิน</option>
            <option>รายจ่ายอื่นๆ</option>
          </select>
        </div>
      </div>
      <br>
      <button class="btn btn-green" onclick="exportExcel()" style="width:100%">⬇️ ดาวน์โหลด Excel</button>
    </div>

    <div class="export-card">
      <h3>🖨️ พิมพ์ / Export PDF</h3>
      <p>พิมพ์รายงานหรือบันทึกเป็น PDF ผ่านเบราว์เซอร์ (Print → Save as PDF)</p>
      <div class="export-options">
        <div class="export-option">
          <label>เดือน:</label>
          <select id="pdfMonth">
            <option value="all">ทุกเดือน</option>
            <option>มกราคม</option><option>กุมภาพันธ์</option><option>มีนาคม</option>
            <option>เมษายน</option><option>พฤษภาคม</option><option>มิถุนายน</option>
        <option>กรกฎาคม</option><option>สิงหาคม</option>
          </select>
        </div>
      </div>
      <br>
      <button class="btn btn-secondary" onclick="printReport()" style="width:100%">🖨️ พิมพ์รายงาน</button>
    </div>

    <div class="export-card">
      <h3>📋 Export CSV</h3>
      <p>ส่งออกข้อมูลดิบเป็น CSV สำหรับนำไปใช้ใน Google Sheets หรือวิเคราะห์ต่อ</p>
      <div class="export-options">
        <div class="export-option">
          <label>เดือน:</label>
          <select id="csvMonth">
            <option value="all">ทุกเดือน</option>
            <option>มกราคม</option><option>กุมภาพันธ์</option><option>มีนาคม</option>
            <option>เมษายน</option><option>พฤษภาคม</option><option>มิถุนายน</option>
        <option>กรกฎาคม</option><option>สิงหาคม</option>
          </select>
        </div>
      </div>
      <br>
      <button class="btn btn-secondary" onclick="exportCSV()" style="width:100%">⬇️ ดาวน์โหลด CSV</button>
    </div>
  </div>

  <!-- Monthly Summary for PDF -->
  <div class="table-card">
    <div class="table-header">
      <h3>📑 สรุปค่าใช้จ่ายรายเดือน</h3>
    </div>
    <div class="table-wrap">
      <table id="summaryTable">
        <thead>
          <tr>
            <th>เดือน</th>
            <th style="text-align:right">ต้นทุนสินค้า</th>
            <th style="text-align:right">fixcos</th>
            <th style="text-align:right">บุคลากร</th>
            <th style="text-align:right">การตลาด</th>
            <th style="text-align:right">อื่นๆ</th>
            <th style="text-align:right">รวม</th>
            <th style="text-align:right">% ของรวมทั้งหมด</th>
          </tr>
        </thead>
        <tbody id="summaryBody"></tbody>
      </table>
    </div>
  </div>

</div>
</div>

<!-- ADD/EDIT MODAL -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-title">➕ <span id="modalTitle">เพิ่มรายการค่าใช้จ่าย</span></div>
    <div class="form-grid">
      <div class="form-group">
        <label>วันที่ *</label>
        <input type="date" id="f-date" required>
      </div>
      <div class="form-group">
        <label>เดือน *</label>
        <select id="f-month">
          <option>มกราคม</option><option>กุมภาพันธ์</option><option>มีนาคม</option>
          <option>เมษายน</option><option>พฤษภาคม</option><option>มิถุนายน</option>
        <option>กรกฎาคม</option><option>สิงหาคม</option>
          <option>กรกฎาคม</option><option>สิงหาคม</option><option>กันยายน</option>
          <option>ตุลาคม</option><option>พฤศจิกายน</option><option>ธันวาคม</option>
        </select>
      </div>
      <div class="form-group full">
        <label>รายการ / รายละเอียด *</label>
        <textarea id="f-desc" rows="2" placeholder="ระบุรายละเอียดค่าใช้จ่าย..."></textarea>
      </div>
      <div class="form-group">
        <label>จำนวนเงิน (บาท) *</label>
        <input type="number" id="f-amount" min="0" step="0.01" placeholder="0.00">
      </div>
      <div class="form-group">
        <label>ผู้ขาย / ร้านค้า</label>
        <input type="text" id="f-vendor" placeholder="ชื่อผู้ขาย...">
      </div>
      <div class="form-group">
        <label>หมวดหมู่หลัก *</label>
        <select id="f-cat">
          <option>ต้นทุนสินค้า</option>
          <option>fixcos</option>
          <option>บุคลากร & ค่าจ้าง</option>
          <option>การตลาด & โฆษณา</option>
          <option>ไอที & ซอฟต์แวร์</option>
          <option>อาหาร & รับรอง</option>
          <option>เดินทาง</option>
          <option>การเงิน</option>
          <option>รายจ่ายอื่นๆ</option>
          <option>ตลาด</option>
          <option>ไม่แน่ใจ</option>
        </select>
      </div>
      <div class="form-group">
        <label>หมวดหมู่ย่อย</label>
        <input type="text" id="f-subcat" placeholder="เช่น วัตถุดิบ, ค่าไฟ...">
      </div>
      <div class="form-group">
        <label>ผู้จ่าย *</label>
        <select id="f-payer">
          <option>รมณ</option>
          <option>ฝรั่ง</option>
          <option>พี่คิว</option>
          <option>พ่อ</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn btn-secondary" onclick="closeModal()">ยกเลิก</button>
      <button class="btn btn-primary" onclick="saveEntry()">💾 บันทึก</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
// ==================== DATA ====================
// ข้อมูลถูกดึงจากฐานข้อมูลเซิร์ฟเวอร์ผ่าน api.php (ดู apiCall/loadDataFromServer ด้านล่าง)

let data = [];
let dataLoaded = false;

// ==================== API DATA LAYER ====================
// ข้อมูลทั้งหมดอยู่บนฐานข้อมูลเซิร์ฟเวอร์ (MySQL) ผ่าน api.php
// แก้ไข/ลบ/เพิ่ม จะถูกบันทึกถาวรบนเซิร์ฟเวอร์ ไม่ใช่ localStorage อีกต่อไป
async function apiCall(action, payload) {
  const opts = { method: payload ? 'POST' : 'GET', headers: {'Content-Type':'application/json'} };
  if (payload) opts.body = JSON.stringify({ action, ...payload });
  const url = payload ? 'api.php' : `api.php?action=${action}`;
  const resp = await fetch(url, opts);
  if (resp.status === 401) { window.location.href = 'login.php'; throw new Error('Not logged in'); }
  return resp.json();
}

async function loadDataFromServer() {
  document.getElementById('loadingOverlay')?.classList.remove('hidden');
  try {
    const res = await apiCall('list');
    if (res.success) {
      data = res.data;
      dataLoaded = true;
    } else {
      showToast('⚠️ โหลดข้อมูลไม่สำเร็จ: ' + (res.error||''));
    }
  } catch(e) {
    showToast('⚠️ เชื่อมต่อเซิร์ฟเวอร์ไม่ได้');
  }
  document.getElementById('loadingOverlay')?.classList.add('hidden');
}

// ==================== UTILS ====================
const MONTHS = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
const CAT_COLORS = {
  'ต้นทุนสินค้า': '#ef4444', 'fixcos': '#f97316', 'บุคลากร & ค่าจ้าง': '#8b5cf6',
  'การตลาด & โฆษณา': '#3b82f6', 'ไอที & ซอฟต์แวร์': '#06b6d4',
  'อาหาร & รับรอง': '#10b981', 'เดินทาง': '#84cc16',
  'การเงิน': '#f59e0b', 'รายจ่ายอื่นๆ': '#6b7280', 'ตลาด': '#ec4899', 'ไม่แน่ใจ': '#d1d5db'
};
const PAYER_COLORS = { 'รมณ': '#d97706', 'ฝรั่ง': '#3b82f6', 'พี่คิว': '#8b5cf6', 'พ่อ': '#10b981' };

function fmt(n) { return '฿' + Number(n).toLocaleString('th-TH', {minimumFractionDigits:0, maximumFractionDigits:0}); }
function fmtN(n) { return Number(n).toLocaleString('th-TH', {minimumFractionDigits:0}); }
function ddmmyyyy(d) {
  const dt = new Date(d);
  if (isNaN(dt)) return d;
  return ('0'+dt.getDate()).slice(-2)+'/'+('0'+(dt.getMonth()+1)).slice(-2)+'/'+dt.getFullYear();
}
function parseDate(s) {
  if (!s) return new Date(0);
  const p = s.split('/');
  if (p.length === 3) return new Date(+p[2], +p[1]-1, +p[0]);
  return new Date(s);
}

function showToast(msg, dur=2500) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), dur);
}

function catBadge(c) {
  const color = CAT_COLORS[c] || '#6b7280';
  return `<span class="badge" style="background:${color}22;color:${color}">${c}</span>`;
}
function payerBadge(p) {
  const color = PAYER_COLORS[p] || '#6b7280';
  return `<span class="badge" style="background:${color}22;color:${color}">${p||'–'}</span>`;
}

// ==================== TABS ====================
function switchTab(name, el) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  if (el) el.classList.add('active');
  else document.querySelectorAll('.nav-tab').forEach(t => { if (t.onclick?.toString().includes(name)) t.classList.add('active'); });
  if (name === 'dashboard') renderDashboard();
  if (name === 'expenses') applyFilters();
  if (name === 'breakdown') renderBreakdown();
  if (name === 'supply') renderSupply();
  if (name === 'fixcost') renderFixcost();
  if (name === 'export') renderSummaryTable();
}

// ==================== DASHBOARD ====================
let charts = {};

function getFilteredData() {
  const m = document.getElementById('dash-month').value;
  const p = document.getElementById('dash-payer').value;
  return data.filter(e => {
    if (m !== 'all' && e.m !== m) return false;
    if (p !== 'all' && e.payer !== p) return false;
    return true;
  });
}

function renderDashboard() {
  const d = getFilteredData();
  const total = d.reduce((s,e) => s + e.a, 0);
  const count = d.length;
  const avg = count > 0 ? total/count : 0;
  const maxEntry = d.reduce((mx,e) => e.a > mx.a ? e : mx, {a:0,r:'–'});

  document.getElementById('kpi-grid').innerHTML = `
    <div class="kpi-card highlight"><div class="kpi-label">💸 รวมค่าใช้จ่าย</div><div class="kpi-value">${fmt(total)}</div><div class="kpi-sub">${fmtN(count)} รายการ</div></div>
    <div class="kpi-card"><div class="kpi-label">📊 เฉลี่ยต่อรายการ</div><div class="kpi-value">${fmt(avg)}</div><div class="kpi-sub">per transaction</div></div>
    <div class="kpi-card"><div class="kpi-label">🔝 รายการสูงสุด</div><div class="kpi-value">${fmt(maxEntry.a)}</div><div class="kpi-sub" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${maxEntry.r||'–'}</div></div>
    <div class="kpi-card"><div class="kpi-label">📅 จำนวนเดือน</div><div class="kpi-value">${new Set(d.map(e=>e.m)).size}</div><div class="kpi-sub">เดือนที่มีข้อมูล</div></div>
  `;

  // Monthly chart
  const monthlyTotals = {};
  MONTHS.slice(0,8).forEach(m => monthlyTotals[m] = 0);
  d.forEach(e => { if (monthlyTotals[e.m] !== undefined) monthlyTotals[e.m] += e.a; });
  const mlabels = MONTHS.slice(0,8).map(m => m.replace('มกราคม','ม.ค.').replace('กุมภาพันธ์','ก.พ.').replace('มีนาคม','มี.ค.').replace('เมษายน','เม.ย.').replace('พฤษภาคม','พ.ค.').replace('มิถุนายน','มิ.ย.').replace('กรกฎาคม','ก.ค.').replace('สิงหาคม','ส.ค.'));
  const mvalues = MONTHS.slice(0,8).map(m => monthlyTotals[m]);
  renderBarChart('chartMonthly', mlabels, mvalues, '#d97706');

  // Category doughnut
  const catTotals = {};
  d.forEach(e => { catTotals[e.c] = (catTotals[e.c]||0) + e.a; });
  const catSorted = Object.entries(catTotals).sort((a,b) => b[1]-a[1]).slice(0,8);
  renderDoughnutChart('chartCategory', catSorted.map(x=>x[0]), catSorted.map(x=>x[1]), catSorted.map(x=>CAT_COLORS[x[0]]||'#6b7280'));

  // Payer bar
  const payerTotals = {};
  d.forEach(e => { payerTotals[e.payer] = (payerTotals[e.payer]||0) + e.a; });
  const ps = Object.entries(payerTotals).sort((a,b)=>b[1]-a[1]);
  renderBarChart('chartPayer', ps.map(x=>x[0]), ps.map(x=>x[1]), ps.map(x=>PAYER_COLORS[x[0]]||'#6b7280'));

  // Top 10
  const top10 = [...d].sort((a,b)=>b.a-a.a).slice(0,10);
  renderHorizChart('chartTop10', top10.map(e=>e.r.substring(0,25)), top10.map(e=>e.a));
}

function renderBarChart(id, labels, values, color) {
  if (charts[id]) charts[id].destroy();
  const ctx = document.getElementById(id)?.getContext('2d');
  if (!ctx) return;
  const colors = Array.isArray(color) ? color : labels.map(()=>color);
  charts[id] = new Chart(ctx, {
    type: 'bar', data: { labels, datasets: [{ data: values, backgroundColor: colors.map(c=>c+'cc'), borderColor: colors, borderWidth: 1.5, borderRadius: 6 }] },
    options: { responsive:true, plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: ctx => fmt(ctx.raw) } } },
      scales: { y: { ticks:{ callback: v => fmt(v) }, grid:{color:'#f0ede8'} }, x:{grid:{display:false}} } }
  });
}

function renderDoughnutChart(id, labels, values, colors) {
  if (charts[id]) charts[id].destroy();
  const ctx = document.getElementById(id)?.getContext('2d');
  if (!ctx) return;
  charts[id] = new Chart(ctx, {
    type: 'doughnut', data: { labels, datasets: [{ data: values, backgroundColor: colors.map(c=>c+'cc'), borderColor: colors, borderWidth: 2 }] },
    options: { responsive:true, plugins:{ legend:{ position:'right', labels:{boxWidth:12, font:{size:11}} }, tooltip:{ callbacks:{ label: ctx => `${ctx.label}: ${fmt(ctx.raw)} (${(ctx.raw/values.reduce((a,b)=>a+b,0)*100).toFixed(1)}%)` } } } }
  });
}

function renderHorizChart(id, labels, values) {
  if (charts[id]) charts[id].destroy();
  const ctx = document.getElementById(id)?.getContext('2d');
  if (!ctx) return;
  charts[id] = new Chart(ctx, {
    type: 'bar', data: { labels, datasets: [{ data: values, backgroundColor: '#ef444488', borderColor: '#ef4444', borderWidth: 1.5, borderRadius: 4 }] },
    options: { responsive:true, indexAxis:'y', plugins:{ legend:{display:false}, tooltip:{ callbacks:{ label: ctx => fmt(ctx.raw) } } },
      scales: { x:{ ticks:{ callback: v => fmt(v) }, grid:{color:'#f0ede8'} }, y:{grid:{display:false}} } }
  });
}

// ==================== EXPENSES TABLE ====================
let filtered = [...data];
let currentPage = 1;
const PAGE_SIZE = 30;
let sortCol = 'd';
let sortDir = -1; // -1 = desc

function applyFilters() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const month = document.getElementById('filterMonth').value;
  const cat = document.getElementById('filterCat').value;
  const payer = document.getElementById('filterPayer').value;
  filtered = data.filter(e => {
    if (search && !e.r.toLowerCase().includes(search) && !e.v.toLowerCase().includes(search)) return false;
    if (month && e.m !== month) return false;
    if (cat && e.c !== cat) return false;
    if (payer && e.payer !== payer) return false;
    return true;
  });
  currentPage = 1;
  renderTable();
}

function clearFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('filterMonth').value = '';
  document.getElementById('filterCat').value = '';
  document.getElementById('filterPayer').value = '';
  applyFilters();
}

function sortBy(col) {
  if (sortCol === col) sortDir *= -1; else { sortCol = col; sortDir = -1; }
  document.querySelectorAll('thead th').forEach(t => { t.textContent = t.textContent.replace(' ↑','').replace(' ↓','').replace(' ↕','') + ' ↕'; });
  const th = document.getElementById('th-' + col);
  if (th) th.textContent = th.textContent.replace(' ↕', '') + (sortDir === 1 ? ' ↑' : ' ↓');
  filtered.sort((a,b) => {
    let va = col === 'a' ? +a[col] : col === 'd' ? parseDate(a[col]) : (a[col]||'');
    let vb = col === 'a' ? +b[col] : col === 'd' ? parseDate(b[col]) : (b[col]||'');
    if (va < vb) return -sortDir; if (va > vb) return sortDir; return 0;
  });
  renderTable();
}

function renderTable() {
  const total = filtered.reduce((s,e) => s+e.a, 0);
  document.getElementById('rowCount').textContent = fmtN(filtered.length);
  document.getElementById('rowTotal').textContent = fmt(total);

  const start = (currentPage-1)*PAGE_SIZE;
  const page = filtered.slice(start, start+PAGE_SIZE);
  const tbody = document.getElementById('tableBody');
  tbody.innerHTML = page.map(e => `
    <tr>
      <td style="white-space:nowrap">${e.d}</td>
      <td style="max-width:220px"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px" title="${e.r}">${e.r}</div></td>
      <td>${catBadge(e.c)}</td>
      <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:var(--text2)">${e.v||'–'}</td>
      <td>${payerBadge(e.payer)}</td>
      <td class="amount" style="text-align:right;white-space:nowrap">${fmt(e.a)}</td>
      <td><button class="btn btn-secondary btn-sm" onclick="editEntry(${e.id})">✏️</button> <button class="btn btn-secondary btn-sm" onclick="deleteEntry(${e.id})">🗑</button></td>
    </tr>`).join('');

  const totalPages = Math.ceil(filtered.length/PAGE_SIZE);
  document.getElementById('pageInfo').textContent = `แสดง ${start+1}–${Math.min(start+PAGE_SIZE,filtered.length)} จาก ${fmtN(filtered.length)} รายการ`;
  const pag = document.getElementById('pagination');
  pag.innerHTML = '';
  const makeBtn = (n, label) => {
    const b = document.createElement('button');
    b.className = 'page-btn' + (n===currentPage?' active':'');
    b.textContent = label||n;
    b.onclick = () => { currentPage=n; renderTable(); };
    return b;
  };
  if (currentPage > 1) pag.appendChild(makeBtn(currentPage-1, '‹'));
  let start2 = Math.max(1, currentPage-2), end2 = Math.min(totalPages, start2+4);
  for (let i=start2; i<=end2; i++) pag.appendChild(makeBtn(i));
  if (currentPage < totalPages) pag.appendChild(makeBtn(currentPage+1, '›'));
}

function editEntry(id) {
  const entry = data.find(e => e.id === id);
  if (!entry) { showToast('⚠️ ไม่พบรายการ'); return; }
  openAddModal(entry);
}

async function deleteEntry(id) {
  if (!confirm('ลบรายการนี้?')) return;
  try {
    const res = await apiCall('delete', { id });
    if (!res.success) { showToast('⚠️ ลบไม่สำเร็จ'); return; }
    data = data.filter(e => e.id !== id);
    filtered = filtered.filter(e => e.id !== id);
    refreshAllViews();
    showToast('🗑 ลบรายการแล้ว (บันทึกถาวรบนเซิร์ฟเวอร์)');
  } catch(e) {
    showToast('⚠️ เชื่อมต่อเซิร์ฟเวอร์ไม่ได้');
  }
}

function refreshAllViews() {
  const dashEl = document.getElementById('tab-dashboard');
  const expEl = document.getElementById('tab-expenses');
  const bdEl = document.getElementById('tab-breakdown');
  const supEl = document.getElementById('tab-supply');
  const fcEl = document.getElementById('tab-fixcost');
  const exportEl = document.getElementById('tab-export');
  if (dashEl && dashEl.classList.contains('active')) renderDashboard();
  if (expEl && expEl.classList.contains('active')) applyFilters();
  if (bdEl && bdEl.classList.contains('active')) renderBreakdown();
  if (supEl && supEl.classList.contains('active')) renderSupply();
  if (fcEl && fcEl.classList.contains('active')) renderFixcost();
  if (exportEl && exportEl.classList.contains('active')) renderSummaryTable();
}

// ==================== ADD MODAL ====================
let editingId = null;

function openAddModal(entry=null) {
  editingId = entry ? entry.id : null;
  document.getElementById('modalTitle').textContent = entry ? 'แก้ไขรายการ' : 'เพิ่มรายการค่าใช้จ่าย';
  const today = new Date();
  document.getElementById('f-date').value = entry ? entry.d.split('/').reverse().join('-') : today.toISOString().split('T')[0];
  document.getElementById('f-month').value = entry ? entry.m : MONTHS[today.getMonth()];
  document.getElementById('f-desc').value = entry ? entry.r : '';
  document.getElementById('f-amount').value = entry ? entry.a : '';
  document.getElementById('f-vendor').value = entry ? entry.v : '';
  document.getElementById('f-cat').value = entry ? entry.c : 'ต้นทุนสินค้า';
  document.getElementById('f-subcat').value = entry ? (entry.sc||'') : '';
  document.getElementById('f-payer').value = entry ? entry.payer : 'รมณ';
  document.getElementById('addModal').classList.add('open');
}
// Auto-fill month from date (attached once, not on every modal open)
document.getElementById('f-date').addEventListener('change', function() {
  const d = new Date(this.value);
  if (!isNaN(d)) document.getElementById('f-month').value = MONTHS[d.getMonth()];
});

function closeModal() { document.getElementById('addModal').classList.remove('open'); }

async function saveEntry() {
  const desc = document.getElementById('f-desc').value.trim();
  const amount = parseFloat(document.getElementById('f-amount').value);
  if (!desc || isNaN(amount) || amount <= 0) { alert('กรุณากรอกรายการและจำนวนเงิน'); return; }
  const dateVal = document.getElementById('f-date').value;
  const entry = {
    id: editingId || null,
    d: ddmmyyyy(dateVal),
    r: desc,
    qty: 1,
    price: amount,
    a: amount,
    c: document.getElementById('f-cat').value,
    sc: document.getElementById('f-subcat').value,
    v: document.getElementById('f-vendor').value,
    m: document.getElementById('f-month').value,
    payer: document.getElementById('f-payer').value
  };
  try {
    const res = await apiCall('save', entry);
    if (!res.success) { showToast('⚠️ บันทึกไม่สำเร็จ'); return; }
    entry.id = res.id;
    if (editingId) {
      const idx = data.findIndex(e => e.id === editingId);
      if (idx >= 0) data[idx] = entry;
    } else {
      data.unshift(entry);
    }
    closeModal();
    refreshAllViews();
    showToast(editingId ? '✅ แก้ไขรายการแล้ว (บันทึกถาวรบนเซิร์ฟเวอร์)' : '✅ เพิ่มรายการแล้ว (บันทึกถาวรบนเซิร์ฟเวอร์)');
  } catch(e) {
    showToast('⚠️ เชื่อมต่อเซิร์ฟเวอร์ไม่ได้');
  }
}

// ==================== SYNC ====================
async function syncFromSheet() {
  document.getElementById('syncStatus').textContent = '⏳ กำลัง sync...';
  showToast('⏳ กำลังดึงข้อมูลจาก Paypers Sheet (ผ่านเซิร์ฟเวอร์)...');
  try {
    const res = await apiCall('trigger_sync', {});
    if (res.success) {
      await loadDataFromServer();
      applyFilters();
      refreshAllViews();
      document.getElementById('syncStatus').textContent = `✅ Synced +${res.inserted} รายการใหม่`;
      showToast(`✅ Sync สำเร็จ! เพิ่ม ${res.inserted} รายการใหม่ (ข้าม ${res.skipped} รายการซ้ำ)`, 4000);
    } else {
      document.getElementById('syncStatus').textContent = '❌ Sync ล้มเหลว';
      showToast('❌ Sync ล้มเหลว: ' + (res.error || ''));
    }
  } catch(e) {
    document.getElementById('syncStatus').textContent = '❌ Sync ล้มเหลว';
    showToast('❌ เชื่อมต่อเซิร์ฟเวอร์ไม่ได้');
  }
}

// ==================== EXPORT ====================
function getExportData(monthSel, catSel) {
  return data.filter(e => {
    if (monthSel && monthSel !== 'all' && e.m !== monthSel) return false;
    if (catSel && catSel !== 'all' && e.c !== catSel) return false;
    return true;
  });
}

function exportExcel() {
  const month = document.getElementById('xlsxMonth').value;
  const cat = document.getElementById('xlsxCat').value;
  const rows = getExportData(month, cat);
  if (!rows.length) { showToast('⚠️ ไม่มีข้อมูลที่จะ export'); return; }

  const wb = XLSX.utils.book_new();

  // Sheet 1: Raw data
  const headers = ['ลำดับ','วันที่','รายการ','จำนวน','ราคา/หน่วย','ยอดรวม','หมวดหมู่','หมวดย่อย','ผู้ขาย','เดือน','ผู้จ่าย'];
  const wsData = [headers, ...rows.map((e,i) => [i+1, e.d, e.r, e.qty, e.price, e.a, e.c, e.sc||'', e.v||'', e.m, e.payer||''])];
  const ws = XLSX.utils.aoa_to_sheet(wsData);
  ws['!cols'] = [5,10,40,8,12,12,18,15,20,12,12].map(w=>({wch:w}));
  XLSX.utils.book_append_sheet(wb, ws, 'รายการค่าใช้จ่าย');

  // Sheet 2: Summary by month
  const monthSummary = {};
  MONTHS.slice(0,8).forEach(m => monthSummary[m] = {count:0, total:0});
  rows.forEach(e => { if (monthSummary[e.m]) { monthSummary[e.m].count++; monthSummary[e.m].total+=e.a; } });
  const ws2Data = [['เดือน','จำนวนรายการ','รวมค่าใช้จ่าย (บาท)'],
    ...MONTHS.slice(0,8).map(m => [m, monthSummary[m].count, monthSummary[m].total]),
    ['รวมทั้งหมด', rows.length, rows.reduce((s,e)=>s+e.a,0)]];
  const ws2 = XLSX.utils.aoa_to_sheet(ws2Data);
  ws2['!cols'] = [{wch:15},{wch:15},{wch:20}];
  XLSX.utils.book_append_sheet(wb, ws2, 'สรุปรายเดือน');

  // Sheet 3: Summary by category
  const catSum = {};
  rows.forEach(e => { catSum[e.c] = (catSum[e.c]||0)+e.a; });
  const ws3Data = [['หมวดหมู่','รวม (บาท)','%'],
    ...Object.entries(catSum).sort((a,b)=>b[1]-a[1]).map(([c,v]) => [c, v, (v/rows.reduce((s,e)=>s+e.a,0)*100).toFixed(1)+'%'])];
  const ws3 = XLSX.utils.aoa_to_sheet(ws3Data);
  ws3['!cols'] = [{wch:25},{wch:20},{wch:10}];
  XLSX.utils.book_append_sheet(wb, ws3, 'สรุปหมวดหมู่');

  const monthLabel = month==='all' ? 'ทุกเดือน' : month;
  XLSX.writeFile(wb, `Tiger88_Expenses_${monthLabel}_${new Date().toISOString().split('T')[0]}.xlsx`);
  showToast('✅ ดาวน์โหลด Excel สำเร็จ!');
}

function exportCSV() {
  const month = document.getElementById('csvMonth').value;
  const rows = getExportData(month, 'all');
  if (!rows.length) { showToast('⚠️ ไม่มีข้อมูล'); return; }
  const headers = 'ลำดับ,วันที่,รายการ,จำนวน,ราคา,รวม,หมวดหมู่,หมวดย่อย,ผู้ขาย,เดือน,ผู้จ่าย';
  const csvRows = rows.map((e,i) => [i+1, e.d, `"${e.r.replace(/"/g,'""')}"`, e.qty, e.price, e.a, e.c, e.sc||'', `"${(e.v||'').replace(/"/g,'""')}"`, e.m, e.payer||''].join(','));
  const csv = '﻿' + headers + '\n' + csvRows.join('\n');
  const a = document.createElement('a');
  a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
  a.download = `Tiger88_Expenses_${new Date().toISOString().split('T')[0]}.csv`;
  a.click();
  showToast('✅ ดาวน์โหลด CSV สำเร็จ!');
}

function printReport() {
  const month = document.getElementById('pdfMonth').value;
  const rows = getExportData(month, 'all');
  const total = rows.reduce((s,e)=>s+e.a,0);
  const win = window.open('','_blank');
  win.document.write(`<html><head><title>รายงานค่าใช้จ่าย Tiger88</title>
  <style>body{font-family:sans-serif;padding:20px;font-size:12px}h1{color:#92400e}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:6px;text-align:left}th{background:#fef3c7}tr:nth-child(even){background:#fafafa}.total{font-weight:bold;text-align:right}</style></head>
  <body><h1>🐯 เสือกินเส้น 88 — รายงานค่าใช้จ่าย</h1>
  <p>เดือน: ${month==='all'?'ทุกเดือน':month} | จำนวน: ${rows.length} รายการ | รวม: ฿${total.toLocaleString('th-TH')}</p>
  <table><thead><tr><th>#</th><th>วันที่</th><th>รายการ</th><th>หมวดหมู่</th><th>ผู้จ่าย</th><th>จำนวนเงิน</th></tr></thead>
  <tbody>${rows.map((e,i)=>`<tr><td>${i+1}</td><td>${e.d}</td><td>${e.r.substring(0,60)}</td><td>${e.c}</td><td>${e.payer||''}</td><td style="text-align:right">฿${e.a.toLocaleString('th-TH')}</td></tr>`).join('')}
  <tr><td colspan="5" class="total">รวมทั้งหมด</td><td style="text-align:right;font-weight:bold">฿${total.toLocaleString('th-TH')}</td></tr></tbody></table>
  <p style="margin-top:20px;color:#999">พิมพ์เมื่อ: ${new Date().toLocaleDateString('th-TH')}</p></body></html>`);
  win.document.close(); win.print();
}

// ==================== FIXED COST (ค่าใช้จ่ายคงที่) ====================
const FIXCOST_CATS = ['ค่าเช่า','ค่าไฟ','ค่าน้ำ','ค่าเนต','เงินเดือนเด็ก','อื่นๆ'];
const FIXCOST_COLORS = {
  'ค่าเช่า':'#7c3aed','ค่าไฟ':'#f59e0b','ค่าน้ำ':'#0ea5e9',
  'ค่าเนต':'#06b6d4','เงินเดือนเด็ก':'#ec4899','อื่นๆ':'#6b7280'
};

function classifyFixcost(e) {
  const sc = (e.sc||'').trim();
  const r = e.r||'';
  const v = e.v||'';
  // เงินเดือนเด็ก (staff salary) - detect via sc or description keyword, from any category
  if (sc === 'เงินเดือนพนักงาน' || r.includes('เงินเดือน')) return 'เงินเดือนเด็ก';
  // Only consider fixcos-related items for the rest
  if (e.c !== 'fixcos') return null;
  if (sc === 'ค่าเช่าสำนักงาน' || r.includes('เช่า')) return 'ค่าเช่า';
  if (sc === 'ค่าอินเทอร์เน็ต' || r.includes('เน็ต') || v.toLowerCase().includes('internet')) return 'ค่าเนต';
  if (r.includes('ค่าน้ำ') || v.includes('ประปา') || v.toUpperCase().includes('WATER')) return 'ค่าน้ำ';
  if (r.includes('ค่าไฟ') || r.includes('การไฟฟ้า') || v.includes('การไฟฟ้า') || v.toUpperCase().includes('ELECTRIC')) return 'ค่าไฟ';
  return 'อื่นๆ';
}

function getFixcostData() {
  const m = document.getElementById('fc-month').value;
  return data.filter(e => (e.c === 'fixcos' || e.sc === 'เงินเดือนพนักงาน') && (m === 'all' || e.m === m))
    .map(e => ({...e, fcCat: classifyFixcost(e)}))
    .filter(e => e.fcCat);
}

function renderFixcost() {
  const d = getFixcostData();
  const total = d.reduce((s,e)=>s+e.a,0);
  const monthCount = new Set(d.map(e=>e.m)).size || 1;

  const catTotals = {};
  FIXCOST_CATS.forEach(c => catTotals[c] = {count:0, total:0});
  d.forEach(e => { catTotals[e.fcCat].count++; catTotals[e.fcCat].total += e.a; });

  document.getElementById('fc-kpis').innerHTML = FIXCOST_CATS.filter(c=>catTotals[c].total>0).map(c => {
    const v = catTotals[c];
    const color = FIXCOST_COLORS[c]||'#6b7280';
    const pct = total>0 ? (v.total/total*100).toFixed(1) : '0.0';
    return `<div class="kpi-card" style="border-left:4px solid ${color}">
      <div class="kpi-label">${c}</div>
      <div class="kpi-value">${fmt(v.total)}</div>
      <div class="kpi-sub">${fmtN(v.count)} รายการ · ${pct}%</div>
    </div>`;
  }).join('') + `<div class="kpi-card highlight">
      <div class="kpi-label">💸 รวมค่าใช้จ่ายคงที่</div>
      <div class="kpi-value">${fmt(total)}</div>
      <div class="kpi-sub">เฉลี่ย ${fmt(total/monthCount)}/เดือน</div>
    </div>`;

  const chartCats = FIXCOST_CATS.filter(c => catTotals[c].total > 0);
  renderDoughnutChart('chartFixcost', chartCats, chartCats.map(c=>catTotals[c].total), chartCats.map(c=>FIXCOST_COLORS[c]||'#6b7280'));

  if (charts['chartFixcostTrend']) charts['chartFixcostTrend'].destroy();
  const ctx2 = document.getElementById('chartFixcostTrend').getContext('2d');
  const allFC = data.filter(e => e.c === 'fixcos' || e.sc === 'เงินเดือนพนักงาน').map(e => ({...e, fcCat: classifyFixcost(e)})).filter(e=>e.fcCat);
  const monthsUsed = MONTHS.slice(0,8).filter(m => allFC.some(e => e.m === m));
  const mlabelsShort = monthsUsed.map(m => m.replace('มกราคม','ม.ค.').replace('กุมภาพันธ์','ก.พ.').replace('มีนาคม','มี.ค.').replace('เมษายน','เม.ย.').replace('พฤษภาคม','พ.ค.').replace('มิถุนายน','มิ.ย.').replace('กรกฎาคม','ก.ค.').replace('สิงหาคม','ส.ค.'));
  charts['chartFixcostTrend'] = new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: mlabelsShort,
      datasets: chartCats.map(c => ({
        label: c,
        data: monthsUsed.map(m => allFC.filter(e => e.m===m && e.fcCat===c).reduce((s,e)=>s+e.a,0)),
        backgroundColor: (FIXCOST_COLORS[c]||'#6b7280') + 'cc',
        borderRadius: 3
      }))
    },
    options: {
      responsive: true,
      plugins: { legend:{ position:'top', labels:{boxWidth:10,font:{size:10}} }, tooltip:{ callbacks:{ label: ctx => `${ctx.dataset.label}: ${fmt(ctx.raw)}` } } },
      scales: { x:{ stacked:true, grid:{display:false} }, y:{ stacked:true, ticks:{ callback:v=>fmt(v) }, grid:{color:'#f0ede8'} } }
    }
  });

  const sumBody = document.getElementById('fixcostSummaryBody');
  sumBody.innerHTML = FIXCOST_CATS.map(c => {
    const v = catTotals[c];
    if (v.count === 0) return `<tr style="color:#d6d3d1"><td>${c}</td><td style="text-align:right">0</td><td style="text-align:right">–</td><td style="text-align:right">–</td><td style="text-align:right">–</td></tr>`;
    const pct = total>0 ? (v.total/total*100).toFixed(1) : '0.0';
    const avgMonth = v.total/monthCount;
    return `<tr>
      <td><span class="badge" style="background:${FIXCOST_COLORS[c]}22;color:${FIXCOST_COLORS[c]}">${c}</span></td>
      <td style="text-align:right">${fmtN(v.count)}</td>
      <td style="text-align:right;font-weight:600;color:var(--red)">${fmt(v.total)}</td>
      <td style="text-align:right;color:var(--text2)">${pct}%</td>
      <td style="text-align:right;color:var(--text2)">${fmt(avgMonth)}</td>
    </tr>`;
  }).join('') + `<tr style="background:var(--tiger-light);font-weight:700">
    <td>รวมทั้งหมด</td><td style="text-align:right">${fmtN(d.length)}</td>
    <td style="text-align:right;color:var(--red)">${fmt(total)}</td><td style="text-align:right">100%</td>
    <td style="text-align:right">${fmt(total/monthCount)}</td>
  </tr>`;

  window._fixcostData = d;
  renderFixcostTable();
  renderFixcostMonthly();
}

function renderFixcostTable() {
  const catFilter = document.getElementById('fc-catfilter').value;
  const d = (window._fixcostData||[]).filter(e => !catFilter || e.fcCat === catFilter)
    .sort((a,b) => parseDate(b.d) - parseDate(a.d));
  const tbody = document.getElementById('fixcostTableBody');
  tbody.innerHTML = d.map(e => `
    <tr>
      <td style="white-space:nowrap">${e.d}</td>
      <td style="max-width:220px"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${e.r}">${e.r}</div></td>
      <td><span class="badge" style="background:${FIXCOST_COLORS[e.fcCat]}22;color:${FIXCOST_COLORS[e.fcCat]}">${e.fcCat}</span></td>
      <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:var(--text2)">${e.v||'–'}</td>
      <td>${payerBadge(e.payer)}</td>
      <td class="amount" style="text-align:right;white-space:nowrap">${fmt(e.a)}</td>
      <td style="white-space:nowrap"><button class="btn btn-secondary btn-sm" onclick="editEntry(${e.id})">✏️</button> <button class="btn btn-secondary btn-sm" onclick="deleteEntry(${e.id})">🗑</button></td>
    </tr>`).join('') || `<tr><td colspan="7" style="text-align:center;color:var(--text2);padding:14px">ไม่มีข้อมูล</td></tr>`;
}

function renderFixcostMonthly() {
  const d = data.filter(e => e.c === 'fixcos' || e.sc === 'เงินเดือนพนักงาน').map(e => ({...e, fcCat: classifyFixcost(e)})).filter(e=>e.fcCat);
  const monthsUsed = MONTHS.slice(0,8).filter(m => d.some(e => e.m === m));
  const mlabelsShort = monthsUsed.map(m => m.replace('มกราคม','ม.ค.').replace('กุมภาพันธ์','ก.พ.').replace('มีนาคม','มี.ค.').replace('เมษายน','เม.ย.').replace('พฤษภาคม','พ.ค.').replace('มิถุนายน','มิ.ย.').replace('กรกฎาคม','ก.ค.').replace('สิงหาคม','ส.ค.'));

  const head = document.getElementById('fixcostMonthlyHead');
  head.innerHTML = `<tr><th>หมวดหมู่</th>${mlabelsShort.map(m=>`<th style="text-align:right">${m}</th>`).join('')}<th style="text-align:right;background:#fef3c7">รวม</th></tr>`;

  const body = document.getElementById('fixcostMonthlyBody');
  let grandTotal = 0;
  const rows = FIXCOST_CATS.map(c => {
    const catData = d.filter(e => e.fcCat === c);
    const catTotal = catData.reduce((s,e)=>s+e.a,0);
    grandTotal += catTotal;
    const cells = monthsUsed.map(m => {
      const v = catData.filter(e=>e.m===m).reduce((s,e)=>s+e.a,0);
      return `<td style="text-align:right">${v ? fmt(v) : '<span style="color:#d6d3d1">–</span>'}</td>`;
    }).join('');
    const rowStyle = catTotal === 0 ? ' style="color:#d6d3d1"' : '';
    return `<tr${rowStyle}><td><span class="badge" style="background:${FIXCOST_COLORS[c]}22;color:${FIXCOST_COLORS[c]}">${c}</span></td>${cells}<td style="text-align:right;font-weight:700;background:#fef9ec">${catTotal?fmt(catTotal):'–'}</td></tr>`;
  }).join('');

  const totalRow = `<tr style="background:var(--tiger-light);font-weight:700">
    <td>รวมทั้งหมด</td>
    ${monthsUsed.map(m => `<td style="text-align:right">${fmt(d.filter(e=>e.m===m).reduce((s,e)=>s+e.a,0))}</td>`).join('')}
    <td style="text-align:right;color:var(--red)">${fmt(grandTotal)}</td>
  </tr>`;
  body.innerHTML = rows + totalRow;

  window._fixcostMonthlyData = d;
  window._fixcostMonthsUsed = monthsUsed;
}

function exportFixcostExcel() {
  const d = getFixcostData();
  const total = d.reduce((s,e)=>s+e.a,0);
  const catTotals = {};
  FIXCOST_CATS.forEach(c => catTotals[c] = {count:0, total:0});
  d.forEach(e => { catTotals[e.fcCat].count++; catTotals[e.fcCat].total += e.a; });

  const wb = XLSX.utils.book_new();
  const ws1Data = [['หมวดหมู่','จำนวนรายการ','รวม (บาท)','% ของยอดรวม'],
    ...FIXCOST_CATS.map(c => [c, catTotals[c].count, catTotals[c].total, total>0?(catTotals[c].total/total*100).toFixed(1)+'%':'0%']),
    ['รวมทั้งหมด', d.length, total, '100%']];
  const ws1 = XLSX.utils.aoa_to_sheet(ws1Data);
  ws1['!cols'] = [{wch:18},{wch:14},{wch:14},{wch:14}];
  XLSX.utils.book_append_sheet(wb, ws1, 'สรุปค่าใช้จ่ายคงที่');

  const headers = ['วันที่','รายการ','หมวด','ผู้ขาย','ผู้จ่าย','จำนวนเงิน'];
  const rows = d.map(e => [e.d, e.r, e.fcCat, e.v||'', e.payer||'', e.a]);
  const ws2 = XLSX.utils.aoa_to_sheet([headers, ...rows]);
  ws2['!cols'] = [{wch:12},{wch:35},{wch:14},{wch:22},{wch:10},{wch:12}];
  XLSX.utils.book_append_sheet(wb, ws2, 'รายการทั้งหมด');

  const month = document.getElementById('fc-month').value;
  XLSX.writeFile(wb, `Tiger88_ค่าใช้จ่ายคงที่_${month==='all'?'ทุกเดือน':month}_${new Date().toISOString().split('T')[0]}.xlsx`);
  showToast('✅ ดาวน์โหลด Excel สำเร็จ!');
}

function exportFixcostMonthlyExcel() {
  const d = window._fixcostMonthlyData || [];
  const monthsUsed = window._fixcostMonthsUsed || [];
  if (!d.length) { showToast('⚠️ ไม่มีข้อมูล'); return; }

  const wb = XLSX.utils.book_new();
  const headers = ['หมวดหมู่', ...monthsUsed, 'รวม'];
  const rows = FIXCOST_CATS.map(c => {
    const catData = d.filter(e => e.fcCat === c);
    const vals = monthsUsed.map(m => catData.filter(e=>e.m===m).reduce((s,e)=>s+e.a,0));
    return [c, ...vals, vals.reduce((a,b)=>a+b,0)];
  });
  const totalRow = ['รวมทั้งหมด', ...monthsUsed.map(m => d.filter(e=>e.m===m).reduce((s,e)=>s+e.a,0)), d.reduce((s,e)=>s+e.a,0)];
  const ws = XLSX.utils.aoa_to_sheet([headers, ...rows, totalRow]);
  ws['!cols'] = [{wch:18}, ...monthsUsed.map(()=>({wch:13})), {wch:14}];
  XLSX.utils.book_append_sheet(wb, ws, 'ค่าใช้จ่ายคงที่ x เดือน');

  XLSX.writeFile(wb, `Tiger88_ค่าใช้จ่ายคงที่_รายเดือน_${new Date().toISOString().split('T')[0]}.xlsx`);
  showToast('✅ ดาวน์โหลด Excel สำเร็จ!');
}

// ==================== SUPPLY CATEGORY (หมวดวัตถุดิบ/ซัพพลาย) ====================
const SUPPLY_CATS = ['ตลาด','หมูสด','เนื้อ','แม็คโคร','แก๊ส','แกร็ป','ช้อปปี้','ไทยน้ำทิพย์','วัสดุสิ้นเปลือง','รายจ่ายอื่นๆ'];
const SUPPLY_COLORS = {
  'ตลาด':'#059669','หมูสด':'#ef4444','เนื้อ':'#dc2626','แม็คโคร':'#f97316',
  'แก๊ส':'#f59e0b','แกร็ป':'#10b981','ช้อปปี้':'#f97316','ไทยน้ำทิพย์':'#3b82f6',
  'วัสดุสิ้นเปลือง':'#8b5cf6','รายจ่ายอื่นๆ':'#6b7280'
};

function classifySupply(e) {
  const sc = (e.sc||'').trim();
  const v = e.v||'';
  if (sc === 'ตลาด') return 'ตลาด';
  if (sc === 'หมู') return 'หมูสด';
  if (sc === 'เนื้อ') return 'เนื้อ';
  if (sc === 'แม็คโคร') return 'แม็คโคร';
  if (sc === 'แก๊สหุงต้ม') return 'แก๊ส';
  if (sc === 'แกร็ป') return 'แกร็ป';
  if (sc === 'ช้อปปี้') return 'ช้อปปี้';
  if (v.includes('ไทยน้ำทิพย์')) return 'ไทยน้ำทิพย์';
  if (sc.includes('สิ้นเปลือง') || sc.includes('วัสดุ')) return 'วัสดุสิ้นเปลือง';
  return 'รายจ่ายอื่นๆ';
}

function getSupplyData() {
  const m = document.getElementById('sup-month').value;
  const p = document.getElementById('sup-payer').value;
  return data.filter(e => {
    if (m !== 'all' && e.m !== m) return false;
    if (p !== 'all' && e.payer !== p) return false;
    return true;
  }).map(e => ({...e, supplyCat: classifySupply(e)}));
}

function renderSupply() {
  const d = getSupplyData();
  const total = d.reduce((s,e)=>s+e.a,0);

  // KPI: top 4 categories by spend
  const catTotals = {};
  SUPPLY_CATS.forEach(c => catTotals[c] = {count:0, total:0});
  d.forEach(e => { catTotals[e.supplyCat].count++; catTotals[e.supplyCat].total += e.a; });
  const topCats = Object.entries(catTotals).filter(([c,v])=>v.total>0).sort((a,b)=>b[1].total-a[1].total).slice(0,4);

  document.getElementById('sup-kpis').innerHTML = topCats.map(([c,v]) => {
    const color = SUPPLY_COLORS[c]||'#6b7280';
    const pct = total>0 ? (v.total/total*100).toFixed(1) : '0.0';
    return `<div class="kpi-card" style="border-left:4px solid ${color}">
      <div class="kpi-label">${c}</div>
      <div class="kpi-value">${fmt(v.total)}</div>
      <div class="kpi-sub">${fmtN(v.count)} รายการ · ${pct}%</div>
    </div>`;
  }).join('') + `<div class="kpi-card highlight">
      <div class="kpi-label">💸 รวมทั้งหมด</div>
      <div class="kpi-value">${fmt(total)}</div>
      <div class="kpi-sub">${fmtN(d.length)} รายการ</div>
    </div>`;

  // Doughnut chart
  const chartCats = SUPPLY_CATS.filter(c => catTotals[c].total > 0);
  renderDoughnutChart('chartSupply', chartCats, chartCats.map(c=>catTotals[c].total), chartCats.map(c=>SUPPLY_COLORS[c]||'#6b7280'));

  // Monthly trend chart (stacked by supply cat)
  if (charts['chartSupplyTrend']) charts['chartSupplyTrend'].destroy();
  const ctx2 = document.getElementById('chartSupplyTrend').getContext('2d');
  const monthsUsed = MONTHS.slice(0,8);
  const mlabelsShort = monthsUsed.map(m => m.replace('มกราคม','ม.ค.').replace('กุมภาพันธ์','ก.พ.').replace('มีนาคม','มี.ค.').replace('เมษายน','เม.ย.').replace('พฤษภาคม','พ.ค.').replace('มิถุนายน','มิ.ย.').replace('กรกฎาคม','ก.ค.').replace('สิงหาคม','ส.ค.'));
  const allData = getSupplyDataAllMonths();
  charts['chartSupplyTrend'] = new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: mlabelsShort,
      datasets: chartCats.map(c => ({
        label: c,
        data: monthsUsed.map(m => allData.filter(e => e.m===m && e.supplyCat===c).reduce((s,e)=>s+e.a,0)),
        backgroundColor: (SUPPLY_COLORS[c]||'#6b7280') + 'cc',
        borderRadius: 3
      }))
    },
    options: {
      responsive: true,
      plugins: { legend: { position:'top', labels:{boxWidth:10,font:{size:10}} }, tooltip: { callbacks:{ label: ctx => `${ctx.dataset.label}: ${fmt(ctx.raw)}` } } },
      scales: { x: { stacked:true, grid:{display:false} }, y: { stacked:true, ticks:{ callback:v=>fmt(v) }, grid:{color:'#f0ede8'} } }
    }
  });

  // Summary table
  const sumBody = document.getElementById('supplySummaryBody');
  sumBody.innerHTML = SUPPLY_CATS.map(c => {
    const v = catTotals[c];
    if (v.count === 0) return `<tr style="color:#d6d3d1"><td>${c}</td><td style="text-align:right">0</td><td style="text-align:right">–</td><td style="text-align:right">–</td><td style="text-align:right">–</td></tr>`;
    const pct = total>0 ? (v.total/total*100).toFixed(1) : '0.0';
    const avg = v.count>0 ? v.total/v.count : 0;
    return `<tr>
      <td><span class="badge" style="background:${SUPPLY_COLORS[c]}22;color:${SUPPLY_COLORS[c]}">${c}</span></td>
      <td style="text-align:right">${fmtN(v.count)}</td>
      <td style="text-align:right;font-weight:600;color:var(--red)">${fmt(v.total)}</td>
      <td style="text-align:right;color:var(--text2)">${pct}%</td>
      <td style="text-align:right;color:var(--text2)">${fmt(avg)}</td>
    </tr>`;
  }).join('') + `<tr style="background:var(--tiger-light);font-weight:700">
    <td>รวมทั้งหมด</td><td style="text-align:right">${fmtN(d.length)}</td>
    <td style="text-align:right;color:var(--red)">${fmt(total)}</td><td style="text-align:right">100%</td><td></td>
  </tr>`;

  window._supplyData = d;
  renderSupplyTable();
  renderSupplyMonthly();
}

function getSupplyDataAllMonths() {
  const p = document.getElementById('sup-payer').value;
  return data.filter(e => p==='all' || e.payer===p).map(e => ({...e, supplyCat: classifySupply(e)}));
}

function renderSupplyTable() {
  const catFilter = document.getElementById('sup-catfilter').value;
  const d = (window._supplyData||[]).filter(e => !catFilter || e.supplyCat === catFilter)
    .sort((a,b) => parseDate(b.d) - parseDate(a.d));
  const tbody = document.getElementById('supplyTableBody');
  tbody.innerHTML = d.slice(0,200).map(e => `
    <tr>
      <td style="white-space:nowrap">${e.d}</td>
      <td style="max-width:220px"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${e.r}">${e.r}</div></td>
      <td><span class="badge" style="background:${SUPPLY_COLORS[e.supplyCat]}22;color:${SUPPLY_COLORS[e.supplyCat]}">${e.supplyCat}</span></td>
      <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:var(--text2)">${e.v||'–'}</td>
      <td>${payerBadge(e.payer)}</td>
      <td class="amount" style="text-align:right;white-space:nowrap">${fmt(e.a)}</td>
      <td style="white-space:nowrap"><button class="btn btn-secondary btn-sm" onclick="editEntry(${e.id})">✏️</button> <button class="btn btn-secondary btn-sm" onclick="deleteEntry(${e.id})">🗑</button></td>
    </tr>`).join('');
  if (d.length > 200) {
    tbody.innerHTML += `<tr><td colspan="7" style="text-align:center;color:var(--text2);padding:14px">แสดง 200 จาก ${fmtN(d.length)} รายการ — ใช้ตัวกรองเพื่อดูเพิ่มเติม</td></tr>`;
  }
}

function renderSupplyMonthly() {
  // Always use payer filter but ignore month filter (show all months in the cross table)
  const p = document.getElementById('sup-payer').value;
  const d = data.filter(e => p==='all' || e.payer===p).map(e => ({...e, supplyCat: classifySupply(e)}));

  const monthsUsed = MONTHS.slice(0,8).filter(m => d.some(e => e.m === m));
  const mlabelsShort = monthsUsed.map(m => m.replace('มกราคม','ม.ค.').replace('กุมภาพันธ์','ก.พ.').replace('มีนาคม','มี.ค.').replace('เมษายน','เม.ย.').replace('พฤษภาคม','พ.ค.').replace('มิถุนายน','มิ.ย.').replace('กรกฎาคม','ก.ค.').replace('สิงหาคม','ส.ค.'));

  const head = document.getElementById('supplyMonthlyHead');
  head.innerHTML = `<tr><th>หมวดหมู่</th>${mlabelsShort.map(m=>`<th style="text-align:right">${m}</th>`).join('')}<th style="text-align:right;background:#fef3c7">รวม</th></tr>`;

  const body = document.getElementById('supplyMonthlyBody');
  let grandTotal = 0;
  const rows = SUPPLY_CATS.map(c => {
    const catData = d.filter(e => e.supplyCat === c);
    const catTotal = catData.reduce((s,e)=>s+e.a,0);
    grandTotal += catTotal;
    const cells = monthsUsed.map(m => {
      const v = catData.filter(e=>e.m===m).reduce((s,e)=>s+e.a,0);
      return `<td style="text-align:right">${v ? fmt(v) : '<span style="color:#d6d3d1">–</span>'}</td>`;
    }).join('');
    const rowStyle = catTotal === 0 ? ' style="color:#d6d3d1"' : '';
    return `<tr${rowStyle}><td><span class="badge" style="background:${SUPPLY_COLORS[c]}22;color:${SUPPLY_COLORS[c]}">${c}</span></td>${cells}<td style="text-align:right;font-weight:700;background:#fef9ec">${catTotal?fmt(catTotal):'–'}</td></tr>`;
  }).join('');

  const totalRow = `<tr style="background:var(--tiger-light);font-weight:700">
    <td>รวมทั้งหมด</td>
    ${monthsUsed.map(m => `<td style="text-align:right">${fmt(d.filter(e=>e.m===m).reduce((s,e)=>s+e.a,0))}</td>`).join('')}
    <td style="text-align:right;color:var(--red)">${fmt(grandTotal)}</td>
  </tr>`;
  body.innerHTML = rows + totalRow;

  window._supplyMonthlyData = d;
  window._supplyMonthsUsed = monthsUsed;
}

function exportSupplyMonthlyExcel() {
  const d = window._supplyMonthlyData || [];
  const monthsUsed = window._supplyMonthsUsed || [];
  if (!d.length) { showToast('⚠️ ไม่มีข้อมูล'); return; }

  const wb = XLSX.utils.book_new();
  const headers = ['หมวดหมู่', ...monthsUsed, 'รวม'];
  const rows = SUPPLY_CATS.map(c => {
    const catData = d.filter(e => e.supplyCat === c);
    const vals = monthsUsed.map(m => catData.filter(e=>e.m===m).reduce((s,e)=>s+e.a,0));
    return [c, ...vals, vals.reduce((a,b)=>a+b,0)];
  });
  const totalRow = ['รวมทั้งหมด', ...monthsUsed.map(m => d.filter(e=>e.m===m).reduce((s,e)=>s+e.a,0)), d.reduce((s,e)=>s+e.a,0)];
  const ws = XLSX.utils.aoa_to_sheet([headers, ...rows, totalRow]);
  ws['!cols'] = [{wch:20}, ...monthsUsed.map(()=>({wch:13})), {wch:14}];
  XLSX.utils.book_append_sheet(wb, ws, 'หมวดวัตถุดิบ x เดือน');

  XLSX.writeFile(wb, `Tiger88_หมวดวัตถุดิบ_รายเดือน_${new Date().toISOString().split('T')[0]}.xlsx`);
  showToast('✅ ดาวน์โหลด Excel สำเร็จ!');
}

function exportSupplyExcel() {
  const d = getSupplyData();
  const total = d.reduce((s,e)=>s+e.a,0);
  const catTotals = {};
  SUPPLY_CATS.forEach(c => catTotals[c] = {count:0, total:0});
  d.forEach(e => { catTotals[e.supplyCat].count++; catTotals[e.supplyCat].total += e.a; });

  const wb = XLSX.utils.book_new();

  // Sheet 1: Summary
  const ws1Data = [['หมวดหมู่','จำนวนรายการ','รวม (บาท)','% ของยอดรวม'],
    ...SUPPLY_CATS.map(c => [c, catTotals[c].count, catTotals[c].total, total>0?(catTotals[c].total/total*100).toFixed(1)+'%':'0%']),
    ['รวมทั้งหมด', d.length, total, '100%']];
  const ws1 = XLSX.utils.aoa_to_sheet(ws1Data);
  ws1['!cols'] = [{wch:20},{wch:14},{wch:14},{wch:14}];
  XLSX.utils.book_append_sheet(wb, ws1, 'สรุปหมวดวัตถุดิบ');

  // Sheet 2: Raw data with classification
  const headers = ['วันที่','รายการ','หมวดวัตถุดิบ','หมวดหมู่หลัก','หมวดย่อยเดิม','ผู้ขาย','ผู้จ่าย','จำนวนเงิน'];
  const rows = d.map(e => [e.d, e.r, e.supplyCat, e.c, e.sc||'', e.v||'', e.payer||'', e.a]);
  const ws2 = XLSX.utils.aoa_to_sheet([headers, ...rows]);
  ws2['!cols'] = [{wch:12},{wch:35},{wch:16},{wch:16},{wch:16},{wch:22},{wch:10},{wch:12}];
  XLSX.utils.book_append_sheet(wb, ws2, 'รายการทั้งหมด');

  const month = document.getElementById('sup-month').value;
  XLSX.writeFile(wb, `Tiger88_หมวดวัตถุดิบ_${month==='all'?'ทุกเดือน':month}_${new Date().toISOString().split('T')[0]}.xlsx`);
  showToast('✅ ดาวน์โหลด Excel สำเร็จ!');
}

// ==================== BREAKDOWN (แยกตามผู้จ่าย) ====================
function getBreakdownData() {
  const m = document.getElementById('bd-month').value;
  return data.filter(e => m === 'all' || e.m === m);
}

function renderBreakdown() {
  const d = getBreakdownData();
  const payers = ['รมณ','ฝรั่ง','พี่คิว','พ่อ'];
  const categories = [...new Set(d.map(e => e.c))].sort((a,b) => {
    const ta = d.filter(e=>e.c===a).reduce((s,e)=>s+e.a,0);
    const tb = d.filter(e=>e.c===b).reduce((s,e)=>s+e.a,0);
    return tb - ta;
  });
  const grandTotal = d.reduce((s,e)=>s+e.a,0);

  // KPI cards per payer
  document.getElementById('bd-payer-kpis').innerHTML = payers.map(p => {
    const pData = d.filter(e => e.payer === p);
    const pTotal = pData.reduce((s,e)=>s+e.a,0);
    const pct = grandTotal>0 ? (pTotal/grandTotal*100).toFixed(1) : '0.0';
    const color = PAYER_COLORS[p] || '#6b7280';
    return `<div class="kpi-card" style="border-left:4px solid ${color}">
      <div class="kpi-label">${payerBadge(p)}</div>
      <div class="kpi-value" style="margin-top:6px">${fmt(pTotal)}</div>
      <div class="kpi-sub">${fmtN(pData.length)} รายการ · ${pct}% ของยอดรวม</div>
    </div>`;
  }).join('');

  // Payer pie chart
  const payerTotals = payers.map(p => d.filter(e=>e.payer===p).reduce((s,e)=>s+e.a,0));
  renderDoughnutChart('chartBdPayer', payers, payerTotals, payers.map(p=>PAYER_COLORS[p]||'#6b7280'));

  // Stacked bar: category x payer
  if (charts['chartBdStacked']) charts['chartBdStacked'].destroy();
  const ctx = document.getElementById('chartBdStacked').getContext('2d');
  const topCats = categories.slice(0, 8);
  charts['chartBdStacked'] = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: topCats,
      datasets: payers.map(p => ({
        label: p,
        data: topCats.map(c => d.filter(e => e.c===c && e.payer===p).reduce((s,e)=>s+e.a,0)),
        backgroundColor: (PAYER_COLORS[p]||'#6b7280') + 'cc',
        borderRadius: 3
      }))
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'top', labels:{boxWidth:12,font:{size:11}} }, tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${fmt(ctx.raw)}` } } },
      scales: { x: { stacked: true, grid:{display:false}, ticks:{font:{size:10}} }, y: { stacked: true, ticks:{ callback: v => fmt(v) }, grid:{color:'#f0ede8'} } }
    }
  });

  // Cross table: category x payer
  const head = document.getElementById('breakdownHead');
  head.innerHTML = `<tr><th>หมวดหมู่</th>${payers.map(p=>`<th style="text-align:right">${p}</th>`).join('')}<th style="text-align:right;background:#fef3c7">รวม</th></tr>`;

  const body = document.getElementById('breakdownBody');
  let rowsHtml = categories.map(c => {
    const catTotal = d.filter(e=>e.c===c).reduce((s,e)=>s+e.a,0);
    const cells = payers.map(p => {
      const v = d.filter(e => e.c===c && e.payer===p).reduce((s,e)=>s+e.a,0);
      return `<td style="text-align:right">${v ? fmt(v) : '<span style="color:#d6d3d1">–</span>'}</td>`;
    }).join('');
    return `<tr><td>${catBadge(c)}</td>${cells}<td style="text-align:right;font-weight:700;background:#fef9ec">${fmt(catTotal)}</td></tr>`;
  }).join('');

  const totalRow = `<tr style="background:var(--tiger-light);font-weight:700">
    <td>รวมทั้งหมด</td>
    ${payers.map(p => `<td style="text-align:right">${fmt(d.filter(e=>e.payer===p).reduce((s,e)=>s+e.a,0))}</td>`).join('')}
    <td style="text-align:right;color:var(--red)">${fmt(grandTotal)}</td>
  </tr>`;
  body.innerHTML = rowsHtml + totalRow;

  // Detailed payer breakdown table
  const detailBody = document.getElementById('payerDetailBody');
  let detailRows = [];
  payers.forEach(p => {
    const pData = d.filter(e => e.payer === p);
    const pTotal = pData.reduce((s,e)=>s+e.a,0);
    if (pTotal === 0) return;
    const pCats = [...new Set(pData.map(e=>e.c))].sort((a,b) => {
      const ta = pData.filter(e=>e.c===a).reduce((s,e)=>s+e.a,0);
      const tb = pData.filter(e=>e.c===b).reduce((s,e)=>s+e.a,0);
      return tb-ta;
    });
    pCats.forEach((c, idx) => {
      const cData = pData.filter(e=>e.c===c);
      const cTotal = cData.reduce((s,e)=>s+e.a,0);
      detailRows.push(`<tr>
        <td>${idx===0 ? payerBadge(p) : ''}</td>
        <td>${catBadge(c)}</td>
        <td style="text-align:right">${fmtN(cData.length)}</td>
        <td style="text-align:right;font-weight:600">${fmt(cTotal)}</td>
        <td style="text-align:right;color:var(--text2)">${(cTotal/pTotal*100).toFixed(1)}%</td>
      </tr>`);
    });
    detailRows.push(`<tr style="background:#faf7f2;font-weight:700">
      <td colspan="2">รวม ${p}</td>
      <td style="text-align:right">${fmtN(pData.length)}</td>
      <td style="text-align:right;color:var(--red)">${fmt(pTotal)}</td>
      <td style="text-align:right">100%</td>
    </tr>`);
  });
  detailBody.innerHTML = detailRows.join('');
}

function exportBreakdownExcel() {
  const d = getBreakdownData();
  const payers = ['รมณ','ฝรั่ง','พี่คิว','พ่อ'];
  const categories = [...new Set(d.map(e => e.c))].sort((a,b) => {
    const ta = d.filter(e=>e.c===a).reduce((s,e)=>s+e.a,0);
    const tb = d.filter(e=>e.c===b).reduce((s,e)=>s+e.a,0);
    return tb - ta;
  });
  const wb = XLSX.utils.book_new();

  // Sheet 1: Cross table (category x payer)
  const headers = ['หมวดหมู่', ...payers, 'รวม'];
  const rows = categories.map(c => {
    const vals = payers.map(p => d.filter(e=>e.c===c && e.payer===p).reduce((s,e)=>s+e.a,0));
    return [c, ...vals, vals.reduce((a,b)=>a+b,0)];
  });
  const totalRow = ['รวมทั้งหมด', ...payers.map(p => d.filter(e=>e.payer===p).reduce((s,e)=>s+e.a,0)), d.reduce((s,e)=>s+e.a,0)];
  const ws1 = XLSX.utils.aoa_to_sheet([headers, ...rows, totalRow]);
  ws1['!cols'] = [{wch:22}, ...payers.map(()=>({wch:14})), {wch:14}];
  XLSX.utils.book_append_sheet(wb, ws1, 'หมวดหมู่ x ผู้จ่าย');

  // Sheet 2: Detail per payer per category
  const detailHeaders = ['ผู้จ่าย','หมวดหมู่','จำนวนรายการ','รวม (บาท)','% ของยอดผู้จ่าย'];
  let detailRows2 = [];
  payers.forEach(p => {
    const pData = d.filter(e => e.payer === p);
    const pTotal = pData.reduce((s,e)=>s+e.a,0);
    if (pTotal === 0) return;
    const pCats = [...new Set(pData.map(e=>e.c))];
    pCats.forEach(c => {
      const cData = pData.filter(e=>e.c===c);
      const cTotal = cData.reduce((s,e)=>s+e.a,0);
      detailRows2.push([p, c, cData.length, cTotal, (cTotal/pTotal*100).toFixed(1)+'%']);
    });
    detailRows2.push([`รวม ${p}`, '', pData.length, pTotal, '100%']);
  });
  const ws2 = XLSX.utils.aoa_to_sheet([detailHeaders, ...detailRows2]);
  ws2['!cols'] = [{wch:12},{wch:22},{wch:14},{wch:14},{wch:16}];
  XLSX.utils.book_append_sheet(wb, ws2, 'รายละเอียดผู้จ่าย');

  // Sheet 3: Raw filtered transactions
  const rawHeaders = ['วันที่','รายการ','หมวดหมู่','ผู้ขาย','ผู้จ่าย','จำนวนเงิน'];
  const rawRows = d.map(e => [e.d, e.r, e.c, e.v||'', e.payer||'', e.a]);
  const ws3 = XLSX.utils.aoa_to_sheet([rawHeaders, ...rawRows]);
  ws3['!cols'] = [{wch:12},{wch:40},{wch:18},{wch:20},{wch:12},{wch:12}];
  XLSX.utils.book_append_sheet(wb, ws3, 'รายการดิบ');

  const month = document.getElementById('bd-month').value;
  XLSX.writeFile(wb, `Tiger88_แยกผู้จ่าย_${month==='all'?'ทุกเดือน':month}_${new Date().toISOString().split('T')[0]}.xlsx`);
  showToast('✅ ดาวน์โหลด Excel สำเร็จ!');
}

// ==================== SUMMARY TABLE ====================
function renderSummaryTable() {
  const mainCats = ['ต้นทุนสินค้า','fixcos','บุคลากร & ค่าจ้าง','การตลาด & โฆษณา'];
  const total = data.reduce((s,e)=>s+e.a,0);
  const tbody = document.getElementById('summaryBody');
  let grandTotal = 0;
  tbody.innerHTML = MONTHS.slice(0,8).map(m => {
    const mData = data.filter(e => e.m === m);
    const mTotal = mData.reduce((s,e)=>s+e.a,0);
    grandTotal += mTotal;
    const catTotals = mainCats.map(c => mData.filter(e=>e.c===c).reduce((s,e)=>s+e.a,0));
    const others = mData.filter(e=>!mainCats.includes(e.c)).reduce((s,e)=>s+e.a,0);
    return `<tr>
      <td style="font-weight:600">${m}</td>
      ${catTotals.map(v=>`<td style="text-align:right">${v?fmt(v):'–'}</td>`).join('')}
      <td style="text-align:right">${others?fmt(others):'–'}</td>
      <td style="text-align:right;font-weight:700;color:var(--red)">${fmt(mTotal)}</td>
      <td style="text-align:right;color:var(--text2)">${total>0?(mTotal/total*100).toFixed(1)+'%':'–'}</td>
    </tr>`;
  }).join('') + `<tr style="background:var(--tiger-light);font-weight:700">
    <td>รวมทั้งหมด</td>
    ${mainCats.map(c=>`<td style="text-align:right">${fmt(data.filter(e=>e.c===c).reduce((s,e)=>s+e.a,0))}</td>`).join('')}
    <td style="text-align:right">${fmt(data.filter(e=>!mainCats.includes(e.c)).reduce((s,e)=>s+e.a,0))}</td>
    <td style="text-align:right;color:var(--red)">${fmt(grandTotal)}</td>
    <td style="text-align:right">100%</td>
  </tr>`;
}

// Close modal on overlay click
document.getElementById('addModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

// ==================== INIT ====================
async function initApp() {
  await loadDataFromServer();
  renderDashboard();
  applyFilters();
  renderSummaryTable();
}
initApp();
</script>
</body>
</html>