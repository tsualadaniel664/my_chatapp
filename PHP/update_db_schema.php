<?php
require 'connexion.php';

try {
    $bdd->exec("ALTER TABLE users ADD COLUMN last_activity DATETIME DEFAULT NULL");
    echo "Colonne last_activity ajoutée avec succès.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "La colonne last_activity existe déjà.";
    } else {
        echo "Erreur : " . $e->getMessage();
    }
}
?>