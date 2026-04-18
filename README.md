# Application de Messagerie Instantanée (Chat App)

Bienvenue sur le dépôt de l'application de messagerie instantanée. C'est une application web complète qui permet aux utilisateurs de discuter en temps réel, de gérer leur profil et offre une interface d'administration avancée.

## 🚀 Fonctionnalités Principales

### Pour les Utilisateurs :
- **Inscription & Connexion :** Création de compte sécurisée.
- **Messagerie en temps réel :** Discutez avec d'autres utilisateurs de manière dynamique (sans rechargement de page).
- **Notifications :** Bip sonore à la réception d'un nouveau message, et affichage en priorité des messages non lus de manière automatique.
- **Profil Utilisateur :**
  - Modification des informations personnelles via les paramètres (`settings`).
  - Ajout d'une photo de profil.
  - Fonctionnalité "À propos de moi" : Possibilité de publier une galerie de présentation allant jusqu'à 6 photos avec des descriptions facultatives.
- **Design Réactif (Responsive) :** Interface optimisée pour s'adapter à toutes les tailles d'écrans (Smartphones, Tablettes, Ordinateurs).

### Pour les Administrateurs :
- **Tableau de Bord Administrateur (`admin_dashboard`) :** Interface dédiée pour gérer la plateforme.
- **Gestion des Utilisateurs :** Possibilité de bloquer/débloquer des utilisateurs.
- **Statistiques et Alertes :**
  - Compteur du nombre total d'utilisateurs.
  - Alertes / Notifications automatiques lors de l'inscription d'un nouvel utilisateur sur la plateforme.

## 🛠️ Technologies Utilisées
- **Front-end :** HTML, CSS, JavaScript (avec des appels asynchrones / Ajax ou Fetch API pour le temps réel)
- **Back-end :** PHP
- **Base de Données :** MySQL (via PDO en PHP)

## 📁 Structure du Projet

- `/CSS` : Feuilles de style de l'application pour le design.
- `/HTML` : Fichiers statiques et la structure des pages si requise.
- `/JS` : Fichiers JavaScript gérant les événements, le chat asynchrone et les notifications.
- `/PHP` : Cœur de l'application (logique métier : connexion, requêtes de chat, actions admins).
- `/database` : Scripts SQL pour créer et configurer la base de données.
- `/images` : Dossier de stockage des images (photos de profil, galeries utilisateurs).

## 💻 Installation & Configuration

1. **Prérequis :** Avoir un environnement serveur local (tel que XAMPP, WAMP, ou MAMP) installé avec PHP et MySQL.
2. **Cloner / Déplacer le projet :** Placez le dossier du projet (`AML`) dans le répertoire web racine de votre serveur local (par exemple, `C:/xampp/htdocs/AML`).
3. **Base de données :**
   - Importez la base de données via le fichier situé dans `/database` ou exécutez le script d'initialisation `/PHP/setup_db.php`.
   - Modifiez les paramètres de connexion à la base de données si nécessaire dans le fichier de configuration (`/PHP/connexion.php`).
4. **Démarrer l'application :** Lancez Apache et MySQL et rendez-vous sur `http://localhost/AML/` via votre navigateur.

---
*Ce projet est pensé pour offrir une expérience fluide, rapide et portable grâce à son approche responsive et ses fonctionnalités temps réel.*
