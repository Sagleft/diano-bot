CREATE TABLE `channels` (
  `id` int(11) NOT NULL,
  `messenger` set('default','utopia','telegram') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `channelid` varchar(96) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channelid` (`channelid`);


ALTER TABLE `channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
