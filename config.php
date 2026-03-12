<?php
session_start();

function getDBConnection() {
    $serverName = "localhost";    // your SQL Server name
    $database   = "SA_HelpDesk";    // your database name
    $username   = "helpdesk_user";          // your SQL Server username
    $password   = "Sterling123?"; // your SQL Server password

    try {
        $conn = new PDO(
            "sqlsrv:Server=$serverName;Database=$database",
            $username,
            $password
        );
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch (PDOException $e) {
        die(json_encode(['success' => false, 'message' => 'DB Connection failed: ' . $e->getMessage()]));
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function requireLogin() {
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
}

function respond($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}