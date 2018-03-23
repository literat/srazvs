CREATE TABLE `kk_visitors` (
  `guid` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `id` int(11) NOT NULL,
  `code` varchar(4) COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `surname` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `nick` varchar(20) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `birthday` date NOT NULL,
  `email` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `street` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `city` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `postal_code` varchar(6) COLLATE utf8_czech_ci NOT NULL,
  `province` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `group_num` varchar(6) COLLATE utf8_czech_ci NOT NULL,
  `group_name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `troop_name` varchar(50) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `comment` varchar(255) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `arrival` varchar(255) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `departure` varchar(255) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  `question` text COLLATE utf8_czech_ci NOT NULL,
  `question2` text COLLATE utf8_czech_ci NOT NULL,
  `bill` smallint(4) NOT NULL DEFAULT '0',
  `cost` smallint(6) NOT NULL DEFAULT '0',
  `checked` enum('0','1') COLLATE utf8_czech_ci NOT NULL DEFAULT '0',
  `reg_daytime` datetime NOT NULL,
  `meeting` smallint(4) NOT NULL,
  `hash` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `deleted` enum('0','1') COLLATE utf8_czech_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
