CREATE TABLE `kk_programs` (
  `guid` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(1024) COLLATE utf8_czech_ci NOT NULL,
  `material` varchar(512) COLLATE utf8_czech_ci NOT NULL,
  `tutor` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `block` int(11) NOT NULL DEFAULT '0',
  `display_in_reg` enum('0','1') COLLATE utf8_czech_ci NOT NULL DEFAULT '1',
  `capacity` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `category` tinyint(3) NOT NULL DEFAULT '0',
  `deleted` enum('0','1') COLLATE utf8_czech_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;