CREATE TABLE `kk_visitor-program` (
  `guid` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `id` int(11) NOT NULL,
  `visitor` int(11) NOT NULL,
  `program` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
