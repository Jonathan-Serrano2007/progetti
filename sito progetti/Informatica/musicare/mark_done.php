<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['utente_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$cat = isset($_POST['cat']) ? preg_replace('/[^a-z0-9_\-]/i', '', $_POST['cat']) : '';

if ($id < 1 || $id > 100 || $cat === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_parameters']);
    exit;
}

if (!isset($_SESSION['completed']) || !is_array($_SESSION['completed'])) {
    $_SESSION['completed'] = [];
}

if (!isset($_SESSION['completed'][$cat]) || !is_array($_SESSION['completed'][$cat])) {
    $_SESSION['completed'][$cat] = [];
}

$_SESSION['completed'][$cat][intval($id)] = true;

echo json_encode(['ok' => true]);
exit;
