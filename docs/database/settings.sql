CREATE TABLE `kk_settings` (
  `guid` varchar(32) NOT NULL,
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL,
  `value` longtext NOT NULL COMMENT 'json encoded'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
