<?php
// api.php - REST-style JSON API for the Help Desk
require_once __DIR__ . "/config.php";

$action = sanitizeInput($_GET["action"] ?? "");

switch ($action) {
    case "login":
        actionLogin();
        break;
    case "create_ticket":
        requireLogin();
        actionCreateTicket();
        break;
    case "fetch_tickets":
        requireLogin();
        actionFetchTickets();
        break;
    case "fetch_all_tickets":
        requireLogin();
        actionFetchAllTickets();
        break;
    case "update_status":
        requireLogin();
        actionUpdateStatus();
        break;
    case "logout":
        actionLogout();
        break;
    case "me":
        if (!empty($_SESSION["user"])) {
            respond(["success" => true, "user" => $_SESSION["user"]]);
        } else {
            respond(["success" => false, "message" => "Not logged in"]);
        }
        break;
    default:
        respond(["success" => false, "message" => "Invalid action"], 400);
}

function actionLogin(): void {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        respond(["success" => false, "message" => "POST required"], 405);
    }

    $email    = sanitizeInput($_POST["email"]    ?? "");
    $password = $_POST["password"] ?? "";

    if (!$email || !$password) {
        respond(["success" => false, "message" => "Email and password are required"]);
    }

    // DEV MODE - remove and use DB check below for production
    if (str_ends_with(strtolower($email), "@sterlingassure.com")) {
        $localPart = strtolower(explode("@", $email)[0]);
        $nameParts = explode(".", $localPart);
        $fullname  = ucfirst($nameParts[0]) . " " . ucfirst($nameParts[1] ?? "");
        $adminPrefixes = ["admin", "it", "itsupport", "helpdesk"];
        $isAdmin = (int)(in_array($localPart, $adminPrefixes, true) || str_contains($localPart, "admin"));
        $_SESSION["user"] = [
            "id"       => 1,
            "fullname" => trim($fullname),
            "email"    => $email,
            "is_admin" => $isAdmin,
        ];
        respond(["success" => true, "user" => $_SESSION["user"]]);
    }

    // PRODUCTION DB CHECK (uncomment when ready)
    // $conn = getDBConnection();
    // $stmt = $conn->prepare("SELECT * FROM userlog WHERE email = :email");
    // $stmt->execute([": email" => $email]);
    // $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // if (!$user) { respond(["success" => false, "message" => "Invalid email or password"]); }
    // $pwdCol = null;
    // foreach (["password_hash","password","passwd","pass","pwd"] as $col) {
    //     if (array_key_exists($col, $user)) { $pwdCol = $col; break; }
    // }
    // $stored = $user[$pwdCol] ?? "";
    // if (!password_verify($password, $stored) && $stored !== $password) {
    //     respond(["success" => false, "message" => "Invalid email or password"]);
    // }
    // $nameCol = null;
    // foreach (["fullname","full_name","name","username","staff_name"] as $col) {
    //     if (array_key_exists($col, $user)) { $nameCol = $col; break; }
    // }
    // $_SESSION["user"] = [
    //     "id"       => $user["id"],
    //     "fullname" => $nameCol ? $user[$nameCol] : $email,
    //     "email"    => $user["email"],
    //     "is_admin" => (int)($user["is_admin"] ?? $user["admin"] ?? 0),
    // ];
    // respond(["success" => true, "user" => $_SESSION["user"]]);

    respond(["success" => false, "message" => "Please use your @sterlingassure.com email"]);
}

function actionCreateTicket(): void {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        respond(["success" => false, "message" => "POST required"], 405);
    }
    $userId      = (int) $_SESSION["user"]["id"];
    $subject     = sanitizeInput($_POST["subject"]     ?? "");
    $description = sanitizeInput($_POST["description"] ?? "");
    $priority    = sanitizeInput($_POST["priority"]    ?? "medium");
    $type        = sanitizeInput($_POST["type"]        ?? "Software");
    $branch      = sanitizeInput($_POST["branch"]      ?? "");

    if (!$subject || !$description) {
        respond(["success" => false, "message" => "Subject and description are required"]);
    }
    $allowed = ["low", "medium", "high", "critical"];
    if (!in_array(strtolower($priority), $allowed, true)) { $priority = "medium"; }

    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "INSERT INTO tickets (user_id, type, subject, description, branch, priority, status, created_at)"
        . " VALUES (:user_id, :type, :subject, :description, :branch, :priority, 'Open', GETDATE())"
    );
    $stmt->execute([
        ":user_id"     => $userId,
        ":type"        => $type,
        ":subject"     => $subject,
        ":description" => $description,
        ":branch"      => $branch,
        ":priority"    => ucfirst(strtolower($priority)),
    ]);
    respond(["success" => true, "message" => "Ticket created successfully"]);
}

function actionFetchTickets(): void {
    $userId = (int) $_SESSION["user"]["id"];
    $conn   = getDBConnection();
    $stmt   = $conn->prepare(
        "SELECT id, type, subject, branch, priority, status, created_at"
        . " FROM tickets WHERE user_id = :user_id ORDER BY created_at DESC"
    );
    $stmt->execute([": user_id" => $userId]);
    respond(["success" => true, "tickets" => $stmt->fetchAll()]);
}

function actionFetchAllTickets(): void {
    if (empty($_SESSION["user"]["is_admin"])) {
        respond(["success" => false, "message" => "Admin access required"], 403);
    }
    $conn   = getDBConnection();
    $status = sanitizeInput($_GET["status"] ?? "");
    $type   = sanitizeInput($_GET["type"]   ?? "");
    $branch = sanitizeInput($_GET["branch"] ?? "");
    $where  = []; $params = [];
    if ($status) { $where[] = "t.status = :status"; $params[":status"] = $status; }
    if ($type)   { $where[] = "t.type = :type";     $params[":type"]   = $type; }
    if ($branch) { $where[] = "t.branch = :branch"; $params[":branch"] = $branch; }
    $whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

    try {
        $stmt = $conn->prepare(
            "SELECT t.id, t.type, t.subject, t.branch, t.priority, t.status,"
            . " t.description, t.created_at, t.est_time, t.resolved_by,"
            . " COALESCE(u.fullname, u.full_name, u.name, u.username, '') AS staff_name,"
            . " u.email AS staff_email"
            . " FROM tickets t"
            . " LEFT JOIN userlog u ON u.id = t.user_id"
            . " $whereSQL"
            . " ORDER BY t.created_at DESC"
        );
        $stmt->execute($params);
        respond(["success" => true, "tickets" => $stmt->fetchAll()]);
    } catch (\PDOException $e) {
        // Fallback without user join
        try {
            $stmt2 = $conn->prepare(
                "SELECT id, type, subject, branch, priority, status, description, created_at, est_time, resolved_by"
                . " FROM tickets $whereSQL ORDER BY created_at DESC"
            );
            $stmt2->execute($params);
            respond(["success" => true, "tickets" => $stmt2->fetchAll()]);
        } catch (\PDOException $e2) {
            respond(["success" => false, "message" => "DB error: " . $e2->getMessage()], 500);
        }
    }
}

function actionUpdateStatus(): void {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        respond(["success" => false, "message" => "POST required"], 405);
    }
    if (empty($_SESSION["user"]["is_admin"])) {
        respond(["success" => false, "message" => "Admin access required"], 403);
    }
    $ticketId   = (int) ($_POST["ticket_id"] ?? 0);
    $status     = sanitizeInput($_POST["status"]      ?? "");
    $estTime    = sanitizeInput($_POST["est_time"]    ?? "");
    $resolvedBy = sanitizeInput($_POST["resolved_by"] ?? "");

    $allowed = ["Open", "In Progress", "Resolved", "Closed"];
    if (!$ticketId || !in_array($status, $allowed, true)) {
        respond(["success" => false, "message" => "Invalid ticket ID or status"]);
    }
    $allowedEst = ["", "1 hour", "2 hours", "4 hours", "8 hours", "1 day", "2 days", "3 days", "1 week", "2 weeks"];
    if (!in_array($estTime, $allowedEst, true)) { $estTime = ""; }

    $conn = getDBConnection();
    $stmt = $conn->prepare(
        "UPDATE tickets SET status = :status, est_time = :est_time, resolved_by = :resolved_by WHERE id = :id"
    );
    $stmt->execute([
        ":status"      => $status,
        ":est_time"    => $estTime    ?: null,
        ":resolved_by" => $resolvedBy ?: null,
        ":id"          => $ticketId,
    ]);
    respond(["success" => true, "message" => "Ticket updated"]);
}

function actionLogout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    session_destroy();
    respond(["success" => true, "message" => "Logged out"]);
}
