<?php
// setup_db.php

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server without selecting a DB
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server.\n";

    // Read the SQL file
    $sqlFile = '../database/chat.sql';
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);

    // Execute the SQL commands
    // PDO::exec can run multiple statements if the driver supports it, 
    // but sometimes it's better to split them. 
    // However, XAMPP/MySQL usually handles multi-statements in PDO if configured or by default for basic scripts.
    // Let's try executing it directly.

    $pdo->exec($sql);

    echo "Database 'chat' created and tables initialized successfully.\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}
?>