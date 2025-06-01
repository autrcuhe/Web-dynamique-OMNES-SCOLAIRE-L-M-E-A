-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 01 juin 2025 à 16:56
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `omnes_scolaire`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories_services_internationaux`
--

DROP TABLE IF EXISTS `categories_services_internationaux`;
CREATE TABLE IF NOT EXISTS `categories_services_internationaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categories_services_internationaux`
--

INSERT INTO `categories_services_internationaux` (`id`, `nom`, `description`) VALUES
(1, 'Les universités partenaires', 'Explorez nos partenariats avec des universités à l\'étranger.'),
(2, 'Doubles diplômes à l\'international', 'Programmes permettant d\'obtenir deux diplômes.'),
(3, 'Apprentissage des langues', 'Cours et ressources pour l\'amélioration linguistique.'),
(4, 'Summer School', 'Programmes courts et intensifs durant l\'été à l\'étranger.');

-- --------------------------------------------------------

--
-- Structure de la table `cours_internationaux`
--

DROP TABLE IF EXISTS `cours_internationaux`;
CREATE TABLE IF NOT EXISTS `cours_internationaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `universite_id` int DEFAULT NULL,
  `nom_cours` varchar(255) NOT NULL,
  `description` text,
  `credits` varchar(50) DEFAULT NULL,
  `semestre` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `universite_id` (`universite_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cours_internationaux`
--

INSERT INTO `cours_internationaux` (`id`, `universite_id`, `nom_cours`, `description`, `credits`, `semestre`) VALUES
(1, 1, 'Advanced Robotics', 'Cours sur la robotique avancée et l\'IA.', '6 ECTS', 'Automne 2024'),
(2, 1, 'Sustainable Energy Systems', 'Étude des systèmes énergétiques durables.', '5 ECTS', 'Printemps 2025');

-- --------------------------------------------------------

--
-- Structure de la table `creneaux_disponibles`
--

DROP TABLE IF EXISTS `creneaux_disponibles`;
CREATE TABLE IF NOT EXISTS `creneaux_disponibles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `professeur_id` int DEFAULT NULL,
  `date` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `statut` enum('disponible','reservé','annulé') DEFAULT 'disponible',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `service_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `professeur_id` (`professeur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `creneaux_disponibles`
--

INSERT INTO `creneaux_disponibles` (`id`, `professeur_id`, `date`, `heure_debut`, `heure_fin`, `statut`, `created_at`, `updated_at`, `service_id`) VALUES
(1, 1, '2023-10-27', '09:00:00', '09:30:00', 'reservé', '2025-05-31 14:32:09', '2025-05-31 15:08:25', NULL),
(2, 1, '2023-10-27', '09:30:00', '10:00:00', 'disponible', '2025-05-31 14:32:09', '2025-06-01 16:44:59', NULL),
(3, 1, '2023-10-28', '14:00:00', '14:30:00', 'reservé', '2025-05-31 14:32:09', '2025-05-31 16:04:49', NULL),
(4, 1, '2023-10-27', '15:00:00', '15:30:00', 'reservé', '2025-05-31 14:32:09', '2025-05-31 14:32:09', NULL),
(5, 1, '2023-10-28', '17:30:00', '18:00:00', 'reservé', '2025-05-31 14:32:09', '2025-05-31 14:32:09', NULL),
(6, NULL, '2025-10-27', '14:00:00', '14:20:00', 'reservé', '2025-05-31 14:32:15', '2025-05-31 14:55:30', 1),
(7, NULL, '2025-10-27', '14:20:00', '14:40:00', 'reservé', '2025-05-31 14:32:15', '2025-05-31 15:44:00', 1),
(8, NULL, '2025-10-27', '14:40:00', '15:00:00', 'reservé', '2025-05-31 14:32:15', '2025-05-31 14:32:15', 1),
(9, NULL, '2025-10-29', '09:00:00', '09:20:00', 'reservé', '2025-05-31 14:32:15', '2025-05-31 15:07:34', 1),
(10, NULL, '2025-10-29', '09:20:00', '09:40:00', 'disponible', '2025-05-31 14:32:15', '2025-05-31 14:32:15', 1),
(11, NULL, '2025-10-29', '14:00:00', '14:20:00', 'disponible', '2025-05-31 14:32:15', '2025-05-31 14:32:15', 1),
(12, NULL, '2025-10-31', '09:00:00', '09:20:00', 'disponible', '2025-05-31 14:32:15', '2025-05-31 14:32:15', 1),
(13, NULL, '2025-10-31', '14:00:00', '14:20:00', 'reservé', '2025-05-31 14:32:15', '2025-05-31 14:32:15', 1),
(17, 7, '2025-06-02', '10:00:00', '11:00:00', 'disponible', '2025-06-01 17:45:49', '2025-06-01 17:45:49', NULL),
(16, 7, '2025-06-02', '09:00:00', '10:00:00', 'disponible', '2025-06-01 17:42:45', '2025-06-01 17:42:45', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

DROP TABLE IF EXISTS `departements`;
CREATE TABLE IF NOT EXISTS `departements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`id`, `nom`) VALUES
(1, 'Informatique'),
(2, 'Mathématiques'),
(3, 'Physique');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_user_id` int DEFAULT NULL COMMENT 'L''utilisateur qui envoie (null si système)',
  `receiver_user_id` int DEFAULT NULL COMMENT 'L''utilisateur qui reçoit (si message système ou réponse prof)',
  `receiver_prof_id` int DEFAULT NULL COMMENT 'Le professeur qui reçoit (si message étudiant)',
  `message_content` text NOT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `type` enum('communication','confirmation') NOT NULL COMMENT 'Type de message',
  `appointment_id` int DEFAULT NULL COMMENT 'Lien vers le rendez-vous si c''est une confirmation',
  PRIMARY KEY (`id`),
  KEY `sender_user_id` (`sender_user_id`),
  KEY `receiver_user_id` (`receiver_user_id`),
  KEY `receiver_prof_id` (`receiver_prof_id`),
  KEY `appointment_id` (`appointment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `sender_user_id`, `receiver_user_id`, `receiver_prof_id`, `message_content`, `timestamp`, `type`, `appointment_id`) VALUES
(1, 3, NULL, 1, 'Sujet : dii\n\njjjjgjgj', '2025-05-28 20:06:42', 'communication', NULL),
(2, NULL, 3, NULL, 'Votre rendez-vous avec le professeur 1 a été confirmé pour le 2023-10-27 à 09:00. Motif : sdhfjhsj', '2025-05-28 20:19:06', 'confirmation', 4),
(3, 3, NULL, 1, 'Sujet : VD\n\nOUCOU', '2025-05-28 22:59:28', 'communication', NULL),
(4, NULL, 3, NULL, 'Votre rendez-vous avec le professeur 1 a été confirmé pour le 2023-10-28 à 14:00. Motif : loo', '2025-05-28 23:01:16', 'confirmation', 5),
(5, NULL, 3, NULL, 'Votre rendez-vous avec Jean-Pierre SEGADO a été confirmé pour le 2023-10-27 à 10:00. Motif : ldld', '2025-05-28 23:05:46', 'confirmation', 6),
(6, NULL, 3, NULL, 'Votre rendez-vous avec Jean-Pierre SEGADO a été confirmé pour le 2023-10-27 à 09:00. Motif : dq', '2025-05-28 23:12:21', 'confirmation', 7),
(7, 1, 3, NULL, 'coucou', '2025-05-28 23:49:38', 'communication', NULL),
(8, 1, 3, NULL, 'DDZ', '2025-05-28 23:59:27', 'communication', NULL),
(9, 3, NULL, 1, 'LOLSLS', '2025-05-29 12:18:41', 'communication', NULL),
(10, 1, 3, NULL, 'cch', '2025-05-29 13:02:42', 'communication', NULL),
(11, 1, 3, NULL, 'ljljl', '2025-05-29 13:15:45', 'communication', NULL),
(12, 1, 3, NULL, 'jljlj', '2025-05-29 13:15:57', 'communication', NULL),
(13, 3, NULL, 1, 'FFZF', '2025-05-29 14:31:59', 'communication', NULL),
(14, 3, NULL, 1, 'FFFFFF', '2025-05-29 14:32:03', 'communication', NULL),
(15, 3, NULL, 1, 'FFFFF', '2025-05-29 14:32:08', 'communication', NULL),
(16, 3, NULL, 1, 'FFFF', '2025-05-29 14:32:20', 'communication', NULL),
(17, 3, NULL, 1, 'lklk', '2025-05-29 14:34:38', 'communication', NULL),
(18, 3, NULL, 1, 'gjgjg', '2025-05-29 14:37:07', 'communication', NULL),
(19, 3, NULL, 1, 'gg', '2025-05-29 14:37:18', 'communication', NULL),
(20, 1, 3, NULL, 'gjg', '2025-05-29 14:48:39', 'communication', NULL),
(21, NULL, 3, NULL, 'Votre rendez-vous avec Jean-Pierre SEGADO a été confirmé pour le 2023-10-27 à 09:00. Motif : nulll', '2025-05-29 14:54:10', 'confirmation', 8),
(22, 1, 3, NULL, 'Rendez-vous confirmé avec Jean-Pierre SEGADO pour le 2023-10-27 à 09:00.\nMotif : null', '2025-05-29 14:57:44', 'confirmation', 9),
(23, 3, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-27 à 09:00.\nMotif : null', '2025-05-29 14:57:44', 'confirmation', 9),
(24, 0, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-27 à 09:00.\nMotif : null', '2025-05-29 15:00:45', '', 10),
(25, 0, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-27 à 09:00.\nMotif : null', '2025-05-29 15:00:45', '', 10),
(26, 0, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-28 à 14:00.\nMotif : null', '2025-05-29 15:01:36', '', 11),
(27, 0, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-28 à 14:00.\nMotif : null', '2025-05-29 15:01:36', '', 11),
(28, 0, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-27 à 09:00.\nMotif : null', '2025-05-29 15:07:12', '', 12),
(29, 0, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-27 à 09:00.\nMotif : null', '2025-05-29 15:07:12', '', 12),
(30, 0, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-27 à 10:00.\nMotif : lolo', '2025-05-29 15:08:48', '', 13),
(31, 0, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-27 à 10:00.\nMotif : lolo', '2025-05-29 15:08:48', '', 13),
(32, 3, NULL, 1, 'coucou', '2025-05-29 15:09:11', 'communication', NULL),
(33, 0, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-28 à 14:00.\nMotif : lolo', '2025-05-29 15:12:36', '', 14),
(34, 0, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-28 à 14:00.\nMotif : lolo', '2025-05-29 15:12:36', '', 14),
(35, 0, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-27 à 09:00.\nMotif : null', '2025-05-29 15:17:39', '', 15),
(36, 0, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-27 à 09:00.\nMotif : null', '2025-05-29 15:17:39', '', 15),
(37, 0, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-27 à 10:00.\nMotif : vdv', '2025-05-29 15:18:06', '', 16),
(38, 0, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-27 à 10:00.\nMotif : vdv', '2025-05-29 15:18:06', '', 16),
(39, 0, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-28 à 14:00.\nMotif : gdh', '2025-05-29 15:22:44', '', 17),
(40, 0, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-28 à 14:00.\nMotif : gdh', '2025-05-29 15:22:44', '', 17),
(41, 0, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-27 à 09:00.\nMotif : lolo', '2025-05-29 15:25:47', '', 18),
(42, 0, NULL, 1, 'Nouveau rendez-vous avec Emma Dupont pour le 2023-10-27 à 09:00.\nMotif : lolo', '2025-05-29 15:25:47', '', 18),
(43, 1, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-27 à 09:00.\nMotif : vgsg', '2025-05-29 15:39:15', 'communication', 19),
(44, 1, 6, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-27 à 10:00.\nMotif : nul', '2025-05-29 18:32:40', 'communication', 20),
(45, 1, 6, NULL, 'coucou', '2025-05-29 18:50:38', 'communication', NULL),
(46, 1, 1, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-28 à 14:00.\nMotif : sasa', '2025-05-29 18:50:52', 'communication', 21),
(47, 1, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-27 à 09:00.\nMotif : frf', '2025-05-29 18:54:08', 'communication', 22),
(48, 0, 3, NULL, 'Votre rendez-vous avec Relation Internationale a été confirmé pour le 29/10/2025 à 09:00.\nMotif : hflhjflhl', '2025-05-31 15:07:34', 'communication', 23),
(49, 0, 3, NULL, 'Votre rendez-vous avec Relation Internationale a été confirmé pour le 27/10/2025 à 14:20.\nMotif : xzz', '2025-05-31 15:44:00', 'communication', 24),
(50, 1, 3, NULL, 'Votre rendez-vous avec Jean-Pierre segado a été confirmé pour le 2023-10-28 à 14:00.\nMotif : bg,,g', '2025-05-31 16:04:49', 'communication', 25),
(51, 6, NULL, 1, 'super', '2025-06-01 00:46:20', 'communication', NULL),
(52, 6, NULL, 1, 'null', '2025-06-01 15:23:24', 'communication', NULL),
(53, 6, NULL, 2, 'Bonjour Professeur,', '2025-06-01 13:31:52', 'communication', NULL),
(54, 6, NULL, 4, 'Bonjour Professeur,', '2025-06-01 13:32:25', 'communication', NULL),
(55, 6, NULL, 4, 'jj', '2025-06-01 15:32:40', 'communication', NULL),
(56, 6, NULL, 3, 'Bonjour Professeur,', '2025-06-01 13:33:03', 'communication', NULL),
(57, 6, NULL, 7, 'Bonjour Professeur,', '2025-06-01 13:46:02', 'communication', NULL),
(58, 6, NULL, 15, 'Bonjour Professeur,', '2025-06-01 13:48:59', 'communication', NULL),
(59, 6, NULL, 16, 'Bonjour Professeur,', '2025-06-01 13:54:36', 'communication', NULL),
(60, 2, NULL, 15, 'Bonjour Professeur,', '2025-06-01 15:43:16', 'communication', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `professeurs`
--

DROP TABLE IF EXISTS `professeurs`;
CREATE TABLE IF NOT EXISTS `professeurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `bureau` varchar(50) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `disponibilite` text,
  `cv` varchar(255) DEFAULT NULL,
  `departement_id` int DEFAULT NULL,
  `acces` text,
  `materielles_demandes` text,
  `laboratoire_recherche` varchar(255) DEFAULT NULL,
  `utilisateur_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  KEY `departement_id` (`departement_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `professeurs`
--

INSERT INTO `professeurs` (`id`, `nom`, `prenom`, `photo`, `bureau`, `telephone`, `email`, `disponibilite`, `cv`, `departement_id`, `acces`, `materielles_demandes`, `laboratoire_recherche`, `utilisateur_id`) VALUES
(1, 'SEGADO', 'Jean-Pierre', 'image/segado.jpg', 'P-428', '+33 01 02 03 04 05', 'jean-pierre.segado@ece.fr', 'Lundi matin, Jeudi toute la journée', 'cv_1.xml', 1, '3315', 'Ordinateur', 'Systèmes intelligents communicants', 1),
(2, 'DUPONT', 'Alice', 'https://via.placeholder.com/120x120?text=Alice+D', 'P-430', '+33 01 02 03 04 06', 'alice.dupont@ece.fr', 'Mardi après-midi, Vendredi matin', '#', 1, '1234', 'Ordinateur', NULL, 4),
(3, 'MARTIN', 'Paul', 'https://via.placeholder.com/120x120?text=Paul+M', 'M-210', '+33 01 02 03 04 07', 'paul.martin@ece.fr', 'Mercredi matin, Jeudi après-midi', '#', 2, '4255', 'Feuilles\r\nstylo\r\nExercices à travailler', NULL, 7),
(4, 'LEGRAND', 'Sophie', 'https://via.placeholder.com/120x120?text=Sophie+L', 'PH-101', '+33 01 02 03 04 08', 'sophie.legrand@ece.fr', 'Lundi après-midi, Vendredi toute la journée', '#', 3, NULL, 'Feuilles\r\nstylo\r\nExercices à travailler', NULL, 8),
(7, 'Palasi', 'Julienne', NULL, 'SC212', '0612345678', 'slfzlvcc@omnes.fr', NULL, NULL, 1, '3312', NULL, NULL, 15),
(8, 'CHAARI', 'Anis', NULL, 'EM306', '0614536564', 'anis.chaar@omnes.fr', NULL, NULL, 2, '2134', NULL, NULL, 16),
(9, 'Blanc', 'Damien', NULL, 'EM226', '0698452468', 'damien.blanc@omesn.fr', NULL, 'cv_9.xml', 3, '2587', NULL, 'Nanoscience et nanotechnologie pour l\'ingénierie', 17);

-- --------------------------------------------------------

--
-- Structure de la table `programmes_doubles_diplomes`
--

DROP TABLE IF EXISTS `programmes_doubles_diplomes`;
CREATE TABLE IF NOT EXISTS `programmes_doubles_diplomes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `universite_partenaire_id` int DEFAULT NULL,
  `nom_programme` varchar(191) NOT NULL,
  `description` text,
  `duree` varchar(100) DEFAULT NULL,
  `conditions_admission` text,
  `diplome_obtenu` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_programme` (`nom_programme`),
  KEY `universite_partenaire_id` (`universite_partenaire_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `programmes_doubles_diplomes`
--

INSERT INTO `programmes_doubles_diplomes` (`id`, `universite_partenaire_id`, `nom_programme`, `description`, `duree`, `conditions_admission`, `diplome_obtenu`) VALUES
(1, 4, 'Master Ingénierie Structurale Internationale', NULL, '2 ans', NULL, 'Master of Science in Civil Engineering (double degree)');

-- --------------------------------------------------------

--
-- Structure de la table `programmes_langues`
--

DROP TABLE IF EXISTS `programmes_langues`;
CREATE TABLE IF NOT EXISTS `programmes_langues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_programme` varchar(191) NOT NULL,
  `langue` varchar(100) DEFAULT NULL,
  `niveau` varchar(50) DEFAULT NULL,
  `format` varchar(100) DEFAULT NULL,
  `description` text,
  `duree` varchar(100) DEFAULT NULL,
  `frais` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_programme` (`nom_programme`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `programmes_langues`
--

INSERT INTO `programmes_langues` (`id`, `nom_programme`, `langue`, `niveau`, `format`, `description`, `duree`, `frais`) VALUES
(1, 'Cours Intensif d\'Anglais', 'Anglais', 'Intermédiaire', 'Intensif', 'Améliorez rapidement votre anglais pour un contexte professionnel.', '4 semaines', 800.00),
(2, 'Préparation au TOEFL/IELTS', 'Anglais', 'Avancé', 'En ligne', 'Préparez les examens de compétence linguistique reconnus mondialement.', '8 semaines', 450.00);

-- --------------------------------------------------------

--
-- Structure de la table `programmes_summer_school`
--

DROP TABLE IF EXISTS `programmes_summer_school`;
CREATE TABLE IF NOT EXISTS `programmes_summer_school` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_programme` varchar(191) NOT NULL,
  `universite_partenaire_id` int DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `description` text,
  `domaines_etude` text,
  `dates_debut` date DEFAULT NULL,
  `dates_fin` date DEFAULT NULL,
  `duree` varchar(100) DEFAULT NULL,
  `frais` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_programme` (`nom_programme`),
  KEY `universite_partenaire_id` (`universite_partenaire_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `programmes_summer_school`
--

INSERT INTO `programmes_summer_school` (`id`, `nom_programme`, `universite_partenaire_id`, `ville`, `pays`, `description`, `domaines_etude`, `dates_debut`, `dates_fin`, `duree`, `frais`) VALUES
(1, 'Summer Program in AI and Data Science', 3, 'Toronto', 'Canada', 'Introduction et approfondissement en Intelligence Artificielle et Science des Données.', 'Informatique, Science des Données', '2025-07-01', '2025-07-26', '4 semaines', 2500.00),
(2, 'European Business Summer School', NULL, 'Berlin', 'Allemagne', 'Panorama du monde des affaires en Europe.', 'Affaires, Économie', '2025-07-15', '2025-08-05', '3 semaines', 1800.00);

-- --------------------------------------------------------

--
-- Structure de la table `rendezvous`
--

DROP TABLE IF EXISTS `rendezvous`;
CREATE TABLE IF NOT EXISTS `rendezvous` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `professeur_id` int DEFAULT NULL,
  `creneau_id` int NOT NULL,
  `motif` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `service_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_creneau` (`creneau_id`),
  KEY `client_id` (`client_id`),
  KEY `professeur_id` (`professeur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `rendezvous`
--

INSERT INTO `rendezvous` (`id`, `client_id`, `professeur_id`, `creneau_id`, `motif`, `created_at`, `service_id`) VALUES
(22, 3, 1, 1, 'frf', '2025-05-29 18:54:08', NULL),
(23, 3, NULL, 9, 'hflhjflhl', '2025-05-31 15:07:34', 1),
(24, 3, NULL, 7, 'xzz', '2025-05-31 15:44:00', 1),
(25, 3, 1, 3, 'bg,,g', '2025-05-31 16:04:49', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `salles_international`
--

DROP TABLE IF EXISTS `salles_international`;
CREATE TABLE IF NOT EXISTS `salles_international` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `localisation` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `salles_international`
--

INSERT INTO `salles_international` (`id`, `nom`, `localisation`) VALUES
(1, 'Salle P-425', 'Bâtiment P, 4ème étage'),
(2, 'Salle B-101', 'Bâtiment B, 1er étage'),
(3, 'Salle C-302', 'Bâtiment C, 3ème étage');

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`id`, `nom`, `description`) VALUES
(1, 'Relation Internationale', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `universites_partenaires`
--

DROP TABLE IF EXISTS `universites_partenaires`;
CREATE TABLE IF NOT EXISTS `universites_partenaires` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) NOT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `adresse` text,
  `contact_nom` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_telephone` varchar(50) DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `universites_partenaires`
--

INSERT INTO `universites_partenaires` (`id`, `nom`, `pays`, `ville`, `adresse`, `contact_nom`, `contact_email`, `contact_telephone`, `site_web`, `description`) VALUES
(1, 'Université Technique de Munich', 'Allemagne', 'Munich', NULL, NULL, 'info@tum.de', NULL, 'https://www.tum.de/', NULL),
(2, 'Université de Tokyo', 'Japon', 'Tokyo', NULL, NULL, 'admission@u-tokyo.ac.jp', NULL, 'https://www.u-tokyo.ac.jp/', NULL),
(3, 'Université de Toronto', 'Canada', 'Toronto', NULL, NULL, 'future.students@utoronto.ca', NULL, 'https://www.utoronto.ca/', NULL),
(4, 'École Polytechnique Fédérale de Lausanne', 'Suisse', 'Lausanne', NULL, NULL, 'info@epfl.ch', NULL, 'https://www.epfl.ch/', NULL),
(5, 'Université Tsinghua', 'Chine', 'Pékin', NULL, NULL, 'admission@tsinghua.edu.cn', NULL, 'https://www.tsinghua.edu.cn/', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `type` enum('admin','personnel','client') NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `adresse_ligne1` varchar(255) DEFAULT NULL,
  `adresse_ligne2` varchar(255) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `carte_etudiante` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `type`, `date_creation`, `adresse_ligne1`, `adresse_ligne2`, `ville`, `code_postal`, `pays`, `carte_etudiante`) VALUES
(2, 'Englender', 'Lorenzo', 'lorenzo.englender05@gmail.com', '$2y$10$k/6mziYf79zO.MriQyCBy.PwKYtxpXQFk4X3za2BjtN8pL/f6WndO', NULL, 'admin', '2025-05-27 20:14:57', NULL, NULL, NULL, NULL, NULL, NULL),
(1, 'segado', 'Jean-Pierre', 'jeanpierre.segado@omnes.fr', '$2y$10$k/6mziYf79zO.MriQyCBy.PwKYtxpXQFk4X3za2BjtN8pL/f6WndO', '0603054657', 'personnel', '2025-05-27 20:14:57', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Dupont', 'Emma', 'emma.dupont@gmail.com', '$2y$10$k/6mziYf79zO.MriQyCBy.PwKYtxpXQFk4X3za2BjtN8pL/f6WndO', '0603054657', 'client', '2025-05-27 20:14:57', 'ville de nulos', NULL, 'nulosland', '92000', 'france', '11EG3144'),
(6, 'Paschal', 'Eliot', 'paschal.eliot@gmail.com', '$2y$10$LF1VLdnaulgeezwFF2MVduy3xhlL55Y05Fm8gAet6lJsyypIahLdC', '0603054657', 'client', '2025-05-29 18:28:00', '7 Villa Blanche', '', 'Nanterre', '92000', 'France', '123ERT'),
(16, 'CHAARI', 'Anis', 'anis.chaar@omnes.fr', '$2y$10$/VKqZOrjVyUW6I4C/IXEmuODu7A03rXA9WdEYslaYLBupN5ipdWEK', NULL, 'personnel', '2025-06-01 15:07:55', NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'Palasi', 'Julienne', 'slfzlvcc@omnes.fr', '$2y$10$daZrHbgR3dvHhQBS9qqhFenfd3ZAAguJe64K5.N4.ZkiaKSOZ7AYm', NULL, 'personnel', '2025-05-30 15:38:51', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Dupont ', 'Alice', 'alice.dupont@ece.fr', 'lolololo', '0612345678', 'personnel', '2025-05-31 18:02:31', '55  rue des roses', NULL, 'Paris', '75000', 'France', NULL),
(7, 'Martin', 'Paul', 'paul.martin@omnes.fr', 'lolololo', '0634563243', 'personnel', '2025-06-01 15:51:03', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'Legrand', 'Sophie', 'sophie.legrand@omnes.fr', 'lolololo', '0613243546', 'personnel', '2025-06-01 15:52:17', NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'Blanc', 'Damien', 'damien.blanc@omesn.fr', '$2y$10$ejQalZUeBO6Qm4c/3h/f2uHsKOs/R4p1oOOsum26/r9iY2dXAmEP2', NULL, 'personnel', '2025-06-01 18:18:30', NULL, NULL, NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
