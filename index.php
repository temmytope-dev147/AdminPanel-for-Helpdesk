<?php
require_once __DIR__ . "/config.php";
if (!empty($_SESSION["user"])) {
    if (!empty($_SESSION["user"]["is_admin"])) { header("Location: admin.php"); exit; }
    // Non-admin tried to access admin portal - deny
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Sterling Assurance IT Help Desk</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:"DM Sans",sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0b1f4b 0%,#1751c8 100%)}

.login-box{background:#fff;border-radius:20px;padding:48px 40px;width:100%;max-width:420px;box-shadow:0 24px 64px rgba(0,0,0,0.25)}

.logo-wrap{text-align:center;margin-bottom:32px}
.logo-wrap img{width:70px;height:70px;object-fit:contain;border-radius:12px;display:block;margin:0 auto 14px}
.logo-wrap h1{font-family:"DM Serif Display",serif;font-size:22px;font-weight:400;color:#0b1f4b;margin-bottom:4px}
.logo-wrap p{font-size:13px;color:#64748b}

.admin-tag{display:inline-flex;align-items:center;gap:6px;background:#fef3c7;color:#b45309;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;border:1px solid #fde68a;margin-bottom:28px}

.field{margin-bottom:18px}
.field label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#64748b;margin-bottom:6px}
.field input{width:100%;padding:12px 14px;border:1.5px solid #dde3f0;border-radius:9px;font-family:"DM Sans",sans-serif;font-size:14px;color:#0b1f4b;background:#fff;transition:border-color .2s}
.field input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1)}
.field input::placeholder{color:#a0aec0}

.btn-login{width:100%;padding:13px;background:#1751c8;color:#fff;border:none;border-radius:9px;font-family:"DM Sans",sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;margin-top:4px}
.btn-login:hover{background:#1340a8}
.btn-login:disabled{opacity:.65;pointer-events:none}

.err{background:#fee2e2;color:#b91c1c;border-left:4px solid #dc2626;border-radius:8px;padding:11px 14px;font-size:13px;margin-bottom:16px;display:none}
.err.show{display:block}

.help{margin-top:24px;padding-top:20px;border-top:1px solid #dde3f0;text-align:center;font-size:12px;color:#64748b;line-height:1.9}
.help a{color:#1751c8;text-decoration:none}
</style>
</head>
<body>
<div class="login-box">
    <div class="logo-wrap">
        <img src="https://sterlingassure.com/assets/uploads/logo.jpg" alt="Sterling Assurance"
             onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2270%22 height=%2270%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%231751c8%22/><text x=%2214%22 y=%2265%22 fill=%22white%22 font-size=%2248%22 font-weight=%22bold%22>SA</text></svg>'">
        <h1>IT Help Desk</h1>
        <p>Sterling Assurance Nigeria Limited</p>
    </div>

    <div style="text-align:center">
        <div class="admin-tag">&#9881; Admin Portal</div>
    </div>

    <div class="err" id="errBox"></div>

    <form id="loginForm">
        <div class="field">
            <label>Email Address</label>
            <input type="email" id="loginEmail" placeholder="admin@sterlingassure.com" autocomplete="username" required>
        </div>
        <div class="field">
            <label>Password</label>
            <input type="password" id="loginPassword" placeholder="Enter your password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn-login" id="btnLogin">Sign In to Admin Panel</button>
    </form>

    <div class="help">
        <p>IT Support: <a href="mailto:itsupport@sterlingassure.com">itsupport@sterlingassure.com</a></p>
        <p>Tel: +0800Sterling</p>
    </div>
</div>

<script>
document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault();
    var email    = document.getElementById("loginEmail").value.trim();
    var password = document.getElementById("loginPassword").value;
    var errBox   = document.getElementById("errBox");
    var btn      = document.getElementById("btnLogin");

    errBox.classList.remove("show");
    btn.textContent = "Signing in...";
    btn.disabled    = true;

    fetch("api.php?action=login", {
        method:  "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:    "email=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(password)
    })
    .then(function(r){ return r.json(); })
    .then(function(d) {
        btn.textContent = "Sign In to Admin Panel";
        btn.disabled    = false;
        if (d.success && d.user.is_admin) {
            window.location.href = "admin.php";
        } else if (d.success && !d.user.is_admin) {
            errBox.textContent = "Access denied. This portal is for admins only.";
            errBox.classList.add("show");
        } else {
            errBox.textContent = d.message || "Login failed. Please try again.";
            errBox.classList.add("show");
        }
    })
    .catch(function() {
        btn.textContent = "Sign In to Admin Panel";
        btn.disabled    = false;
        errBox.textContent = "Network error. Please try again.";
        errBox.classList.add("show");
    });
});
</script>
</body>
</html>
