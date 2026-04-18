<?php
// admin_dashboard.php
session_start();
require 'connexion.php';

// Security check: only admins allowed
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Pagination settings
$usersPerPage = 10;
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($currentPage < 1)
    $currentPage = 1;
$offset = ($currentPage - 1) * $usersPerPage;

// Get total users for pagination UI
$countStmt = $bdd->query("SELECT COUNT(*) FROM users");
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $usersPerPage);

// Fetch users for the current page
$stmt = $bdd->prepare("SELECT * FROM users ORDER BY role ASC, created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $usersPerPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all announcements (usually not that many, so no pagination needed for now)
$stmtAnnounce = $bdd->query("SELECT * FROM announcements ORDER BY created_at DESC");
$allAnnouncements = $stmtAnnounce->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Chat App</title>
    <link rel="stylesheet" href="../CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #7b2ff7;
            --secondary-color: #f107a3;
            --bg-gradient: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.75);
        }

        body {
            background: var(--bg-gradient);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Outfit', sans-serif;
            color: var(--text-main) !important;
            padding: 2rem 0;
        }

        .admin-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 2.5rem;
            box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.5);
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: var(--primary-color);
            transform: translateX(-5px);
            color: white;
        }

        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .user-table th {
            padding: 1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
            border: none;
        }

        .user-row {
            background: rgba(255, 255, 255, 0.02);
            transition: transform 0.3s, background 0.3s;
        }

        .user-row:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: scale(1.01);
        }

        .user-row td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid var(--glass-border);
            border-bottom: 1px solid var(--glass-border);
        }

        .user-row td:first-child {
            border-left: 1px solid var(--glass-border);
            border-top-left-radius: 16px;
            border-bottom-left-radius: 16px;
        }

        .user-row td:last-child {
            border-right: 1px solid var(--glass-border);
            border-top-right-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid var(--glass-border);
        }

        .badge-role {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-admin {
            background: rgba(123, 47, 247, 0.2);
            color: #b085ff;
        }

        .badge-user {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
        }

        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-active {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
        }

        .status-blocked {
            background: rgba(255, 68, 68, 0.1);
            color: #ff4444;
        }

        .btn-action {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--glass-border);
            background: var(--glass-bg);
            color: white;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-action:hover {
            transform: translateY(-3px);
        }

        .btn-block:hover {
            background: #ff9800;
            border-color: #ff9800;
        }

        .btn-unblock:hover {
            background: #00ff88;
            border-color: #00ff88;
        }

        .btn-delete:hover {
            background: #ff4444;
            border-color: #ff4444;
        }

        .swal2-popup {
            background: rgba(30, 27, 58, 0.95) !important;
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border) !important;
            color: white !important;
            border-radius: 24px !important;
        }

        /* Visibility Fixes */
        .form-control {
            color: white !important;
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid var(--glass-border) !important;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            box-shadow: 0 0 0 0.25rem rgba(123, 47, 247, 0.25) !important;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        .swal2-textarea,
        .swal2-input {
            color: white !important;
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid var(--glass-border) !important;
        }

        /* Global Text Brightness Overrides */
        .text-muted {
            color: rgba(255, 255, 255, 0.75) !important;
        }

        /* User Counter Badge */
        .user-counter-badge {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 10px 20px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 20px -5px rgba(123, 47, 247, 0.4);
            transition: all 0.3s ease;
        }

        .user-counter-badge:hover {
            transform: translateY(-3px) scale(1.05);
        }

        .user-counter-badge i {
            font-size: 1.4rem;
        }

        .user-counter-badge .count-num {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .user-counter-badge .count-label {
            font-size: 0.8rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Announcement Specific Styles */
        .announce-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .announce-row {
            background: rgba(255, 255, 255, 0.015);
            transition: all 0.3s;
        }

        .announce-row:hover {
            background: rgba(255, 255, 255, 0.04);
            transform: translateY(-2px);
        }

        .announce-row td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
            border-top: 1px solid var(--glass-border);
            border-bottom: 1px solid var(--glass-border);
        }

        .announce-row td:first-child {
            border-left: 1px solid var(--glass-border);
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .announce-row td:last-child {
            border-right: 1px solid var(--glass-border);
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .announce-content-text {
            color: #00ff88;
            font-weight: 500;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5;
        }

        .active-green-text {
            color: #00ff88 !important;
        }

        .announce-date {
            color: #00ff88 !important;
            /* Green like user asked */
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 2rem;
        }

        .page-link-custom {
            padding: 10px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .page-link-custom:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .page-link-custom.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .page-link-custom.disabled {
            opacity: 0.4;
            pointer-events: none;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .admin-container {
                padding: 0 1rem;
            }

            .glass-panel {
                padding: 1.5rem;
            }

            .header-section {
                flex-direction: column;
                gap: 1.5rem;
                align-items: flex-start;
                margin-bottom: 2rem;
            }

            .header-section h1 {
                order: 2;
                font-size: 1.8rem;
            }

            .user-counter-badge {
                order: 1;
            }

            .back-btn {
                order: 3;
            }
        }

        @media (max-width: 768px) {

            .user-table thead,
            .announce-table thead {
                display: none;
            }

            .user-row,
            .announce-row {
                display: block;
                margin-bottom: 1.5rem;
                padding: 1rem;
                border-radius: 20px;
                border: 1px solid var(--glass-border);
                background: rgba(255, 255, 255, 0.04);
            }

            .user-row td,
            .announce-row td {
                display: block;
                padding: 0.5rem 0;
                border: none !important;
                text-align: left !important;
            }

            .user-row td:last-child,
            .announce-row td:last-child {
                border-top: 1px solid var(--glass-border) !important;
                margin-top: 0.5rem;
                padding-top: 1rem;
            }

            .user-row td::before {
                content: attr(data-label);
                font-weight: 700;
                display: block;
                font-size: 0.75rem;
                text-transform: uppercase;
                color: var(--primary-color);
                margin-bottom: 4px;
            }

            .user-avatar {
                width: 50px;
                height: 50px;
            }

            .announce-content-text {
                -webkit-line-clamp: none;
            }
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: rgba(123, 47, 247, 0.2);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            background: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(123, 47, 247, 0.4);
        }
    </style>
</head>

<body>

    <div class="admin-container">
        <div class="header-section">
            <a href="home.php" class="back-btn">
                <i class="bi bi-arrow-left"></i> Retour
            </a>

            <div class="user-counter-badge" id="user-counter">
                <i class="bi bi-people-fill"></i>
                <div class="d-flex flex-column">
                    <span class="count-num"><?= $totalUsers ?></span>
                    <span class="count-label">Utilisateurs</span>
                </div>
            </div>

            <h1 class="m-0 fw-bold">Dashboard Admin</h1>
        </div>

        <!-- Announcement Section -->
        <div class="glass-panel mb-4">
            <h4 class="mb-3 fw-bold"><i class="bi bi-megaphone me-2"></i>Diffuser une annonce</h4>
            <div class="row g-3">
                <div class="col-md-9">
                    <textarea id="announcementContent" class="form-control" autocomplete="off"
                        placeholder="Entrez le texte de l'annonce ici..." rows="2"
                        style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); color: white; border-radius: 12px;"></textarea>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100 h-100 rounded-3" onclick="postAnnouncement()">
                        <i class="bi bi-send-fill me-2"></i>Publier
                    </button>
                </div>
            </div>

            <!-- Existing Announcements List -->
            <hr style="border-color: var(--glass-border); margin: 2rem 0;">
            <h5 class="mb-3 fw-bold small text-uppercase active-green-text">Gérer les annonces</h5>
            <?php if (empty($allAnnouncements)): ?>
                <p class="text-muted small">Aucune annonce publiée.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="announce-table">
                        <thead>
                            <tr class="small text-uppercase active-green-text" style="letter-spacing: 1px; opacity: 0.8;">
                                <th class="ps-3 pb-2">Contenu de l'annonce</th>
                                <th class="pb-2">Date de diffusion</th>
                                <th class="text-end pe-3 pb-2">Gestion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allAnnouncements as $a): ?>
                                <tr class="announce-row">
                                    <td class="ps-3" data-label="Annonce">
                                        <div class="announce-content-text" title="<?= htmlspecialchars($a['content']) ?>">
                                            <i class="bi bi-chat-quote me-2 active-green-text opacity-75"></i>
                                            <?= htmlspecialchars($a['content']) ?>
                                        </div>
                                    </td>
                                    <td class="small announce-date" data-label="Date">
                                        <i class="bi bi-calendar3 me-2 active-green-text"></i>
                                        <?= date('d/m/Y H:i', strtotime($a['created_at'])) ?>
                                    </td>
                                    <td class="text-end pe-3" data-label="Actions">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn-action btn-unblock"
                                                onclick="editAnnouncement(<?= $a['id_announcement'] ?>, '<?= htmlspecialchars(addslashes($a['content'])) ?>')"
                                                title="Modifier">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn-action btn-delete"
                                                onclick="deleteAnnouncement(<?= $a['id_announcement'] ?>)" title="Supprimer">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="glass-panel">
            <div class="table-responsive">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUsers as $u):
                            $avatar = !empty($u['photo']) ? '../images/' . $u['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($u['prenom']) . '&background=random&color=fff';
                            ?>
                            <tr class="user-row" id="user-row-<?= $u['id_users'] ?>">
                                <td data-label="Utilisateur">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= $avatar ?>" class="user-avatar" alt="Avatar">
                                        <div>
                                            <div class="fw-bold">
                                                <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                                            </div>
                                            <div class="small text-muted">@
                                                <?= htmlspecialchars($u['pseudo'] ?? 'user') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Email">
                                    <?= htmlspecialchars($u['email']) ?>
                                </td>
                                <td data-label="Rôle">
                                    <span class="badge-role badge-<?= $u['role'] ?>">
                                        <?= strtoupper($u['role']) ?>
                                    </span>
                                </td>
                                <td data-label="Statut">
                                    <span class="badge-status status-<?= $u['status'] ?>"
                                        id="status-badge-<?= $u['id_users'] ?>">
                                        <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                                        <?= ucfirst($u['status']) ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <div class="d-flex gap-2">
                                        <?php if ($u['role'] !== 'admin'): ?>
                                            <?php if ($u['status'] === 'active'): ?>
                                                <button class="btn-action btn-block"
                                                    onclick="manageUser('<?= $u['id_users'] ?>', 'block')" title="Bloquer">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-action btn-unblock"
                                                    onclick="manageUser('<?= $u['id_users'] ?>', 'unblock')" title="Débloquer">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            <?php endif; ?>

                                            <button class="btn-action btn-delete"
                                                onclick="confirmDelete('<?= $u['id_users'] ?>')" title="Supprimer">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small italic">Admin protégé</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination UI -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <a href="?page=<?= $currentPage - 1 ?>"
                        class="page-link-custom <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="page-link-custom <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <a href="?page=<?= $currentPage + 1 ?>"
                        class="page-link-custom <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SweetAlert2 for nice alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function manageUser(userId, action) {
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('action', action);

            fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            background: 'rgba(30, 27, 58, 0.95)',
                            color: '#fff',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Erreur', text: data.message });
                    }
                });
        }

        function postAnnouncement() {
            const content = document.getElementById('announcementContent').value.trim();
            if (!content) {
                Swal.fire({ icon: 'error', title: 'Oups', text: "L'annonce ne peut pas être vide." });
                return;
            }

            const formData = new FormData();
            formData.append('action', 'post_announcement');
            formData.append('content', content);

            fetch('admin_actions.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: "Annonce publiée !",
                            background: 'rgba(30, 27, 58, 0.95)',
                            color: '#fff',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        document.getElementById('announcementContent').value = '';
                    } else {
                        Swal.fire({ icon: 'error', title: 'Erreur', text: data.message });
                    }
                });
        }

        function editAnnouncement(id, oldContent) {
            Swal.fire({
                title: 'Modifier l\'annonce',
                input: 'textarea',
                inputValue: oldContent,
                inputAttributes: {
                    autocapitalize: 'off',
                    style: 'color: white; border-radius: 12px;'
                },
                background: 'rgba(30, 27, 58, 0.95)',
                color: '#fff',
                showCancelButton: true,
                confirmButtonText: 'Enregistrer',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: (newContent) => {
                    if (!newContent.trim()) {
                        Swal.showValidationMessage("L'annonce ne peut pas être vide.");
                        return false;
                    }
                    const formData = new FormData();
                    formData.append('action', 'update_announcement');
                    formData.append('announce_id', id);
                    formData.append('content', newContent.trim());

                    return fetch('admin_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) throw new Error(data.message);
                            return data;
                        })
                        .catch(error => {
                            Swal.showValidationMessage(`Erreur: ${error}`);
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Modifié !',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            });
        }

        function deleteAnnouncement(id) {
            Swal.fire({
                title: 'Supprimer cette annonce ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4444',
                cancelButtonColor: 'rgba(255,255,255,0.1)',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                background: 'rgba(30, 27, 58, 0.95)',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'delete_announcement');
                    formData.append('announce_id', id);

                    fetch('admin_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                Swal.fire({ icon: 'error', title: 'Erreur', text: data.message });
                            }
                        });
                }
            });
        }

        function confirmDelete(userId) {
            Swal.fire({
                title: 'Supprimer cet utilisateur ?',
                text: "Cette action est irréversible et supprimera tous ses messages.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4444',
                cancelButtonColor: 'rgba(255,255,255,0.1)',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    manageUser(userId, 'delete');
                }
            });
        }

        // Admin Real-Time Updates
        let previousTotalUsers = <?= $totalUsers ?>;

        function checkAdminUpdates() {
            fetch('admin_fetch_updates.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) return;

                    // Update counter UI
                    const counterNum = document.querySelector('#user-counter .count-num');
                    if (counterNum) {
                        counterNum.textContent = data.totalUsers;
                    }

                    // Check for new users
                    if (data.totalUsers > previousTotalUsers) {
                        const newUser = data.latestUser;
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'Nouveau membre !',
                            text: `${newUser.prenom} ${newUser.nom} vient de rejoindre l'application.`,
                            showConfirmButton: false,
                            showCloseButton: true,
                            background: 'rgba(30, 27, 58, 0.95)',
                            color: '#ffffff'
                        });

                        // Play a subtle sound or trigger a refresh after notification if needed
                        // For now, we update the local stored count
                        previousTotalUsers = data.totalUsers;
                    }
                })
                .catch(err => console.error('Error fetching admin updates:', err));
        }

        // Start polling for admin updates
        setInterval(checkAdminUpdates, 10000); // Check every 10 seconds

        // Back to Top Logic
        const backToTopBtn = document.createElement('div');
        backToTopBtn.className = 'back-to-top';
        backToTopBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
        document.body.appendChild(backToTopBtn);

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>

</html>