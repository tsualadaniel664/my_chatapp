<?php
// connexion.php

$host = 'localhost';
$dbname = 'chat';
$username = 'root';
$password = '';

try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Set PDO to throw exceptions on error
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Update last_activity if user is logged in
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['id'])) {
        $updateActivity = $bdd->prepare("UPDATE users SET last_activity = NOW() WHERE id_users = ?");
        $updateActivity->execute([$_SESSION['id']]);
    }
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>