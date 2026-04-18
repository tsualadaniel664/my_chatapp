<?php
// admin_actions.php
session_start();
require 'connexion.php';

// Security check: only admins allowed
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

$action = $_POST['action'] ?? null;
$userId = $_POST['user_id'] ?? null;

// Validate parameters based on action
if (!$action) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Action manquante.']);
    exit();
}

// user_id is only required for user-related actions
if (in_array($action, ['block', 'unblock', 'delete']) && !$userId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant.']);
    exit();
}

try {
    switch ($action) {
        case 'block':
            $stmt = $bdd->prepare("UPDATE users SET status = 'blocked' WHERE id_users = ? AND role != 'admin'");
            $stmt->execute([$userId]);
            $msg = "Utilisateur bloqué.";
            break;

        case 'unblock':
            $stmt = $bdd->prepare("UPDATE users SET status = 'active' WHERE id_users = ?");
            $stmt->execute([$userId]);
            $msg = "Utilisateur débloqué.";
            break;

        case 'delete':
            // Delete messages first (handled by FK cascade ON DELETE CASCADE in SQL, but good to be aware)
            $stmt = $bdd->prepare("DELETE FROM users WHERE id_users = ? AND role != 'admin'");
            $stmt->execute([$userId]);
            $msg = "Utilisateur supprimé.";
            break;

        case 'post_announcement':
            $content = $_POST['content'] ?? null;
            if (!$content)
                throw new Exception("Contenu vide.");
            $stmt = $bdd->prepare("INSERT INTO announcements (content) VALUES (?)");
            $stmt->execute([$content]);
            $msg = "Annonce publiée avec succès.";
            break;

        case 'update_announcement':
            $id = $_POST['announce_id'] ?? null;
            $content = $_POST['content'] ?? null;
            if (!$id || !$content)
                throw new Exception("Paramètres manquants.");
            $stmt = $bdd->prepare("UPDATE announcements SET content = ? WHERE id_announcement = ?");
            $stmt->execute([$content, $id]);
            $msg = "Annonce mise à jour.";
            break;

        case 'delete_announcement':
            $id = $_POST['announce_id'] ?? null;
            if (!$id)
                throw new Exception("ID manquant.");
            $stmt = $bdd->prepare("DELETE FROM announcements WHERE id_announcement = ?");
            $stmt->execute([$id]);
            $msg = "Annonce supprimée.";
            break;

        default:
            throw new Exception("Action non reconnue.");
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => $msg]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>