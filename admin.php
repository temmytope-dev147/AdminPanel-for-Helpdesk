<?php
require_once __DIR__ . "/config.php";
if (empty($_SESSION["user"]))           { header("Location: index.php"); exit; }
if (empty($_SESSION["user"]["is_admin"])){ header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - Sterling Assurance IT Help Desk</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
:root {
  --navy:  #0b1f4b;
  --blue:  #1751c8;
  --sky:   #3b82f6;
  --ice:   #e8f0fe;
  --gold:  #f59e0b;
  --red:   #dc2626;
  --green: #16a34a;
  --off:   #f7f8fc;
  --muted: #64748b;
  --border:#dde3f0;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:"DM Sans",sans-serif;background:var(--off);color:var(--navy);min-height:100vh}

.header{background:var(--navy);padding:0 32px;height:62px;display:flex;align-items:center;justify-content:space-between}
.brand{display:flex;align-items:center;gap:12px}
.brand-mark{width:36px;height:36px;background:var(--blue);border-radius:8px;display:grid;place-items:center;font-family:"DM Serif Display",serif;color:#fff;font-size:15px}
.brand h1{font-size:15px;font-weight:600;color:#fff}
.brand p{font-size:11px;color:rgba(255,255,255,0.45)}
.badge-admin{background:var(--gold);color:#78350f;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px}
.btn-logout{font-size:12px;font-weight:600;color:rgba(255,255,255,0.55);background:none;border:1px solid rgba(255,255,255,0.15);border-radius:8px;padding:6px 14px;cursor:pointer;font-family:inherit}
.btn-logout:hover{color:#fff;border-color:rgba(255,255,255,0.4)}

.main{max-width:1300px;margin:0 auto;padding:32px}

.page-title{margin-bottom:24px}
.page-title h2{font-family:"DM Serif Display",serif;font-size:26px;font-weight:400}
.page-title p{font-size:13px;color:var(--muted);margin-top:3px}

/* Stats */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px}
.stat{background:#fff;border-radius:12px;padding:20px 22px;border:1px solid var(--border);border-left:4px solid var(--blue);box-shadow:0 2px 12px rgba(11,31,75,0.07)}
.stat.s-open{border-left-color:var(--sky)}
.stat.s-prog{border-left-color:var(--gold)}
.stat.s-done{border-left-color:var(--green)}
.stat.s-all {border-left-color:var(--blue)}
.stat-label{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.6px;color:var(--muted);margin-bottom:8px}
.stat-num{font-size:34px;font-weight:700;line-height:1}

/* Filters */
.filters{display:flex;gap:12px;margin-bottom:20px;align-items:center;flex-wrap:wrap}
.filters select{padding:9px 14px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;background:#fff;color:var(--navy);cursor:pointer}
.filters select:focus{outline:none;border-color:var(--sky)}
.btn-refresh{padding:9px 18px;background:var(--blue);color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer}
.btn-refresh:hover{background:#1340a8}

/* Table */
.table-wrap{background:#fff;border-radius:14px;border:1px solid var(--border);box-shadow:0 2px 12px rgba(11,31,75,0.07);overflow:hidden}
table{width:100%;border-collapse:collapse}
thead tr{background:#f1f5fb}
th{padding:13px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--muted);text-align:left;white-space:nowrap}
td{padding:13px 16px;font-size:13px;border-top:1px solid var(--border);vertical-align:middle}
tr:hover td{background:#f7f9fe}
.tid{font-weight:700;color:var(--blue)}

/* Badges */
.badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap}
.b-open  {background:#dbeafe;color:#1d4ed8}
.b-prog  {background:#fef3c7;color:#b45309}
.b-done  {background:#dcfce7;color:#15803d}
.b-closed{background:#f1f5f9;color:#475569}
.b-crit  {background:#fee2e2;color:#b91c1c}
.b-high  {background:#ffedd5;color:#c2410c}
.b-med   {background:#fef9c3;color:#a16207}
.b-low   {background:#f0fdf4;color:#166534}

/* Status dropdown inside table */
.status-select{
  padding:5px 10px;border-radius:7px;border:1.5px solid var(--border);
  font-family:inherit;font-size:12px;font-weight:600;cursor:pointer;
  background:#fff;transition:border .2s;min-width:130px;
}
.status-select:focus{outline:none;border-color:var(--sky)}
.status-select.s-open  {background:#dbeafe;color:#1d4ed8;border-color:#bfdbfe}
.status-select.s-prog  {background:#fef3c7;color:#b45309;border-color:#fde68a}
.status-select.s-done  {background:#dcfce7;color:#15803d;border-color:#bbf7d0}
.status-select.s-closed{background:#f1f5f9;color:#475569;border-color:#e2e8f0}

.btn-save{padding:5px 12px;background:var(--blue);color:#fff;border:none;border-radius:7px;font-family:inherit;font-size:12px;font-weight:600;cursor:pointer;transition:background .2s}
.btn-save:hover{background:#1340a8}
.btn-save.saved{background:var(--green)}

.est-select{
  padding:5px 8px;border-radius:7px;border:1.5px solid var(--border);
  font-family:inherit;font-size:12px;color:var(--navy);background:#fff;
  cursor:pointer;min-width:110px;
}
.est-select:focus{outline:none;border-color:var(--sky)}
.resolved-input{
  padding:5px 9px;border-radius:7px;border:1.5px solid var(--border);
  font-family:inherit;font-size:12px;color:var(--navy);background:#fff;
  width:130px;
}
.resolved-input:focus{outline:none;border-color:var(--sky);box-shadow:0 0 0 2px rgba(59,130,246,0.1)}
.resolved-input::placeholder{color:#a0aec0}

/* Modal tracking section */
.tracking-section{background:var(--off);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:20px}
.tracking-section h4{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--muted);margin-bottom:12px}
.tracking-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.tracking-field label{font-size:11px;font-weight:600;color:var(--muted);display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:0.4px}
.tracking-field select,.tracking-field input{width:100%;padding:9px 11px;border:1.5px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;color:var(--navy);background:#fff}
.tracking-field select:focus,.tracking-field input:focus{outline:none;border-color:var(--sky)}

/* Modal */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(11,31,75,0.45);z-index:200;align-items:center;justify-content:center}
.modal-bg.open{display:flex}
.modal{background:#fff;border-radius:16px;padding:32px;max-width:560px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);position:relative}
.modal h3{font-family:"DM Serif Display",serif;font-size:20px;margin-bottom:4px}
.modal p.sub{font-size:13px;color:var(--muted);margin-bottom:20px}
.modal-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}
.meta-item label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--muted);display:block;margin-bottom:3px}
.meta-item span{font-size:13px;font-weight:500}
.desc-box{background:var(--off);border-radius:8px;padding:14px;font-size:13px;line-height:1.6;color:#334155;margin-bottom:20px;border:1px solid var(--border)}
.modal-close{position:absolute;top:16px;right:16px;background:none;border:none;font-size:22px;cursor:pointer;color:var(--muted)}
.modal-close:hover{color:var(--navy)}

/* Toast */
.toast{position:fixed;bottom:28px;right:28px;background:var(--navy);color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,0.2);z-index:300;transform:translateY(80px);opacity:0;transition:all .3s}
.toast.show{transform:translateY(0);opacity:1}
.toast.success{background:var(--green)}
.toast.error{background:var(--red)}

.loading{text-align:center;padding:40px;color:var(--muted);font-size:14px}

@media(max-width:900px){
  .stats{grid-template-columns:repeat(2,1fr)}
  .table-wrap{overflow-x:auto}
}
</style>
</head>
<body>

<header class="header">
  <div class="brand">
    <div class="brand-mark">SA</div>
    <div>
      <h1>IT Help Desk - Admin Panel</h1>
      <p>Sterling Assurance Nigeria Limited</p>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:14px">
    <span class="badge-admin">&#9881; Admin</span>
    <span id="adminName" style="font-size:13px;color:rgba(255,255,255,0.7)"></span>
    <button class="btn-logout" onclick="logout()">Logout</button>
  </div>
</header>

<main class="main">
  <div class="page-title">
    <h2>All Tickets</h2>
    <p>Manage and update the status of every support request</p>
  </div>

  <div class="stats">
    <div class="stat s-all" ><div class="stat-label">Total Tickets</div><div class="stat-num" id="s-total">-</div></div>
    <div class="stat s-open"><div class="stat-label">Open</div>         <div class="stat-num" id="s-open">-</div></div>
    <div class="stat s-prog"><div class="stat-label">In Progress</div>  <div class="stat-num" id="s-prog">-</div></div>
    <div class="stat s-done"><div class="stat-label">Resolved</div>     <div class="stat-num" id="s-done">-</div></div>
  </div>

  <div class="filters">
    <select id="filterStatus" onchange="loadAllTickets()">
      <option value="">All Statuses</option>
      <option value="Open">Open</option>
      <option value="In Progress">In Progress</option>
      <option value="Resolved">Resolved</option>
      <option value="Closed">Closed</option>
    </select>
    <select id="filterType" onchange="loadAllTickets()">
      <option value="">All Types</option>
      <option value="Hardware">Hardware</option>
      <option value="Software">Software</option>
    </select>
    <select id="filterBranch" onchange="loadAllTickets()">
      <option value="">All Branches</option>
      <option>Head Office - Lagos</option>
      <option>Abuja Branch</option>
      <option>Port Harcourt Branch</option>
      <option>Kaduna Branch</option>
      <option>NIIP</option>
      <option>Ado-ekiti Branch</option>
      <option>Calabar Branch</option>
      <option>Illorin Branch</option>
      <option>Warri Branch</option>
      <option>Retail Marketing</option>
      <option>BOI</option>
      <option>Kano Branch</option>
      <option>Ibadan Branch</option>
      <option>Marina Branch</option>
    </select>
    <button class="btn-refresh" onclick="loadAllTickets()">&#8635; Refresh</button>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Staff</th><th>Type</th><th>Subject</th>
          <th>Branch</th><th>Priority</th><th>Date</th><th>Status</th>
          <th>Est. Time</th><th>Resolved By</th><th>Save</th>
        </tr>
      </thead>
      <tbody id="adminTableBody">
        <tr><td colspan="11" class="loading">Loading tickets...</td></tr>
      </tbody>
    </table>
  </div>
</main>

<!-- Ticket Detail Modal -->
<div class="modal-bg" id="modalBg" onclick="closeModal(event)">
  <div class="modal">
    <button class="modal-close" onclick="document.getElementById('modalBg').classList.remove('open')">x</button>
    <h3 id="modalSubject"></h3>
    <p class="sub" id="modalMeta"></p>
    <div class="modal-meta">
      <div class="meta-item"><label>Staff</label>   <span id="mStaff"></span></div>
      <div class="meta-item"><label>Branch</label>  <span id="mBranch"></span></div>
      <div class="meta-item"><label>Priority</label><span id="mPriority"></span></div>
      <div class="meta-item"><label>Status</label>  <span id="mStatus"></span></div>
    </div>
    <div class="desc-box" id="mDesc"></div>

    <div class="tracking-section">
      <h4>&#128338; Ticket Tracking</h4>
      <div class="tracking-grid">
        <div class="tracking-field">
          <label>Update Status</label>
          <select class="status-select" id="modalStatusSelect" onchange="colorModalSelect()">
            <option>Open</option>
            <option>In Progress</option>
            <option>Resolved</option>
            <option>Closed</option>
          </select>
        </div>
        <div class="tracking-field">
          <label>Estimated Resolution Time</label>
          <select id="modalEstTime">
            <option value="">-- Not set --</option>
            <option value="1 hour">1 hour</option>
            <option value="2 hours">2 hours</option>
            <option value="4 hours">4 hours</option>
            <option value="8 hours">8 hours</option>
            <option value="1 day">1 day</option>
            <option value="2 days">2 days</option>
            <option value="3 days">3 days</option>
            <option value="1 week">1 week</option>
            <option value="2 weeks">2 weeks</option>
            <option value="1 month">1 month</option>
            <option value="2 months">2 months</option>
            <option value="3 months">3 months</option>
            <option value="6 months">6 months</option>
          </select>
        </div>
        <div class="tracking-field" style="grid-column:1/-1">
          <label>Resolved By</label>
          <input type="text" id="modalResolvedBy" placeholder="IT staff name who resolved this ticket">
        </div>
      </div>
    </div>

    <button class="btn-save" id="modalSaveBtn" onclick="saveModalStatus()" style="width:100%;padding:11px;font-size:13px">Save Changes</button>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
var currentTicketId = null;

// Auth check - JS double-guard (PHP already guards at top)
fetch("api.php?action=me")
  .then(function(r){ return r.json(); })
  .then(function(data) {
    if (!data.success || !data.user.is_admin) { window.location.href = "index.php"; return; }
    document.getElementById("adminName").textContent = data.user.fullname || data.user.email;
    loadAllTickets();
  })
  .catch(function(){ window.location.href = "index.php"; });

function loadAllTickets() {
  var status = document.getElementById("filterStatus").value;
  var type   = document.getElementById("filterType").value;
  var branch = document.getElementById("filterBranch").value;

  var url = "api.php?action=fetch_all_tickets";
  if (status) url += "&status=" + encodeURIComponent(status);
  if (type)   url += "&type="   + encodeURIComponent(type);
  if (branch) url += "&branch=" + encodeURIComponent(branch);

  fetch(url)
    .then(function(r){ return r.json(); })
    .then(function(data) {
      if (!data.success) { showToast(data.message || "Failed to load", "error"); return; }
      renderTable(data.tickets);
      updateStats(data.tickets);
    })
    .catch(function(){ showToast("Failed to load tickets", "error"); });
}

function renderTable(tickets) {
  var tbody = document.getElementById("adminTableBody");
  var estOpts = ["1 hour","2 hours","4 hours","8 hours","1 day","2 days","3 days","1 week","2 weeks","1 month","2 months","3 months","6 months"];
  if (!tickets || !tickets.length) {
    tbody.innerHTML = "<tr><td colspan='11' class='loading'>No tickets found</td></tr>";
    return;
  }
  tbody.innerHTML = tickets.map(function(t) {
    var tJson = JSON.stringify(t).replace(/"/g, "&quot;");
    var estOptions = "<option value=''>-- Not set --</option>" + estOpts.map(function(o){
      return "<option" + (t.est_time === o ? " selected" : "") + " value='" + o + "'>" + o + "</option>";
    }).join("");
    return "<tr>" +
      "<td class='tid' style='cursor:pointer' onclick='openModal(" + tJson + ")'>#" + t.id + "</td>" +
      "<td>" + esc(t.staff_name || t.staff_email || "-") + "</td>" +
      "<td>" + esc(t.type) + "</td>" +
      "<td style='max-width:180px;cursor:pointer' onclick='openModal(" + tJson + ")'><span title='" + esc(t.subject) + "'>" + esc(t.subject) + "</span></td>" +
      "<td>" + esc(t.branch || "-") + "</td>" +
      "<td><span class='badge " + priorityClass(t.priority) + "'>"+esc(t.priority)+"</span></td>" +
      "<td style='white-space:nowrap'>"+formatDate(t.created_at)+"</td>" +
      "<td><select class='status-select " + statusClass(t.status) + "' id='sel-" + t.id + "' onchange='colorSelect(this)'>" +
        "<option" + (t.status === "Open"        ? " selected" : "") + ">Open</option>" +
        "<option" + (t.status === "In Progress" ? " selected" : "") + ">In Progress</option>" +
        "<option" + (t.status === "Resolved"    ? " selected" : "") + ">Resolved</option>" +
        "<option" + (t.status === "Closed"      ? " selected" : "") + ">Closed</option>" +
      "</select></td>" +
      "<td><select class='est-select' id='est-" + t.id + "'>" + estOptions + "</select></td>" +
      "<td><input class='resolved-input' id='res-" + t.id + "' type='text' placeholder='Name...' value='" + esc(t.resolved_by || "") + "'></td>" +
      "<td><button class='btn-save' id='btn-" + t.id + "' onclick='saveStatus(" + t.id + ")'>Save</button></td>" +
      "</tr>";
  }).join("");
}

function updateStats(tickets) {
  document.getElementById("s-total").textContent = tickets.length;
  document.getElementById("s-open").textContent  = tickets.filter(function(t){ return t.status === "Open"; }).length;
  document.getElementById("s-prog").textContent  = tickets.filter(function(t){ return t.status === "In Progress"; }).length;
  document.getElementById("s-done").textContent  = tickets.filter(function(t){ return t.status === "Resolved"; }).length;
}

function saveStatus(id) {
  var sel        = document.getElementById("sel-" + id);
  var estSel     = document.getElementById("est-" + id);
  var resInput   = document.getElementById("res-" + id);
  var btn        = document.getElementById("btn-" + id);
  var status     = sel.value;
  var estTime    = estSel ? estSel.value : "";
  var resolvedBy = resInput ? resInput.value.trim() : "";

  fetch("api.php?action=update_status", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "ticket_id=" + id +
          "&status="      + encodeURIComponent(status) +
          "&est_time="    + encodeURIComponent(estTime) +
          "&resolved_by=" + encodeURIComponent(resolvedBy)
  })
  .then(function(r){ return r.json(); })
  .then(function(data) {
    if (data.success) {
      btn.textContent = "Saved";
      btn.classList.add("saved");
      setTimeout(function(){ btn.textContent = "Save"; btn.classList.remove("saved"); }, 2000);
      showToast("Ticket #" + id + " updated", "success");
    } else {
      showToast(data.message || "Update failed", "error");
    }
  })
  .catch(function(){ showToast("Network error", "error"); });
}

function openModal(t) {
  currentTicketId = t.id;
  document.getElementById("modalSubject").textContent = t.subject || "-";
  document.getElementById("modalMeta").textContent    = "#" + t.id + " - " + (t.type || "") + " - " + formatDate(t.created_at);
  document.getElementById("mStaff").textContent       = t.staff_name || t.staff_email || "-";
  document.getElementById("mBranch").textContent      = t.branch || "-";
  document.getElementById("mPriority").innerHTML      = "<span class='badge " + priorityClass(t.priority) + "'>"+esc(t.priority)+"</span>";
  document.getElementById("mStatus").innerHTML        = "<span class='badge " + statusBadgeClass(t.status) + "'>"+esc(t.status)+"</span>";
  document.getElementById("mDesc").textContent        = t.description || "-";

  var sel = document.getElementById("modalStatusSelect");
  sel.value = t.status;
  colorModalSelect();

  document.getElementById("modalEstTime").value    = t.est_time    || "";
  document.getElementById("modalResolvedBy").value = t.resolved_by || "";

  document.getElementById("modalBg").classList.add("open");
}

function closeModal(e) {
  if (e.target.id === "modalBg") document.getElementById("modalBg").classList.remove("open");
}

function saveModalStatus() {
  var status     = document.getElementById("modalStatusSelect").value;
  var estTime    = document.getElementById("modalEstTime").value;
  var resolvedBy = document.getElementById("modalResolvedBy").value.trim();
  var btn        = document.getElementById("modalSaveBtn");

  btn.textContent = "Saving...";
  btn.disabled    = true;

  fetch("api.php?action=update_status", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "ticket_id=" + currentTicketId +
          "&status="      + encodeURIComponent(status) +
          "&est_time="    + encodeURIComponent(estTime) +
          "&resolved_by=" + encodeURIComponent(resolvedBy)
  })
  .then(function(r){ return r.json(); })
  .then(function(data) {
    btn.textContent = "Save Changes";
    btn.disabled    = false;
    if (data.success) {
      btn.textContent = "Saved!";
      btn.classList.add("saved");
      setTimeout(function(){ btn.textContent = "Save Changes"; btn.classList.remove("saved"); }, 2000);
      showToast("Ticket #" + currentTicketId + " updated to " + status, "success");
      document.getElementById("modalBg").classList.remove("open");
      loadAllTickets();
    } else {
      showToast(data.message || "Update failed", "error");
    }
  })
  .catch(function(){
    btn.textContent = "Save Changes";
    btn.disabled    = false;
    showToast("Network error", "error");
  });
}

function colorSelect(sel) { sel.className = "status-select " + statusClass(sel.value); }
function colorModalSelect() {
  var sel = document.getElementById("modalStatusSelect");
  sel.className = "status-select " + statusClass(sel.value);
}

function statusClass(s) {
  if (s === "Open")        return "s-open";
  if (s === "In Progress") return "s-prog";
  if (s === "Resolved")    return "s-done";
  if (s === "Closed")      return "s-closed";
  return "s-open";
}
function statusBadgeClass(s) {
  if (s === "Open")        return "b-open";
  if (s === "In Progress") return "b-prog";
  if (s === "Resolved")    return "b-done";
  if (s === "Closed")      return "b-closed";
  return "b-open";
}
function priorityClass(p) {
  var pl = (p || "").toLowerCase();
  if (pl === "critical") return "b-crit";
  if (pl === "high")     return "b-high";
  if (pl === "medium")   return "b-med";
  if (pl === "low")      return "b-low";
  return "b-med";
}
function esc(str) {
  if (!str) return "-";
  return String(str).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
}
function formatDate(str) {
  if (!str) return "-";
  var d = new Date(str);
  return isNaN(d) ? str : d.toLocaleDateString("en-NG",{day:"2-digit",month:"short",year:"numeric"});
}
function showToast(msg, type) {
  var t = document.getElementById("toast");
  t.textContent = msg;
  t.className = "toast " + (type || "") + " show";
  setTimeout(function(){ t.classList.remove("show"); }, 3000);
}
function logout() {
  fetch("api.php?action=logout", { method: "POST" })
    .then(function(){ window.location.href = "index.php"; })
    .catch(function(){ window.location.href = "index.php"; });
}
</script>
</body>
</html>
