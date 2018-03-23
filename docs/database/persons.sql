-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost
-- Vytvořeno: Pát 23. bře 2018, 10:57
-- Verze serveru: 5.7.20-log
-- Verze PHP: 7.0.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `vodni`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `kk_persons`
--

DROP TABLE IF EXISTS `kk_persons`;
CREATE TABLE `kk_persons` (
  `id` int(11) NOT NULL,
  `guid` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `surname` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `nick` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `birthday` date NOT NULL,
  `email` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `street` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `city` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `postal_code` varchar(6) COLLATE utf8_czech_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `kk_persons`
--
ALTER TABLE `kk_persons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guid` (`guid`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `kk_persons`
--
ALTER TABLE `kk_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
