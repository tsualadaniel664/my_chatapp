<?php
// home.php
session_start();
require 'connexion.php';

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Fetch current user info for navbar
$my_id = $_SESSION['id'];
$stmtMe = $bdd->prepare("SELECT prenom, nom, photo FROM users WHERE id_users = ?");
$stmtMe->execute([$my_id]);
$me = $stmtMe->fetch(PDO::FETCH_ASSOC);

$my_nom = $me['nom'];
$my_prenom = $me['prenom'];
$my_photo = !empty($me['photo']) ? '../images/' . $me['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($my_prenom . ' ' . $my_nom) . '&background=random&color=fff&size=100';

// Pagination settings
$usersPerPage = 10;
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($currentPage < 1)
    $currentPage = 1;
$offset = ($currentPage - 1) * $usersPerPage;

// Get total users for pagination UI
$countStmt = $bdd->prepare("SELECT COUNT(*) FROM users WHERE id_users != ?");
$countStmt->execute([$my_id]);
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $usersPerPage);

// Fetch users for the current page with refined sorting
// 1. Users with unread messages first
// 2. Among those, latest unread message first
// 3. Then latest overall activity
$sql = "SELECT u.*, 
       (SELECT COUNT(*) FROM messages m WHERE m.sender_id = u.id_users AND m.receiver_id = ? AND m.is_read = 0) as unread_count,
       (SELECT MAX(created_at) FROM messages m WHERE (m.sender_id = u.id_users AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = u.id_users)) as last_message_time
       FROM users u 
       WHERE u.id_users != ? 
       ORDER BY 
         (unread_count > 0) DESC, 
         last_message_time DESC, 
         u.last_activity DESC
       LIMIT ? OFFSET ?";

$stmt = $bdd->prepare($sql);
$stmt->bindValue(1, $my_id, PDO::PARAM_INT);
$stmt->bindValue(2, $my_id, PDO::PARAM_INT);
$stmt->bindValue(3, $my_id, PDO::PARAM_INT);
$stmt->bindValue(4, $my_id, PDO::PARAM_INT);
$stmt->bindValue(5, $usersPerPage, PDO::PARAM_INT);
$stmt->bindValue(6, $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest announcement
$latestAnnouncement = null;
try {
    $announcementStmt = $bdd->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 1");
    $latestAnnouncement = $announcementStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table might not exist yet, ignore error silently for UX
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Chat App</title>
    <link rel="stylesheet" href="../CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/home.css">
    <style>
        /* Pagination Styles - Reuse from Admin for Consistency */
        .pagination-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 3rem;
            margin-bottom: 2rem;
        }

        .page-link-custom {
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .page-link-custom:hover {
            background: var(--primary-color, #7b2ff7);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(123, 47, 247, 0.4);
        }

        .page-link-custom.active {
            background: var(--primary-color, #7b2ff7);
            border-color: var(--primary-color, #7b2ff7);
            box-shadow: 0 10px 20px -5px rgba(123, 47, 247, 0.4);
        }

        .page-link-custom.disabled {
            opacity: 0.3;
            pointer-events: none;
        }

        /* Dynamic Animations */
        @keyframes pulse-green {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(0, 255, 136, 0.4);
            }

            70% {
                transform: scale(1.02);
                box-shadow: 0 0 20px 10px rgba(0, 255, 136, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(0, 255, 136, 0);
            }
        }

        .pulse-unread {
            animation: pulse-green 2s infinite;
            border-color: #00ff88 !important;
        }

        .user-card {
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
        }
    </style>
</head>

<body>

    <nav class="navbar-custom">
        <div class="logo">
            <h4><i class="bi bi-chat-heart-fill"></i> ChatApp</h4>
        </div>
        <div class="user-profile">
            <span class="d-none d-md-inline">Bonjour, <strong><?= htmlspecialchars($my_prenom) ?></strong></span>
            <a href="settings.php" class="btn-nav-action p-0 overflow-hidden" title="Mon Profil"
                style="border: 2px solid var(--glass-border);">
                <img src="<?= htmlspecialchars($my_photo) ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" class="btn-nav-action" title="Admin Dashboard">
                    <i class="bi bi-shield-lock"></i>
                </a>
            <?php endif; ?>
            <button type="button" class="btn-logout" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Déconnexion</span>
            </button>
        </div>
    </nav>

    <?php if ($latestAnnouncement): ?>
        <div class="announcement-bar-container">
            <div class="announcement-bar">
                <div class="announcement-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
                <div class="announcement-text">
                    <strong>Annonce :</strong> <?= htmlspecialchars($latestAnnouncement['content']) ?>
                </div>
                <button class="announcement-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="home-container">
        <div class="text-center mb-5">
            <h2 class="status-title">Messages</h2>
            <div class="search-container">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="userSearch" class="search-input" placeholder="Rechercher un contact..."
                    autocomplete="off">
            </div>
        </div>

        <div class="users-grid">
            <?php foreach ($users as $user):
                $photo = !empty($user['photo']) ? '../images/' . $user['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['prenom'] . ' ' . $user['nom']) . '&background=random&color=fff&size=128';

                // Online check
                $is_online = false;
                if (!empty($user['last_activity'])) {
                    $last_activity = new DateTime($user['last_activity']);
                    $current_time = new DateTime();
                    $interval = $current_time->diff($last_activity);
                    if ($interval->y == 0 && $interval->m == 0 && $interval->d == 0 && $interval->h == 0 && $interval->i < 5) {
                        $is_online = true;
                    }
                }
                ?>
                <a href="chat.php?receiver_id=<?= $user['id_users'] ?>" class="user-card"
                    data-id="<?= $user['id_users'] ?>">
                    <?php if ($user['unread_count'] > 0): ?>
                        <div class="badge-unread"><?= $user['unread_count'] ?></div>
                    <?php endif; ?>

                    <div style="position: relative;">
                        <img src="<?= htmlspecialchars($photo) ?>" alt="Photo de profil">
                        <?php if ($is_online): ?>
                            <span class="online-dot"></span>
                        <?php endif; ?>
                    </div>

                    <h3><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h3>
                    <p>@<?= htmlspecialchars($user['pseudo'] ?? 'utilisateur') ?></p>
                    <div class="action-btn">Lancer la discussion</div>
                </a>
            <?php endforeach; ?>

            <?php if (count($users) === 0 && (!isset($searchTerm) || empty($searchTerm))): ?>
                <div class="col-12 text-center" id="no-users-msg">
                    <div class="p-5 bg-white bg-opacity-10 rounded-4">
                        <i class="bi bi-people-fill display-4 text-white-50 mb-3"></i>
                        <p class="text-white-50">Aucun autre utilisateur inscrit pour le moment.</p>
                    </div>
                </div>
            <?php endif; ?>
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

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content logout-modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-0 pb-4">
                    <div class="logout-icon-container mb-4">
                        <i class="bi bi-door-open"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Déconnexion ?</h3>
                    <p class="text-white-50 mb-4 px-4">Êtes-vous sûr de vouloir quitter votre session ? Vous devrez vous
                        reconnecter pour accéder à vos messages.</p>

                    <div class="d-flex gap-3 justify-content-center px-4">
                        <button type="button" class="btn btn-secondary flex-grow-1 py-3 rounded-4 fw-bold"
                            data-bs-dismiss="modal"
                            style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border);">
                            Annuler
                        </button>
                        <a href="logout.php" class="btn btn-danger flex-grow-1 py-3 rounded-4 fw-bold shadow-danger">
                            Me déconnecter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Required for Modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../JS/auth.js"></script>
    <script>
        // Specific search handling for the new structure if needed, 
        // but auth.js will handle the core. Overriding here for the specific grid.
        document.getElementById('userSearch').addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.user-card');
            let hasVisible = false;

            cards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const pseudo = card.querySelector('p').textContent.toLowerCase();

                if (name.includes(searchTerm) || pseudo.includes(searchTerm)) {
                    card.style.display = 'flex';
                    card.style.animation = 'fadeIn 0.3s ease forwards';
                    hasVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });

            let noResults = document.getElementById('no-results-msg');
            if (!hasVisible && searchTerm !== '') {
                if (!noResults) {
                    noResults = document.createElement('div');
                    noResults.id = 'no-results-msg';
                    noResults.className = 'col-12 text-center mt-5';
                    noResults.style.gridColumn = '1 / -1';
                    noResults.innerHTML = '<i class="bi bi-search display-4 mb-3 d-block opacity-25"></i><p class="text-white-50">Aucun résultat pour "' + searchTerm + '"</p>';
                    document.querySelector('.users-grid').appendChild(noResults);
                } else {
                    noResults.style.display = 'block';
                    noResults.querySelector('p').textContent = 'Aucun résultat pour "' + searchTerm + '"';
                }
            } else if (noResults) {
                noResults.style.display = 'none';
            }
        });

        // Dashboard updates
        const notificationSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        let previousTotalUnread = 0;

        function updateDashboard() {
            fetch('fetch_updates.php')
                .then(response => response.json())
                .then(data => {
                    let currentTotalUnread = 0;
                    const grid = document.querySelector('.users-grid');
                    const cards = Array.from(grid.querySelectorAll('.user-card'));
                    const dataMap = new Map(data.map(u => [u.id.toString(), u]));

                    data.forEach(userData => {
                        const card = document.querySelector(`.user-card[data-id="${userData.id}"]`);
                        if (!card) return;

                        let badge = card.querySelector('.badge-unread');
                        if (userData.unread > 0) {
                            if (!badge) {
                                badge = document.createElement('div');
                                badge.className = 'badge-unread';
                                card.insertBefore(badge, card.firstChild);
                                // Pulse effect for new unread
                                card.classList.add('pulse-unread');
                                setTimeout(() => card.classList.remove('pulse-unread'), 2000);
                            }
                            badge.textContent = userData.unread;
                        } else if (badge) {
                            badge.remove();
                        }
                        currentTotalUnread += userData.unread;

                        let dot = card.querySelector('.online-dot');
                        let imgBox = card.querySelector('div[style*="position: relative"]');
                        if (userData.online) {
                            if (!dot && imgBox) {
                                dot = document.createElement('span');
                                dot.className = 'online-dot';
                                imgBox.appendChild(dot);
                            }
                        } else if (dot) {
                            dot.remove();
                        }
                    });

                    // Automatic Sorting
                    // We sort the cards currently in the DOM based on the fresh data
                    const sortedCards = cards.sort((a, b) => {
                        const idA = a.getAttribute('data-id');
                        const idB = b.getAttribute('data-id');
                        const uA = dataMap.get(idA);
                        const uB = dataMap.get(idB);

                        if (!uA || !uB) return 0;

                        // 1. Unread status first
                        const hasUnreadA = uA.unread > 0;
                        const hasUnreadB = uB.unread > 0;
                        if (hasUnreadA !== hasUnreadB) return hasUnreadB - hasUnreadA;

                        // 2. Latest message time
                        const timeA = new Date(uA.last_message_time).getTime();
                        const timeB = new Date(uB.last_message_time).getTime();
                        if (timeA !== timeB) return timeB - timeA;

                        // 3. Latest activity
                        const actA = new Date(uA.last_activity).getTime();
                        const actB = new Date(uB.last_activity).getTime();
                        return actB - actA;
                    });

                    // Append in new order (DOM manipulation is efficient enough for small sets)
                    sortedCards.forEach(card => grid.appendChild(card));

                    if (currentTotalUnread > previousTotalUnread) {
                        notificationSound.play().catch(() => { });
                    }
                    previousTotalUnread = currentTotalUnread;
                });
        }
        setInterval(updateDashboard, 5000);

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