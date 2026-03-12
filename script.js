/**
 * script.js — Sterling Assurance IT Help Desk
 * Handles login, ticket submission, ticket listing, navigation and logout.
 */

document.addEventListener("DOMContentLoaded", function () {

    // ── Check session on load ─────────────────────────────────────────────────
    fetch("api.php?action=me")
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showDashboard(data.user);
            } else {
                showLoginPage();
            }
        })
        .catch(() => showLoginPage());

    // ── Login form ────────────────────────────────────────────────────────────
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const email    = document.getElementById("loginEmail").value.trim();
            const password = document.getElementById("loginPassword").value;
            const errorEl  = document.getElementById("loginError");

            errorEl.classList.add("hidden");
            errorEl.innerText = "";

            fetch("api.php?action=login", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showDashboard(data.user);
                } else {
                    errorEl.innerText = data.message || "Login failed. Please try again.";
                    errorEl.classList.remove("hidden");
                }
            })
            .catch(() => {
                errorEl.innerText = "Network error. Please try again.";
                errorEl.classList.remove("hidden");
            });
        });
    }

    // ── Hardware ticket form ──────────────────────────────────────────────────
    const hardwareForm = document.getElementById("hardwareForm");
    if (hardwareForm) {
        hardwareForm.addEventListener("submit", function (e) {
            e.preventDefault();
            submitTicket("hardware");
        });
    }

    // ── Software ticket form ──────────────────────────────────────────────────
    const softwareForm = document.getElementById("softwareForm");
    if (softwareForm) {
        softwareForm.addEventListener("submit", function (e) {
            e.preventDefault();
            submitTicket("software");
        });
    }
});

// ── Page visibility helpers ───────────────────────────────────────────────────

function showLoginPage() {
    document.getElementById("loginPage").style.display  = "flex";
    document.getElementById("dashboardPage").classList.remove("active");
}

function showDashboard(user) {
    document.getElementById("loginPage").style.display = "none";
    document.getElementById("dashboardPage").classList.add("active");

    // Populate user info in header
    const nameEl  = document.getElementById("userName");
    const emailEl = document.getElementById("userEmail");
    if (nameEl)  nameEl.innerText  = user.fullname || user.email;
    if (emailEl) emailEl.innerText = user.email;

    // Show Admin Panel link if user is admin
    const adminLink = document.getElementById("adminPanelLink");
    if (adminLink) {
        if (user.is_admin) {
            adminLink.style.display = "inline-block";
        } else {
            adminLink.style.display = "none";
        }
    }

    loadTickets();
    showPage("dashboard");

    // Auto-refresh ticket statuses every 30 seconds
    if (window._ticketRefreshTimer) clearInterval(window._ticketRefreshTimer);
    window._ticketRefreshTimer = setInterval(loadTickets, 30000);
}

function showPage(page) {
    // Update nav buttons
    document.querySelectorAll(".nav-btn").forEach(btn => btn.classList.remove("active"));
    const activeBtn = document.querySelector(`.nav-btn[onclick*="'${page}'"]`);
    if (activeBtn) activeBtn.classList.add("active");

    // Show correct section
    document.querySelectorAll(".page-section").forEach(s => s.classList.remove("active"));
    const sectionMap = {
        dashboard : "dashboardSection",
        hardware  : "hardwareSection",
        software  : "softwareSection",
        tickets   : "ticketsSection"
    };
    const sectionId = sectionMap[page];
    if (sectionId) {
        document.getElementById(sectionId).classList.add("active");
    }

    // Refresh ticket list when navigating to My Tickets
    if (page === "tickets") loadTickets();
}

// ── Ticket submission ─────────────────────────────────────────────────────────

function submitTicket(type) {
    const prefix      = type;                     // "hardware" or "software"
    const subject     = document.getElementById(prefix + "Subject").value.trim();
    const description = document.getElementById(prefix + "Description").value.trim();
    const priority    = document.getElementById(prefix + "Priority").value;
    const branch      = document.getElementById(prefix + "Branch").value;
    const successEl   = document.getElementById(prefix + "Success");

    successEl.classList.add("hidden");
    successEl.innerText = "";

    if (!branch) {
        showAlert(successEl, "Please select a branch.", false);
        return;
    }

    const body = [
        `type=${encodeURIComponent(type === "hardware" ? "Hardware" : "Software")}`,
        `subject=${encodeURIComponent(subject)}`,
        `description=${encodeURIComponent(description)}`,
        `priority=${encodeURIComponent(priority)}`,
        `branch=${encodeURIComponent(branch)}`
    ].join("&");

    fetch("api.php?action=create_ticket", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert(successEl, "✅ Ticket submitted successfully!", true);
            document.getElementById(prefix + "Form").reset();
            document.getElementById(prefix + "Attachments").innerHTML = "";
            loadTickets();
        } else {
            showAlert(successEl, data.message || "Failed to submit ticket.", false);
        }
    })
    .catch(() => showAlert(successEl, "Network error. Please try again.", false));
}

function showAlert(el, message, success) {
    el.innerText = message;
    el.style.background     = success ? "#e8f5e9" : "#ffebee";
    el.style.color          = success ? "#2e7d32" : "#c62828";
    el.style.borderLeft     = success ? "4px solid #2e7d32" : "4px solid #c62828";
    el.classList.remove("hidden");
    el.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

// ── Load & render tickets ─────────────────────────────────────────────────────

function loadTickets() {
    fetch("api.php?action=fetch_tickets")
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;

            const tickets = data.tickets || [];
            renderTicketsTable(tickets);
            renderRecentTickets(tickets);
            updateStats(tickets);
        })
        .catch(console.error);
}

function renderTicketsTable(tickets) {
    const tbody = document.getElementById("ticketsTableBody");
    if (!tbody) return;

    if (tickets.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty-table-message">No tickets found</td></tr>`;
        return;
    }

    tbody.innerHTML = tickets.map(t => `
        <tr>
            <td class="ticket-id">#${t.id}</td>
            <td>${escHtml(t.type)}</td>
            <td>${escHtml(t.subject)}</td>
            <td>${escHtml(t.branch)}</td>
            <td><span class="badge badge-priority-${(t.priority || '').toLowerCase()}">${escHtml(t.priority)}</span></td>
            <td><span class="badge ${statusBadgeClass(t.status)}">${escHtml(t.status)}</span></td>
            <td>${formatDate(t.created_at)}</td>
        </tr>
    `).join("");
}

function renderRecentTickets(tickets) {
    const container = document.getElementById("recentTickets");
    if (!container) return;

    const recent = tickets.slice(0, 5);

    if (recent.length === 0) {
        container.innerHTML = `<p style="color:#999;font-size:13px;">No recent tickets</p>`;
        return;
    }

    container.innerHTML = recent.map(t => `
        <div class="recent-ticket">
            <div class="recent-ticket-header">
                <h4>${escHtml(t.subject)}</h4>
                <span class="badge ${statusBadgeClass(t.status)}">${escHtml(t.status)}</span>
            </div>
            <p>${escHtml(t.type)} · ${escHtml(t.branch)} · ${formatDate(t.created_at)}</p>
        </div>
    `).join("");
}

function updateStats(tickets) {
    const open     = tickets.filter(t => t.status === "Open").length;
    const progress = tickets.filter(t => t.status === "In Progress").length;
    const resolved = tickets.filter(t => t.status === "Resolved").length;

    setEl("openCount",     open);
    setEl("progressCount", progress);
    setEl("resolvedCount", resolved);
}

// ── Logout ────────────────────────────────────────────────────────────────────

function logout() {
    fetch("api.php?action=logout", { method: "POST" })
        .then(() => showLoginPage())
        .catch(() => showLoginPage());
}

// ── File upload preview ───────────────────────────────────────────────────────

function handleFileUpload(event, type) {
    const files    = Array.from(event.target.files);
    const listEl   = document.getElementById(type + "Attachments");

    files.forEach(file => {
        const item = document.createElement("div");
        item.className = "attachment-item";
        item.innerHTML = `
            <div class="attachment-info">
                <span>📄</span>
                <strong>${escHtml(file.name)}</strong>
                <span>${formatBytes(file.size)}</span>
            </div>
            <button class="btn-remove" title="Remove" onclick="this.parentElement.remove()">×</button>
        `;
        listEl.appendChild(item);
    });
}

// ── Utility helpers ───────────────────────────────────────────────────────────

function escHtml(str) {
    if (str == null) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function formatDate(str) {
    if (!str) return "—";
    const d = new Date(str);
    return isNaN(d) ? str : d.toLocaleDateString("en-NG", {
        day: "2-digit", month: "short", year: "numeric"
    });
}

function formatBytes(bytes) {
    if (bytes < 1024)       return bytes + " B";
    if (bytes < 1048576)    return (bytes / 1024).toFixed(1)    + " KB";
    return (bytes / 1048576).toFixed(1) + " MB";
}

function statusBadgeClass(status) {
    switch ((status || "").toLowerCase()) {
        case "open":        return "badge-open";
        case "in progress": return "badge-progress";
        case "resolved":    return "badge-resolved";
        default:            return "badge-open";
    }
}

function setEl(id, value) {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
}
