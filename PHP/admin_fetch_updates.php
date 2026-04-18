<?php
session_start();
require 'connexion.php';

// Security check: only admins allowed
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get total users
$countStmt = $bdd->query("SELECT COUNT(*) FROM users");
$totalUsers = (int) $countStmt->fetchColumn();

// Get the most recently registered user
$latestUserStmt = $bdd->query("SELECT prenom, nom, created_at FROM users ORDER BY created_at DESC LIMIT 1");
$latestUser = $latestUserStmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'totalUsers' => $totalUsers,
    'latestUser' => $latestUser
]);
?>