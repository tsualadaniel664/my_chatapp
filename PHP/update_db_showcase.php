<?php
// update_db_showcase.php
require 'connexion.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS user_showcase (
        id_showcase INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
        user_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id_users) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $bdd->exec($sql);
    echo "Table 'user_showcase' créée avec succès.<br>";

    // Create directory for showcase images
    $dir = '../images/showcase/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "Répertoire '../images/showcase/' créé.<br>";
    }

} catch (PDOException $e) {
    echo "Erreur lors de la création de la table : " . $e->getMessage();
}
?>