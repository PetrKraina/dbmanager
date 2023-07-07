-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost
-- Vytvořeno: Čtv 06. čec 2023, 11:59
-- Verze serveru: 5.7.31
-- Verze PHP: 8.1.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `testovaci_data`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `objednavky`
--

CREATE TABLE `objednavky` (
  `id` int(11) NOT NULL,
  `id_uzivatele` int(11) NOT NULL,
  `cena` float NOT NULL,
  `datum` date NOT NULL,
  `mena` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Vypisuji data pro tabulku `objednavky`
--

INSERT INTO `objednavky` (`id`, `id_uzivatele`, `cena`, `datum`, `mena`) VALUES
(1, 1, 128, '2023-07-05', 'CZK'),
(2, 2, 999, '2023-07-06', 'CZK'),
(3, 7, 1000, '0000-00-00', 'CZK'),
(4, 8, 1000, '0000-00-00', 'CZK'),
(5, 9, 1000, '0000-00-00', 'CZK'),
(6, 10, 1000, '0000-00-00', 'CZK'),
(7, 11, 1000, '0000-00-00', 'CZK'),
(8, 12, 1000, '0000-00-00', 'CZK'),
(9, 2023, 2023, '2023-07-05', '2023-07-05 12:32:08'),
(10, 2023, 2023, '2023-07-05', '2023-07-05 12:32:57'),
(11, 2023, 2023, '2023-07-05', '2023-07-05 12:33:59'),
(12, 2023, 2023, '2023-07-05', '2023-07-05 12:34:24'),
(13, 2023, 2023, '2023-07-05', '2023-07-05 12:35:14'),
(14, 2023, 2023, '2023-07-05', '2023-07-05 12:35:45'),
(15, 2023, 2023, '2023-07-05', '2023-07-05 12:37:19'),
(16, 2023, 2023, '2023-07-05', '2023-07-05 12:37:46'),
(17, 2023, 2023, '2023-07-05', '2023-07-05 12:38:03'),
(18, 47, 1000, '2023-07-05', 'CZK'),
(19, 48, 1000, '2023-07-05', 'CZK'),
(20, 49, 1000, '2023-07-05', 'CZK'),
(33, 62, 1000, '2023-07-06', 'CZK'),
(34, 63, 1000, '2023-07-06', 'CZK'),
(35, 64, 1000, '2023-07-06', 'CZK');

-- --------------------------------------------------------

--
-- Struktura tabulky `uzivatele`
--

CREATE TABLE `uzivatele` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Vypisuji data pro tabulku `uzivatele`
--

INSERT INTO `uzivatele` (`id`, `name`, `email`, `phone`) VALUES
(1, 'Petr', 'kraina.petr@seznam.cz', '+420737050395'),
(2, 'Jana', 'jana@test.cz', '2023-07-06 10:30:36'),
(3, 'Jakub', 'j@j.com', '2023-07-06 10:30:36'),
(4, 'Jan', 'jan@jan.cz', '2023-07-06 10:30:36'),
(5, 'Petr', 'petr@petr.cz', ''),
(6, 'Petr', 'petr@petr.cz', ''),
(7, 'Petr', 'petr@petr.cz', ''),
(8, 'Petr', 'petr@petr.cz', ''),
(9, 'Petr', 'petr@petr.cz', ''),
(10, 'Petr', 'petr@petr.cz', ''),
(11, 'Petr', 'petr@petr.cz', ''),
(12, 'Petr', 'petr@petr.cz', ''),
(21, 'Petr', 'petr@petr.cz', ''),
(22, 'Petr', 'petr@petr.cz', ''),
(23, 'Petr', 'petr@petr.cz', ''),
(24, 'Petr', 'petr@petr.cz', ''),
(25, 'Petr', 'petr@petr.cz', ''),
(26, 'Petr', 'petr@petr.cz', ''),
(27, 'Petr', 'petr@petr.cz', ''),
(28, 'Petr', 'petr@petr.cz', ''),
(29, 'Petr', 'petr@petr.cz', ''),
(30, 'Petr', 'petr@petr.cz', ''),
(31, 'Petr', 'petr@petr.cz', ''),
(32, 'petr@petr.cz', 'petr@petr.cz', ''),
(33, 'petr@petr.cz', 'petr@petr.cz', ''),
(34, 'petr@petr.cz', 'petr@petr.cz', ''),
(35, 'petr@petr.cz', 'petr@petr.cz', ''),
(36, 'petr@petr.cz', 'petr@petr.cz', ''),
(37, 'petr@petr.cz', 'petr@petr.cz', ''),
(38, 'petr@petr.cz', 'petr@petr.cz', ''),
(39, 'petr@petr.cz', 'petr@petr.cz', ''),
(40, 'petr@petr.cz', 'petr@petr.cz', ''),
(41, 'petr@petr.cz', 'petr@petr.cz', ''),
(42, 'petr@petr.cz', 'petr@petr.cz', ''),
(43, 'petr@petr.cz', 'petr@petr.cz', ''),
(44, 'petr@petr.cz', 'petr@petr.cz', ''),
(45, 'petr@petr.cz', 'petr@petr.cz', ''),
(46, 'petr@petr.cz', 'petr@petr.cz', ''),
(47, 'Petr', 'petr@petr.cz', ''),
(48, 'Petr', 'petr@petr.cz', ''),
(49, 'Petr', 'petr@petr.cz', ''),
(62, 'Karel', 'petr@petr.cz', ''),
(63, 'Karel', 'petr@petr.cz', ''),
(64, 'Karel', 'petr@petr.cz', '');

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `objednavky`
--
ALTER TABLE `objednavky`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `uzivatele`
--
ALTER TABLE `uzivatele`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `objednavky`
--
ALTER TABLE `objednavky`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT pro tabulku `uzivatele`
--
ALTER TABLE `uzivatele`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
