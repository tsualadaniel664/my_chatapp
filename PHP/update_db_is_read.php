<?php
require 'connexion.php';

try {
    $bdd->exec("ALTER TABLE messages ADD COLUMN is_read BOOLEAN DEFAULT 0");
    echo "Colonne is_read ajoutée avec succès.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "La colonne is_read existe déjà.";
    } else {
        echo "Erreur : " . $e->getMessage();
    }
}
?>