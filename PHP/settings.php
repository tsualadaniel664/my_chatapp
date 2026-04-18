<?php
session_start();
require 'connexion.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id'];
$message = '';
$messageType = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle basic info
    if (isset($_POST['nom'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $pseudo = htmlspecialchars($_POST['pseudo']);
        $email = htmlspecialchars($_POST['email']);

        // Update basic info
        $sql = "UPDATE users SET nom = ?, prenom = ?, pseudo = ?, email = ? WHERE id_users = ?";
        $params = [$nom, $prenom, $pseudo, $email, $id];

        // Handle Password Update
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET nom = ?, prenom = ?, pseudo = ?, email = ?, password = ? WHERE id_users = ?";
            $params = [$nom, $prenom, $pseudo, $email, $password, $id];
        }

        // Handle Photo Upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['photo']['name'];
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($extension, $allowed)) {
                $newFilename = uniqid() . '.' . $extension;
                $uploadDir = '../images/';

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newFilename)) {
                    // Cleanup old profile photo
                    $stmt = $bdd->prepare("SELECT photo FROM users WHERE id_users = ?");
                    $stmt->execute([$id]);
                    $old = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($old && !empty($old['photo'])) {
                        $oldPath = '../images/' . $old['photo'];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    if (!empty($_POST['password'])) {
                        $sql = "UPDATE users SET nom = ?, prenom = ?, pseudo = ?, email = ?, password = ?, photo = ? WHERE id_users = ?";
                        $params = [$nom, $prenom, $pseudo, $email, $password, $newFilename, $id];
                    } else {
                        $sql = "UPDATE users SET nom = ?, prenom = ?, pseudo = ?, email = ?, photo = ? WHERE id_users = ?";
                        $params = [$nom, $prenom, $pseudo, $email, $newFilename, $id];
                    }
                } else {
                    $message = "Erreur lors du téléchargement de l'image.";
                    $messageType = "danger";
                }
            } else {
                $message = "Format d'image non valide (jpg, jpeg, png, gif).";
                $messageType = "danger";
            }
        }

        try {
            $stmt = $bdd->prepare($sql);
            $stmt->execute($params);

            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;

            $message = "Profil mis à jour avec succès !";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Erreur : " . $e->getMessage();
            $messageType = "danger";
        }
    }

    // Handle Showcase Images
    if (isset($_POST['showcase_action'])) {
        for ($i = 0; $i < 6; $i++) {
            $desc = htmlspecialchars($_POST['showcase_desc'][$i] ?? '');
            $showcaseId = $_POST['showcase_id'][$i] ?? null;

            // Update description if it exists
            if ($showcaseId) {
                $stmt = $bdd->prepare("UPDATE user_showcase SET description = ? WHERE id_showcase = ? AND user_id = ?");
                $stmt->execute([$desc, $showcaseId, $id]);
            }

            // Handle New Upload
            if (isset($_FILES['showcase_img']['name'][$i]) && $_FILES['showcase_img']['error'][$i] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['showcase_img']['name'][$i];
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($extension, $allowed)) {
                    $newFile = 'sc_' . uniqid() . '.' . $extension;
                    $target = '../images/showcase/' . $newFile;

                    if (move_uploaded_file($_FILES['showcase_img']['tmp_name'][$i], $target)) {
                        if ($showcaseId) {
                            // Cleanup old showcase photo
                            $stmt = $bdd->prepare("SELECT image_path FROM user_showcase WHERE id_showcase = ? AND user_id = ?");
                            $stmt->execute([$showcaseId, $id]);
                            $oldSc = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($oldSc && !empty($oldSc['image_path'])) {
                                $oldScPath = '../images/showcase/' . $oldSc['image_path'];
                                if (file_exists($oldScPath)) {
                                    unlink($oldScPath);
                                }
                            }

                            // Update existing record
                            $stmt = $bdd->prepare("UPDATE user_showcase SET image_path = ? WHERE id_showcase = ? AND user_id = ?");
                            $stmt->execute([$newFile, $showcaseId, $id]);
                        } else {
                            // Insert new record
                            $stmt = $bdd->prepare("INSERT INTO user_showcase (user_id, image_path, description) VALUES (?, ?, ?)");
                            $stmt->execute([$id, $newFile, $desc]);
                        }
                    }
                }
            }
        }
        $message = "Galerie mise à jour !";
        $messageType = "success";
    }

    // Handle Deletion
    if (isset($_POST['delete_showcase_id'])) {
        $delId = (int)$_POST['delete_showcase_id'];
        
        // Fetch image path to delete file
        $stmt = $bdd->prepare("SELECT image_path FROM user_showcase WHERE id_showcase = ? AND user_id = ?");
        $stmt->execute([$delId, $id]);
        $toDelete = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($toDelete) {
            $filePath = '../images/showcase/' . $toDelete['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $stmt = $bdd->prepare("DELETE FROM user_showcase WHERE id_showcase = ? AND user_id = ?");
            $stmt->execute([$delId, $id]);
            
            $message = "Photo supprimée de la galerie.";
            $messageType = "success";
        }
    }

    // Handle Profile Photo Deletion
    if (isset($_POST['delete_profile_photo'])) {
        $stmt = $bdd->prepare("SELECT photo FROM users WHERE id_users = ?");
        $stmt->execute([$id]);
        $curr = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($curr && !empty($curr['photo'])) {
            $filePath = '../images/' . $curr['photo'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $stmt = $bdd->prepare("UPDATE users SET photo = NULL WHERE id_users = ?");
            $stmt->execute([$id]);
            $message = "Photo de profil supprimée.";
            $messageType = "success";
        }
    }
}

// Fetch current user data
$stmt = $bdd->prepare("SELECT * FROM users WHERE id_users = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch showcase data
$stmtSc = $bdd->prepare("SELECT * FROM user_showcase WHERE user_id = ? ORDER BY created_at ASC LIMIT 6");
$stmtSc->execute([$id]);
$showcaseItems = $stmtSc->fetchAll(PDO::FETCH_ASSOC);
$totalShowcase = count($showcaseItems);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Chat App</title>
    <link rel="stylesheet" href="../CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/settings.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="settings-container">
        <a href="home.php" class="back-btn">
            <i class="bi bi-chevron-left"></i> Retour à l'accueil
        </a>

        <div class="settings-card">
            <div class="d-flex align-items-center gap-3 mb-5">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-gear-fill text-primary" style="font-size: 1.5rem;"></i>
                </div>
                <h2 class="m-0 fw-bold">Paramètres du profil</h2>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> d-flex align-items-center">
                    <i
                        class="bi bi-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>-fill me-3 fs-4"></i>
                    <div><?= $message ?></div>
                </div>
            <?php endif; ?>

            <div class="settings-layout">
                <!-- Left Sidebar: Profile & Security -->
                <div class="settings-sidebar">
                    <form method="POST" enctype="multipart/form-data" autocomplete="off">
                        <!-- Image Section -->
                        <div class="profile-img-section mb-5 position-relative" style="width: fit-content; margin: 0 auto;">
                            <?php
                            $hasPhoto = !empty($user['photo']);
                            $photoPath = $hasPhoto ? '../images/' . $user['photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['prenom']) . '&background=random&color=fff&size=200';
                            ?>
                            <img src="<?= htmlspecialchars($photoPath) ?>" alt="Photo actuelle" class="profile-img-preview"
                                id="previewImg">

                            <?php if ($hasPhoto): ?>
                                <button type="button" class="btn-delete-profile-photo" onclick="confirmDeleteProfilePhoto()"
                                    title="Supprimer la photo">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php endif; ?>

                            <div class="custom-file-upload">
                                <label for="photo" class="file-label-custom">
                                    <i class="bi bi-camera-fill"></i>
                                    <span id="file-chosen">Changer la photo</span>
                                </label>
                                <input type="file" name="photo" id="photo" accept="image/*" onchange="previewFile()">
                            </div>
                        </div>

                        <div class="section-title">Informations Personnelles</div>

                        <div class="mb-4 position-relative">
                            <i class="bi bi-person form-icon"></i>
                            <div class="form-floating">
                                <input type="text" class="form-control" name="nom" id="nom" placeholder="Nom"
                                    value="<?= htmlspecialchars($user['nom']) ?>" required>
                                <label for="nom">Nom</label>
                            </div>
                        </div>

                        <div class="mb-4 position-relative">
                            <i class="bi bi-person form-icon"></i>
                            <div class="form-floating">
                                <input type="text" class="form-control" name="prenom" id="prenom" placeholder="Prénom"
                                    value="<?= htmlspecialchars($user['prenom']) ?>" required>
                                <label for="prenom">Prénom</label>
                            </div>
                        </div>

                        <div class="mb-4 position-relative">
                            <i class="bi bi-envelope form-icon"></i>
                            <div class="form-floating">
                                <input type="email" class="form-control" name="email" id="email" placeholder="Email"
                                    value="<?= htmlspecialchars($user['email']) ?>" required>
                                <label for="email">Email</label>
                            </div>
                        </div>

                        <div class="mb-4 position-relative">
                            <i class="bi bi-at form-icon"></i>
                            <div class="form-floating">
                                <input type="text" class="form-control" name="pseudo" id="pseudo" placeholder="Pseudo"
                                    value="<?= htmlspecialchars($user['pseudo']) ?>">
                                <label for="pseudo">Pseudo</label>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="section-title">Sécurité</div>

                        <div class="mb-4 position-relative">
                            <i class="bi bi-lock form-icon"></i>
                            <div class="form-floating">
                                <input type="password" class="form-control" name="password" id="password"
                                    placeholder="Nouveau mot de passe">
                                <label for="password">Nouveau mot de passe</label>
                            </div>
                            <button type="button" class="input-group-text-custom" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>

                        <div class="d-grid pt-2">
                            <button type="submit" class="save-btn btn btn-primary">
                                <i class="bi bi-check2-circle me-2"></i> Enregistrer le profil
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Main Content: Gallery -->
                <div class="settings-main">
                    <div class="section-title">Ma Galerie (À propos de moi)</div>
                    <p class="text-white-50 small mb-4">Partagez jusqu'à 6 photos avec vos contacts pour vous présenter (facultatif).</p>

                    <form method="POST" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="showcase_action" value="1">
                        <div class="row g-4">
                            <?php for ($i = 0; $i < 6; $i++):
                                $item = $showcaseItems[$i] ?? null;
                                $idSc = $item ? $item['id_showcase'] : '';
                                $imgPath = $item ? '../images/showcase/' . $item['image_path'] : '';
                                ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="showcase-card p-3 rounded-4"
                                        style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);">
                                        <input type="hidden" name="showcase_id[<?= $i ?>]" value="<?= $idSc ?>">
                                        <div class="mb-3 position-relative">
                                            <div class="showcase-preview-container mb-2"
                                                style="height: 150px; background: rgba(0,0,0,0.2); border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative; border: 1px dashed rgba(255,255,255,0.1);">
                                                <?php if ($imgPath): ?>
                                                    <img src="<?= $imgPath ?>" id="scPreview_<?= $i ?>"
                                                        style="width: 100%; height: 100%; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="bi bi-image text-white-25 fs-1" id="scIcon_<?= $i ?>"></i>
                                                    <img src="" id="scPreview_<?= $i ?>"
                                                        style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                                <?php endif; ?>
                                                <label for="sc_file_<?= $i ?>"
                                                    style="position: absolute; inset: 0; cursor: pointer;"></label>
                                                
                                                <?php if ($idSc): ?>
                                                    <button type="button" class="btn-delete-showcase" 
                                                            onclick="confirmDeleteShowcase(<?= $idSc ?>)"
                                                            title="Supprimer cette photo">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <input type="file" name="showcase_img[<?= $i ?>]" id="sc_file_<?= $i ?>"
                                                accept="image/*" style="display: none;" onchange="previewShowcase(this, <?= $i ?>)">
                                            <div class="text-center">
                                                <label for="sc_file_<?= $i ?>"
                                                    class="btn btn-sm btn-outline-light rounded-pill px-3"
                                                    style="font-size: 0.75rem;">
                                                    <i class="bi bi-camera me-1"></i> <?= $item ? 'Changer' : 'Ajouter' ?>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="form-floating">
                                            <textarea class="form-control" name="showcase_desc[<?= $i ?>]" id="sc_desc_<?= $i ?>"
                                                placeholder="Description"
                                                style="height: 80px; padding-left: 1rem;"><?= $item ? htmlspecialchars($item['description']) : '' ?></textarea>
                                            <label for="sc_desc_<?= $i ?>" style="padding-left: 1rem;">Description</label>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="save-btn btn btn-primary">
                                <i class="bi bi-cloud-arrow-up me-2"></i> Mettre à jour la galerie
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../JS/auth.js"></script>
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        function previewFile() {
            const file = document.getElementById('photo').files[0];
            const preview = document.getElementById('previewImg');
            const fileChosen = document.getElementById('file-chosen');

            if (file) {
                fileChosen.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        function previewShowcase(input, index) {
            const preview = document.getElementById('scPreview_' + index);
            const icon = document.getElementById('scIcon_' + index);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (icon) icon.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function confirmDeleteShowcase(id) {
            Swal.fire({
                title: 'Supprimer cette photo ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7b2ff7',
                cancelButtonColor: 'rgba(255,255,255,0.1)',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                background: '#1a1a2e',
                color: '#fff',
                backdrop: `rgba(0,0,123,0.4)`
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `<input type="hidden" name="delete_showcase_id" value="${id}">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function confirmDeleteProfilePhoto() {
            Swal.fire({
                title: 'Supprimer la photo de profil ?',
                text: "Votre profil sera moins personnalisé.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7b2ff7',
                cancelButtonColor: 'rgba(255,255,255,0.1)',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                background: '#1a1a2e',
                color: '#fff',
                backdrop: `rgba(0,0,123,0.4)`
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `<input type="hidden" name="delete_profile_photo" value="1">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>

</html>