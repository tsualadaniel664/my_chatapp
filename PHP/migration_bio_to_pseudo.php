<?php
require 'connexion.php';

try {
    // Check if column 'bio' exists
    $check = $bdd->query("SHOW COLUMNS FROM users LIKE 'bio'");
    if ($check->rowCount() > 0) {
        $bdd->exec("ALTER TABLE users CHANGE bio pseudo VARCHAR(255)");
        echo "Colonne 'bio' renommée en 'pseudo' avec succès.";
    } else {
        // Check if 'pseudo' already exists
        $checkPseudo = $bdd->query("SHOW COLUMNS FROM users LIKE 'pseudo'");
        if ($checkPseudo->rowCount() > 0) {
            echo "La colonne 'pseudo' existe déjà.";
        } else {
            echo "Colonne 'bio' introuvable.";
        }
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>