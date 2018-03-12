--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `kk_blocks`
--
ALTER TABLE `kk_blocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `meeting` (`meeting`);

--
-- Klíče pro tabulku `kk_categories`
--
ALTER TABLE `kk_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kid` (`id`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Klíče pro tabulku `kk_meals`
--
ALTER TABLE `kk_meals`
  ADD PRIMARY KEY (`guid`);

--
-- Klíče pro tabulku `kk_meetings`
--
ALTER TABLE `kk_meetings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `guid` (`guid`);

--
-- Klíče pro tabulku `kk_persons`
--
ALTER TABLE `kk_persons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guid` (`guid`);

--
-- Klíče pro tabulku `kk_programs`
--
ALTER TABLE `kk_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `block` (`block`);

--
-- Klíče pro tabulku `kk_provinces`
--
ALTER TABLE `kk_provinces`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `kk_settings`
--
ALTER TABLE `kk_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_name` (`name`);

--
-- Klíče pro tabulku `kk_social_logins`
--
ALTER TABLE `kk_social_logins`
  ADD PRIMARY KEY (`id`,`guid`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD UNIQUE KEY `guid_UNIQUE` (`guid`),
  ADD KEY `social_login_user` (`user_id`);

--
-- Klíče pro tabulku `kk_users`
--
ALTER TABLE `kk_users`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD UNIQUE KEY `guid_UNIQUE` (`guid`),
  ADD KEY `person` (`person_id`);

--
-- Klíče pro tabulku `kk_visitor-program`
--
ALTER TABLE `kk_visitor-program`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `INDEX` (`visitor`,`program`),
  ADD KEY `program` (`program`);

--
-- Klíče pro tabulku `kk_visitors`
--
ALTER TABLE `kk_visitors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guid` (`guid`),
  ADD KEY `code` (`code`),
  ADD KEY `hash` (`hash`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `kk_blocks`
--
ALTER TABLE `kk_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=415;

--
-- AUTO_INCREMENT pro tabulku `kk_categories`
--
ALTER TABLE `kk_categories`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pro tabulku `kk_meetings`
--
ALTER TABLE `kk_meetings`
  MODIFY `id` smallint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pro tabulku `kk_persons`
--
ALTER TABLE `kk_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT pro tabulku `kk_programs`
--
ALTER TABLE `kk_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=339;

--
-- AUTO_INCREMENT pro tabulku `kk_provinces`
--
ALTER TABLE `kk_provinces`
  MODIFY `id` tinyint(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pro tabulku `kk_settings`
--
ALTER TABLE `kk_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `kk_social_logins`
--
ALTER TABLE `kk_social_logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pro tabulku `kk_users`
--
ALTER TABLE `kk_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `kk_visitor-program`
--
ALTER TABLE `kk_visitor-program`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9500;

--
-- AUTO_INCREMENT pro tabulku `kk_visitors`
--
ALTER TABLE `kk_visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1476;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `kk_social_logins`
--
ALTER TABLE `kk_social_logins`
  ADD CONSTRAINT `social_login_user` FOREIGN KEY (`user_id`) REFERENCES `kk_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `kk_users`
--
ALTER TABLE `kk_users`
  ADD CONSTRAINT `users_persons` FOREIGN KEY (`person_id`) REFERENCES `kk_persons` (`id`) ON DELETE CASCADE;
COMMIT;
