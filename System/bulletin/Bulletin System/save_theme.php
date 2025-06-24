<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $theme = $_POST['theme'];
    // Save to session
    $_SESSION['theme'] = $theme;

    // Optionally, save to database for persistence
    // require_once 'connections.php';
    // $stmt = $con->prepare("UPDATE signuptbl SET theme = ? WHERE user_id = ?");
    // $stmt->bind_param("si", $theme, $_SESSION['user_id']);
    // $stmt->execute();
    // $stmt->close();

    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request']); 