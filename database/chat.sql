DROP DATABASE IF EXISTS chat;
CREATE DATABASE chat;
USE chat;

-- *********************************Creation des tables******************************************

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id_users INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    pseudo VARCHAR(255),
    photo VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'blocked') DEFAULT 'active',
    last_activity DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `users` (`id_users`, `password`, `email`, `nom`, `prenom`, `pseudo`, `photo`, `role`, `status`, `last_activity`, `created_at`) VALUES
(1, '$2y$10$dljCecmF7nQ/YcXWeZVppOY51u1.iaVnRBR2Ay7CyY0j5YdyttEZG', 'alves@gmail.com', 'ALves', 'Daniel', 'Mr.Alves', '69e33be6bb112.jpg', 'admin', 'active', '2026-04-18 09:12:35', '2025-12-11 09:03:28'),
(2, '$2y$10$yU7XrEe3fdKeRbpVr5gDiuetEW4d658GvbXCx4BnXjjQQJf3G8ugq', 'nehemie@gmail.com', 'Hapsibisie', 'Nehemie', 'Bathelemie', '693a97e199cca.jpg', 'user', 'active', '2026-04-18 09:13:20', '2025-12-11 09:07:29'),
(5, '$2y$10$fJlC2QJgLWhnX4QsIWRRUuICaM1G11vRnP64t1znD8FEu8i0s51I.', 'rick@gmail.com', 'Rick', 'Sanches', 'Big Dadi Rick', '693d07f76b6b1.png', 'user', 'active', NULL, '2025-12-13 05:30:15'),
(6, '$2y$10$PztwdtmNYND2yj7AwOJQ/eWip56OpzJ9CL4VV9.Jjc1skl5K5O0Hu', 'mbianda@gmail.com', 'Mbianda', 'Oc├®ane', 'Djoumbox', '693d0859bc874.jpeg', 'user', 'active', NULL, '2025-12-13 05:31:53');



DROP TABLE IF EXISTS user_showcase;
CREATE TABLE user_showcase (
    id_showcase INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id_users) ON DELETE CASCADE
);


DROP TABLE IF EXISTS messages;
CREATE TABLE messages(
    id_messages INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(sender_id) REFERENCES users(id_users) ON DELETE CASCADE,
    FOREIGN KEY(receiver_id) REFERENCES users(id_users) ON DELETE CASCADE
);

DROP TABLE IF EXISTS announcements;
CREATE TABLE announcements (
    id_announcement INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- super admin (alves@gmail.com) password: 123