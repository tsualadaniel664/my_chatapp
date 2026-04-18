<?php
// chat.php
session_start();
require 'connexion.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$receiver_id = $_GET['receiver_id'] ?? null;
if (!$receiver_id) {
    header("Location: home.php");
    exit();
}

// Fetch receiver info
$stmt = $bdd->prepare("SELECT * FROM users WHERE id_users = ?");
$stmt->execute([$receiver_id]);
$receiver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$receiver) {
    header("Location: home.php");
    exit();
}

$receiver_photo = !empty($receiver['photo']) ? '../images/' . $receiver['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($receiver['prenom'] . ' ' . $receiver['nom']) . '&background=random&color=fff&size=200';

// Fetch receiver showcase
$stmtSc = $bdd->prepare("SELECT * FROM user_showcase WHERE user_id = ? ORDER BY created_at ASC");
$stmtSc->execute([$receiver_id]);
$showcaseItems = $stmtSc->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat avec <?= htmlspecialchars($receiver['prenom']) ?></title>
    <link rel="stylesheet" href="../CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/Chat.css">
</head>

<body>

    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <a href="home.php" class="back-btn"><i class="bi bi-chevron-left"></i></a>
            <div class="user-info">
                <img src="<?= htmlspecialchars($receiver_photo) ?>" alt="Avatar">
                <div>
                    <h3><?= htmlspecialchars($receiver['prenom'] . ' ' . $receiver['nom']) ?></h3>
                    <span class="text-muted small">@<?= htmlspecialchars($receiver['pseudo'] ?? 'utilisateur') ?></span>
                </div>
            </div>
            <button class="btn-nav-action ms-auto" data-bs-toggle="modal" data-bs-target="#profileModal"
                title="Voir le profil">
                <i class="bi bi-person-circle"></i>
            </button>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chat-messages">
            <div class="text-center text-white-50 mt-5">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Chargement des messages...
            </div>
        </div>

        <!-- Footer / Input -->
        <div class="chat-footer">
            <div class="chat-input-wrapper">
                <input type="text" id="message-input" placeholder="Écrivez votre message..." autocomplete="off">
            </div>
            <button id="send-btn"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>

    <!-- Profile Info Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 24px;">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pb-5 pt-0">
                    <img src="<?= htmlspecialchars($receiver_photo) ?>" class="rounded-4 mb-4 shadow"
                        style="width: 140px; height: 140px; object-fit: cover; border: 3px solid var(--primary-color);">
                    <h3 class="mb-0 fw-bold"><?= htmlspecialchars($receiver['prenom'] . ' ' . $receiver['nom']) ?></h3>
                    <p class="text-primary small mb-3">@<?= htmlspecialchars($receiver['pseudo'] ?? 'utilisateur') ?></p>
                    <p class="text-white-50 mb-4 small">
                        <i class="bi bi-calendar3 me-1"></i> Membre depuis le
                        <?= date('d/m/Y', strtotime($receiver['created_at'])) ?>
                    </p>

                    <?php if (!empty($showcaseItems)): ?>
                        <hr class="my-4 opacity-10">
                        <h5 class="text-start mb-3 fs-6 text-uppercase letter-spacing-1 opacity-75">À propos de moi</h5>
                        <div id="showcaseCarousel" class="carousel slide showcase-carousel" data-bs-ride="carousel">
                            <div class="carousel-inner rounded-4 overflow-hidden shadow-lg"
                                style="border: 1px solid var(--glass-border);">
                                <?php foreach ($showcaseItems as $index => $item): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <div class="position-relative" style="aspect-ratio: 4/3;">
                                            <img src="../images/showcase/<?= htmlspecialchars($item['image_path']) ?>"
                                                class="d-block w-100 h-100" style="object-fit: cover;">
                                            <?php if (!empty($item['description'])): ?>
                                                <div class="carousel-caption d-none d-md-block"
                                                    style="background: rgba(0,0,0,0.6); backdrop-filter: blur(10px); left: 0; right: 0; bottom: 0; padding: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                                                    <p class="mb-0 small"><?= htmlspecialchars($item['description']) ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($item['description'])): ?>
                                            <div class="p-3 d-md-none text-start"
                                                style="background: rgba(255,255,255,0.03); font-size: 0.85rem;">
                                                <?= htmlspecialchars($item['description']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($showcaseItems) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#showcaseCarousel"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Précédent</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#showcaseCarousel"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Suivant</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const receiverId = <?= json_encode($receiver_id) ?>;
        const receiverName = <?= json_encode($receiver['prenom'] . ' ' . $receiver['nom']) ?>;
        const chatMessages = document.getElementById('chat-messages');
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');

        let isScrolledToBottom = true;
        let lastMessageId = 0;

        const notificationSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

        chatMessages.addEventListener('scroll', () => {
            isScrolledToBottom = chatMessages.scrollTop + chatMessages.clientHeight >= chatMessages.scrollHeight - 50;
        });

        function loadMessages() {
            fetch('get_messages.php?receiver_id=' + receiverId)
                .then(r => r.json())
                .then(response => {
                    if (chatMessages.innerHTML !== response.html) {
                        chatMessages.innerHTML = response.html;

                        if (lastMessageId !== 0 && response.lastId > lastMessageId) {
                            notificationSound.play().catch(() => { });
                            if (document.hidden && Notification.permission === "granted") {
                                new Notification("Nouveau message de " + receiverName);
                            }
                        }
                        lastMessageId = response.lastId;
                        if (isScrolledToBottom) {
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                    } else {
                        lastMessageId = response.lastId;
                    }
                }).catch(e => console.error("Error loading messages:", e));
        }

        function sendMessage() {
            const message = messageInput.value.trim();
            if (message === '') return;

            const formData = new FormData();
            formData.append('receiver_id', receiverId);
            formData.append('message', message);

            messageInput.value = ''; // Instant clear for UX

            fetch('send_message.php', { method: 'POST', body: formData })
                .then(r => r.text())
                .then(t => {
                    if (t === 'Success') {
                        loadMessages();
                        setTimeout(() => {
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                            isScrolledToBottom = true;
                        }, 100);
                    }
                });
        }

        sendBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', e => { if (e.key === 'Enter') sendMessage(); });

        loadMessages();
        setInterval(loadMessages, 3000);

        if ("Notification" in window) Notification.requestPermission();
    </script>
</body>

</html>