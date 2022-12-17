-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 17 déc. 2022 à 04:48
-- Version du serveur : 10.4.24-MariaDB
-- Version de PHP : 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_demandes_conges`
--

-- --------------------------------------------------------

--
-- Structure de la table `demandes_conges`
--

CREATE TABLE `demandes_conges` (
  `id_dc` int(11) NOT NULL,
  `id_employe` int(11) NOT NULL,
  `date_depart` date NOT NULL,
  `date_retour` date NOT NULL,
  `commentaire` text DEFAULT NULL,
  `statut` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: non traité,  1: Accepté, 2: refusé',
  `date_traitement` datetime(6) DEFAULT NULL,
  `justificatif_refus` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `demandes_conges`
--

INSERT INTO `demandes_conges` (`id_dc`, `id_employe`, `date_depart`, `date_retour`, `commentaire`, `statut`, `date_traitement`, `justificatif_refus`) VALUES
(1, 1, '2022-12-18', '2022-12-22', 'Mariage cousin', 1, '2022-12-17 04:47:23.000000', NULL),
(2, 1, '2023-01-01', '2023-01-05', 'Fete de nouvel an', 0, NULL, NULL),
(9, 1, '2023-06-01', '2023-06-15', NULL, 2, '2022-11-16 22:23:25.000000', 'Manque d\'effectifs'),
(10, 2, '2023-06-01', '2023-06-15', NULL, 0, NULL, NULL),
(12, 3, '2023-07-01', '2023-07-15', NULL, 0, NULL, NULL),
(13, 3, '2023-09-01', '2023-09-15', NULL, 2, '2022-12-09 02:40:46.000000', 'Besoin de vous'),
(14, 2, '2023-03-01', '2023-03-07', NULL, 2, '2022-12-17 03:53:25.000000', 'test test');

-- --------------------------------------------------------

--
-- Structure de la table `employe`
--

CREATE TABLE `employe` (
  `id_employe` int(11) NOT NULL,
  `nom_emp` varchar(100) NOT NULL,
  `prenom_emp` varchar(100) NOT NULL,
  `telephone_emp` varchar(13) NOT NULL,
  `email_emp` varchar(62) NOT NULL,
  `id_manager` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `employe`
--

INSERT INTO `employe` (`id_employe`, `nom_emp`, `prenom_emp`, `telephone_emp`, `email_emp`, `id_manager`) VALUES
(1, 'A_Tarik', 'Benali amar', '0793015593', 'tarikbenaliamar@gmail.com', 1),
(2, 'A_Abdou', 'Merzoug', '0770500963', 'abdoumg@gmail.com', 1),
(3, 'B_Nadir', 'Rahal', '0793015592', 'nadirrahal@gmail.com', 2),
(4, 'B_akram', 'panopli', '0793015590', 'akrampanopli@gmail.com', 2);

-- --------------------------------------------------------

--
-- Structure de la table `manager`
--

CREATE TABLE `manager` (
  `id_manager` int(11) NOT NULL,
  `nom_manager` varchar(100) NOT NULL,
  `prenom_manager` varchar(100) NOT NULL,
  `telephone` varchar(13) NOT NULL,
  `email_manager` varchar(62) NOT NULL,
  `role` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0: simple manager,  1: Administrateur'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `manager`
--

INSERT INTO `manager` (`id_manager`, `nom_manager`, `prenom_manager`, `telephone`, `email_manager`, `role`) VALUES
(1, 'Manager A', 'Ali Maamri', '0793015593', 'alimaamri@gmail.com', 1),
(2, 'Manager B', 'Mami Amine', '0770500962', 'aminemami@yahoo.com', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `demandes_conges`
--
ALTER TABLE `demandes_conges`
  ADD PRIMARY KEY (`id_dc`),
  ADD KEY `id_employe` (`id_employe`);

--
-- Index pour la table `employe`
--
ALTER TABLE `employe`
  ADD PRIMARY KEY (`id_employe`),
  ADD KEY `id_manager` (`id_manager`);

--
-- Index pour la table `manager`
--
ALTER TABLE `manager`
  ADD PRIMARY KEY (`id_manager`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `demandes_conges`
--
ALTER TABLE `demandes_conges`
  MODIFY `id_dc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `employe`
--
ALTER TABLE `employe`
  MODIFY `id_employe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `manager`
--
ALTER TABLE `manager`
  MODIFY `id_manager` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `demandes_conges`
--
ALTER TABLE `demandes_conges`
  ADD CONSTRAINT `demandes_conges_ibfk_1` FOREIGN KEY (`id_employe`) REFERENCES `employe` (`id_employe`);

--
-- Contraintes pour la table `employe`
--
ALTER TABLE `employe`
  ADD CONSTRAINT `employe_ibfk_1` FOREIGN KEY (`id_manager`) REFERENCES `manager` (`id_manager`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
