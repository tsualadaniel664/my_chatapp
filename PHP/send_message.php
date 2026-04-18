<?php
// send_message.php
session_start();
require 'connexion.php';

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    exit("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['id'];
    $receiver_id = $_POST['receiver_id'] ?? null;
    $message = trim($_POST['message'] ?? '');

    if ($receiver_id && !empty($message)) {
        try {
            $stmt = $bdd->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$sender_id, $receiver_id, $message]);
            echo "Success";
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error";
        }
    } else {
        http_response_code(400);
    }
}
?>