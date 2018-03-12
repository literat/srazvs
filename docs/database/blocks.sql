CREATE TABLE `kk_blocks` (
  `guid` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `from` time NOT NULL,
  `to` time NOT NULL,
  `day` enum('pátek','sobota','neděle') COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(1024) COLLATE utf8_czech_ci DEFAULT NULL,
  `material` varchar(512) COLLATE utf8_czech_ci DEFAULT NULL,
  `tutor` varchar(64) COLLATE utf8_czech_ci DEFAULT NULL,
  `email` varchar(64) COLLATE utf8_czech_ci DEFAULT NULL,
  `program` enum('0','1') COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `display_progs` enum('0','1') COLLATE utf8_czech_ci NOT NULL DEFAULT '1',
  `capacity` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `category` tinyint(3) NOT NULL DEFAULT '0',
  `meeting` smallint(4) NOT NULL,
  `deleted` enum('0','1') COLLATE utf8_czech_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
