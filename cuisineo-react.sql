-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 22 mars 2022 à 10:18
-- Version du serveur : 10.7.3-MariaDB
-- Version de PHP : 8.1.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cuisineo-react`
--

-- --------------------------------------------------------

--
-- Structure de la table `contractPrices`
--

CREATE TABLE `contractPrices` (
  `contract_ID` int(11) NOT NULL,
  `customerAddress_ID` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_Ivoire` int(11) DEFAULT 0,
  `contract_Silver` int(11) DEFAULT 0,
  `contract_Gold` int(11) DEFAULT 0,
  `contract_GoldPlus` int(11) DEFAULT 0,
  `contract_Platinium` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `contractPrices`
--

INSERT INTO `contractPrices` (`contract_ID`, `customerAddress_ID`, `contract_Ivoire`, `contract_Silver`, `contract_Gold`, `contract_GoldPlus`, `contract_Platinium`) VALUES
(78, 'CA102156            -10006347', 131, 215, 741, 870, 1467);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `contractPrices`
--
ALTER TABLE `contractPrices`
  ADD PRIMARY KEY (`contract_ID`),
  ADD UNIQUE KEY `customerAddress_ID` (`customerAddress_ID`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `contractPrices`
--
ALTER TABLE `contractPrices`
  MODIFY `contract_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
