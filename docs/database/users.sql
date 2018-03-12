CREATE TABLE `kk_users` (
  `id` int(11) NOT NULL,
  `guid` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `login` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `person_id` int(11) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
