<?php
session_start();
require 'connexion.php';

if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit;
}

$my_id = $_SESSION['id'];

// Update my own last activity
$updateActivity = $bdd->prepare("UPDATE users SET last_activity = NOW() WHERE id_users = ?");
$updateActivity->execute([$my_id]);

// Fetch users with unread count, last activity, and last message time
$sql = "SELECT id_users, last_activity, 
       (SELECT COUNT(*) FROM messages m WHERE m.sender_id = users.id_users AND m.receiver_id = ? AND m.is_read = 0) as unread_count,
       (SELECT MAX(created_at) FROM messages m WHERE (m.sender_id = users.id_users AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = users.id_users)) as last_message_time
       FROM users 
       WHERE id_users != ?";

$stmt = $bdd->prepare($sql);
$stmt->execute([$my_id, $my_id, $my_id, $my_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
$current_time = new DateTime();

foreach ($users as $user) {
    $is_online = false;
    if (!empty($user['last_activity'])) {
        $last_activity = new DateTime($user['last_activity']);
        $interval = $current_time->diff($last_activity);
        // Online if active < 5 mins ago
        if ($interval->y == 0 && $interval->m == 0 && $interval->d == 0 && $interval->h == 0 && $interval->i < 5) {
            $is_online = true;
        }
    }

    $data[] = [
        'id' => $user['id_users'],
        'unread' => (int) $user['unread_count'],
        'online' => $is_online,
        'last_message_time' => $user['last_message_time'] ?? '0000-00-00 00:00:00',
        'last_activity' => $user['last_activity'] ?? '0000-00-00 00:00:00'
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>