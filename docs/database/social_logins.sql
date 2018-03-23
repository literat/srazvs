CREATE TABLE `kk_social_logins` (
  `id` int(11) NOT NULL,
  `guid` varchar(45) COLLATE utf8_czech_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `provider` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
