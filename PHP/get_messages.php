<?php
// get_messages.php
session_start();
require 'connexion.php';

if (!isset($_SESSION['id']) || !isset($_GET['receiver_id'])) {
    exit("");
}

$my_id = $_SESSION['id'];
$other_id = $_GET['receiver_id'];

// Get other user's info for avatar
$stmtUser = $bdd->prepare("SELECT photo, prenom, nom FROM users WHERE id_users = ?");
$stmtUser->execute([$other_id]);
$otherUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
$otherPhoto = !empty($otherUser['photo']) ? '../images/' . $otherUser['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($otherUser['prenom'] . ' ' . $otherUser['nom']) . '&background=random&color=fff&size=64';

// Mark messages as read
$update = $bdd->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
$update->execute([$other_id, $my_id]);

// Fetch messages between these two users
$sql = "SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC";
$stmt = $bdd->prepare($sql);
$stmt->execute([$my_id, $other_id, $other_id, $my_id]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$html = '';
$lastId = 0;

foreach ($messages as $msg) {
    if ($msg['id_messages'] > $lastId) {
        $lastId = $msg['id_messages'];
    }

    $isMe = ($msg['sender_id'] == $my_id);
    $cls = $isMe ? 'sent' : 'received';
    $time = date('H:i', strtotime($msg['created_at']));

    $html .= '<div class="message ' . $cls . '">';
    if (!$isMe) {
        $html .= '<img src="' . htmlspecialchars($otherPhoto) . '" class="avatar" alt="Avatar">';
    }
    $html .= '<div class="content">';
    $html .= nl2br(htmlspecialchars($msg['message']));
    $html .= '<span class="time">' . $time . '</span>';
    $html .= '</div>';
    $html .= '</div>';
}

header('Content-Type: application/json');
echo json_encode([
    'html' => $html,
    'lastId' => $lastId
]);
?>