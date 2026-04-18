<?php
// test_db.php
require 'connexion.php';

try {
    // 1. Insert a test user
    $stmt = $bdd->prepare("INSERT INTO users (password, email, nom, prenom, pseudo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['hashed_password', 'test@example.com', 'Test', 'User', 'Hello World']);
    $userId = $bdd->lastInsertId();
    echo "User created with ID: $userId\n";

    // 2. Insert another user
    $stmt->execute(['hashed_password_2', 'test2@example.com', 'Test2', 'User2', 'Hello World 2']);
    $userId2 = $bdd->lastInsertId();
    echo "User 2 created with ID: $userId2\n";

    // 3. Insert a message
    $stmt = $bdd->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $userId2, 'Hello from user 1']);
    echo "Message sent.\n";

    // 4. Verify data
    $stmt = $bdd->query("SELECT * FROM messages WHERE sender_id = $userId");
    $msg = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($msg && $msg['message'] === 'Hello from user 1') {
        echo "SUCCESS: Message verified in DB.\n";
    } else {
        echo "FAILURE: Message not found or incorrect.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>