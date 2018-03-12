CREATE TABLE `kk_provinces` (
  `guid` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `id` tinyint(2) NOT NULL,
  `province_name` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `deleted` enum('0','1') COLLATE utf8_czech_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
