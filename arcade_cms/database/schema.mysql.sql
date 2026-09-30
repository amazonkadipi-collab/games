-- Extracted from arcade_cms/assets/includes/core.php
-- Source CMS schema/data definitions; MySQL/MariaDB syntax retained intentionally.

CREATE TABLE IF NOT EXISTS `gm_account` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` text COLLATE utf8_unicode_ci NOT NULL,
                `username` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
                `password` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
                `admin` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
                `email` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
                `avatar_id` int(11) NOT NULL,
                `xp` int(11) NOT NULL,
                `language` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
                `profile_theme` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'style-1',
                `ip` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
                `registration_date` int(11) NOT NULL,
                `last_logged` int(11) NOT NULL,
                `last_update_info` int(11) NOT NULL,
                `active` enum('1','0') COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`) 
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `gm_account` (`id`, `name`, `username`, `password`, `admin`, `email`, `xp`, `language`, `profile_theme`, `ip`, `registration_date`, `active`) VALUES (1, 'Administrator', '{$admin_user}', '{$admin_password}', '1', 'admin@admin.com', 0, 'english', 'style-1', '::0', 1478417322, '1');

CREATE TABLE IF NOT EXISTS `gm_users` (
                `user_id` int(11) NOT NULL,
                `gender` enum('1','2') CHARACTER SET utf8 NOT NULL DEFAULT '1',
                `about` text COLLATE utf8_unicode_ci NOT NULL,
                UNIQUE KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `gm_users` (`user_id`, `gender`) VALUES (1, '1');

CREATE TABLE IF NOT EXISTS `gm_ads` (
                `id` int(11) NOT NULL,
                `728x90` varchar(700) COLLATE utf8_unicode_ci NOT NULL,
                `300x250` varchar(700) COLLATE utf8_unicode_ci NOT NULL,
                `600x300` varchar(700) COLLATE utf8_unicode_ci NOT NULL,
                `728x90_main` varchar(700) COLLATE utf8_unicode_ci NOT NULL,
                `300x250_main` varchar(700) COLLATE utf8_unicode_ci NOT NULL,
                `ads_video` varchar(700) COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `gm_ads` (`id`, `728x90`, `300x250`, `600x300`, `728x90_main`, `300x250_main`, `ads_video`) VALUES (1, '', '', '', '', '', '');

CREATE TABLE IF NOT EXISTS `gm_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `category_pilot` varchar(250) CHARACTER SET utf8 NOT NULL,
                `name` text COLLATE utf8_unicode_ci NOT NULL,
                `image` varchar(400) COLLATE utf8_unicode_ci NOT NULL,
                `footer_description` TEXT NULL DEFAULT '',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `gm_categories` (`id`, `category_pilot`, `name`, `image`) VALUES
(1, 'action-games', 'Action Games', '/cat/action-games.jpg'),
(2, 'racing-games', 'Racing Games', '/cat/racing-games.png'),
(3, 'shooting-games', 'Shooting Games', '/cat/shooting-games.jpg'),
(4, 'arcade-games', 'Arcade Games', '/cat/arcade-games.jpg'),
(5, 'puzzle-games', 'Puzzle Games', '/cat/puzzle-games.jpg'),
(6, 'strategy-games', 'Strategy Games', '/cat/strategy-games.png'),
(7, 'multiplayer-games', 'Multiplayer Games', '/cat/multiplayer-games.jpg'),
(8, 'sports-games', 'Sports Games', '/cat/sports-games.png'),
(9, 'fighting-games', 'Fighting Games', '/cat/fighting-games.png');;

CREATE TABLE IF NOT EXISTS `gm_games` (
                `game_id` int(11) NOT NULL AUTO_INCREMENT,
                `catalog_id` varchar(250) CHARACTER SET latin1 NOT NULL,
                `game_name` varchar(250) CHARACTER SET latin1 NOT NULL,
                `name` varchar(250) COLLATE latin1_swedish_ci NOT NULL,
                `image` varchar(500) COLLATE latin1_swedish_ci NOT NULL,
                `import` enum('0','1') COLLATE latin1_swedish_ci NOT NULL DEFAULT '0',
                `category` int(11) NOT NULL,
                `plays` int(11) NOT NULL,
                `rating` enum('0','0.5','1','1.5','2','2.5','3','3.5','4','4.5','5') COLLATE latin1_swedish_ci NOT NULL DEFAULT '0',
                `description` varchar(15000) COLLATE latin1_swedish_ci NOT NULL,
                `instructions` varchar(600) COLLATE latin1_swedish_ci NOT NULL,
                `file` varchar(500) COLLATE latin1_swedish_ci NOT NULL,
                `game_type` varchar(250) COLLATE latin1_swedish_ci NOT NULL,
                `w` int(10) NOT NULL,
                `h` int(10) NOT NULL,
                `date_added` int(11) NOT NULL,
                `published` enum('0','1') COLLATE latin1_swedish_ci NOT NULL,
                `featured` enum('0','1') COLLATE latin1_swedish_ci NOT NULL DEFAULT '0',
                `mobile` int(255) NOT NULL,
                `featured_sorting` varchar(255) NOT NULL,
                `field_1` varchar(500) NOT NULL,
                `field_2` varchar(500) NOT NULL,
                `field_3` varchar(500) NOT NULL,
                `field_4` varchar(500) NOT NULL,
                `field_5` varchar(500) NOT NULL,
                `field_6` varchar(500) NOT NULL,
                `field_7` varchar(500) NOT NULL,
                `field_8` varchar(500) NOT NULL,
                `field_9` varchar(500) NOT NULL,
                `field_10` varchar(500) NOT NULL,
                `tags_ids` JSON NULL DEFAULT NULL,
                `video_url` VARCHAR(100) NULL DEFAULT NULL,
                PRIMARY KEY (`game_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `gm_media` (
                `id` int(250) NOT NULL AUTO_INCREMENT,
                `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
                `extension` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
                `type` varchar(250) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
                `url` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `gm_setting` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `site_name` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
                `site_url` varchar(500) CHARACTER SET utf8 NOT NULL,
                `site_theme` varchar(500) CHARACTER SET utf8 NOT NULL,
                `site_description` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Best Free Online Games',
                `site_keywords` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'games, online, arcade, html5, gamemonetize',
                `ads_status` enum('0','1') CHARACTER SET utf8 NOT NULL DEFAULT '0',
                `ad_time` int(11) NOT NULL DEFAULT '10',
                `language` varchar(250) CHARACTER SET utf8 NOT NULL,
                `featured_game_limit` int(11) NOT NULL,
                `mp_game_limit` int(11) NOT NULL,
                `xp_play` int(11) NOT NULL,
                `xp_report` int(11) NOT NULL,
                `xp_register` int(11) NOT NULL,
                `plays` int(255) NOT NULL,
                `custom_game_feed_url` VARCHAR(1000) DEFAULT NULL,
                `settings_1` varchar(500) NOT NULL,
                `settings_2` varchar(500) NOT NULL,
                `settings_3` varchar(500) NOT NULL,
                `settings_4` varchar(500) NOT NULL,
                `settings_5` varchar(500) NOT NULL,
                `settings_6` varchar(500) NOT NULL,
                `settings_7` varchar(500) NOT NULL,
                `settings_8` varchar(500) NOT NULL,
                `settings_9` varchar(500) NOT NULL,
                `settings_10` varchar(500) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `gm_setting` (`id`, `site_name`, `site_url`, `site_theme`, `ad_time`, `language`, `featured_game_limit`, `mp_game_limit`, `xp_play`, `xp_report`, `xp_register`, `custom_game_feed_url`) VALUES (1, '{$site_title}', '{$site_url}', 'kizi', 10, 'english', 8, 12, 50, 100, 10, '{$default_link}');

CREATE TABLE IF NOT EXISTS `gm_theme` (
                `theme_id` int(11) NOT NULL AUTO_INCREMENT,
                `theme_class` varchar(250) CHARACTER SET utf8 NOT NULL,
                PRIMARY KEY (`theme_id`), 
                UNIQUE KEY `theme_class` (`theme_class`), 
                UNIQUE KEY `theme_class_3` (`theme_class`), 
                KEY `theme_class_2` (`theme_class`)
            ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `gm_theme` (`theme_id`, `theme_class`) VALUES (1, 'style-1'), (2, 'style-1-image'), (3, 'style-2'), (4, 'style-2-image'), (5, 'style-3'), (6, 'style-3-image'), (7, 'style-4'), (8, 'style-5'), (9, 'style-6'), (10, 'style-7');

CREATE TABLE IF NOT EXISTS `gm_tags` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `url` varchar(100) NOT NULL,
        `name` varchar(100) NOT NULL,
        `footer_description` text DEFAULT '',
        PRIMARY KEY (`id`),
        UNIQUE KEY `url` (`url`)
       ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gm_footer_description` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `page_name` varchar(100) NOT NULL,
        `page_url` varchar(100) NOT NULL,
        `description` text NOT NULL,
        `has_content` enum('1','0') NOT NULL DEFAULT '0',
        `content_value` text DEFAULT '',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

INSERT INTO `gm_footer_description` (`page_name`, `page_url`, `description`, `has_content`, `content_value`) VALUES
    ('home', '/', '', '0', ''),
    ('new games', '/new-games', '', '0', ''),
    ('best games', '/best-games', '', '0', ''),
    ('featured games', '/featured-games', '', '0', ''),
    ('played games', '/played-games', '', '0', ''),
    ('about', '/about', '', '0', ''),
    ('contact', '/contact', '', '0', ''),
    ('privacy', '/privacy', '', '0', ''),
    ('terms', '/terms', '', '0', ''),
    ('blogs', '/blogs', '', '0', ''),
    ('categories', '/categories', '', '0', ''),
    ('search', '/search', '', '0', '');;

CREATE TABLE IF NOT EXISTS `gm_blogs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(100) NOT NULL,
    `url` varchar(100) NOT NULL,
    `image_url` varchar(200) DEFAULT NULL,
    `post` text DEFAULT NULL,
    `date_created` date DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;

CREATE TABLE IF NOT EXISTS `gm_chatgpt` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `api_key` varchar(200) DEFAULT NULL,
    `template_game` text DEFAULT NULL,
    `template_category` text DEFAULT NULL,
    `template_tags` text DEFAULT NULL,
    `template_footer` text DEFAULT NULL,
    `random_words_before_tags` varchar(1000) DEFAULT NULL,
    `random_words_after_tags` varchar(1000) DEFAULT NULL,
    `chatgpt_model` varchar(100) NOT NULL DEFAULT 'gpt-3.5-turbo',
    `maximum_words` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gm_chatgpt` (`id`, `api_key`, `template_game`, `template_category`, `template_tags`, `template_footer`, `random_words_before_tags`, `random_words_after_tags`, `chatgpt_model`, `maximum_words`) VALUES
(1, '  API chat gpt', 'test', 'test', 'test', '', 'test', ' test', 'gpt-4o-mini', 9000);

CREATE TABLE IF NOT EXISTS `gm_links` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `url` varchar(100) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 0,
    `is_chatgpt` tinyint(1) NOT NULL,
    `language_list` varchar(100) DEFAULT 'de,es,fr,it',
    `rewrite_method` varchar(100) DEFAULT NULL,
    `google_translate_language` varchar(100) DEFAULT NULL,
    `last_id` int(11) DEFAULT NULL,
    `autopost_fail_count` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gm_links` (`id`, `name`, `url`, `is_active`, `is_chatgpt`, `language_list`, `rewrite_method`, `google_translate_language`, `last_id`, `autopost_fail_count`) VALUES
(1, 'autopost', 'eJqmslXERPi0dpA649Qky2ujN8WZCxHbotY1wfv5', 0, 0, 'de,es,fr,it', 'chatgpt', 'es', NULL, 0),
(3, 'autopost_old_games', 'HLdsx7baSp2WlByVRf61CIZwmG9KOvA5toDceYNU', 0, 0, 'de,es,fr,it', NULL, NULL, 119, 0),
(4, 'autopost_tags', 'ozCgqUuKfRYkILGHZjE0hAyevMQlN8P13O62rVsS', 0, 0, 'de,es,fr,it', NULL, NULL, 387, 0);

CREATE TABLE IF NOT EXISTS `gm_sliders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `type` varchar(100) NOT NULL,
    `category_tags_id` int(11) NOT NULL,
    `ordering` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gm_sliders` (`id`, `type`, `category_tags_id`, `ordering`) VALUES
(3, 'best', 0, 40),
(18, 'category', 2, 80),
(24, 'category', 4, 70);

CREATE TABLE IF NOT EXISTS `gm_sidebar` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `type` varchar(100) NOT NULL,
    `category_tags_id` int(11) DEFAULT NULL,
    `custom_link` varchar(100) DEFAULT NULL,
    `icon` varchar(100) DEFAULT NULL,
    `ordering` varchar(3) NOT NULL DEFAULT '999',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gm_sidebar` (`id`, `name`, `type`, `category_tags_id`, `custom_link`, `icon`, `ordering`) VALUES
(2, 'New Games', 'new', 0, '', 'new_releases', '20'),
(3, 'Popular Games', 'best', 0, '', 'fa-solid fa-ranking-star', '30'),
(4, 'Played Games', 'separator', 0, '', '', '36'),
(5, 'Featured', 'featured', 0, '', 'auto_graph', '35'),
(6, 'Separator', 'separator', 0, '', '', '70'),
(7, 'Racing', 'category', 2, '', 'fa-solid fa-flag-checkered', '40'),
(8, 'Action', 'category', 1, '', 'local_fire_department', '43'),
(9, 'Dress Up', 'tags', 3, '', 'fa-solid fa-person-pregnant', '47');

ALTER TABLE `gm_setting`
    ADD `recaptcha_site_key` VARCHAR(100) NULL AFTER `settings_10`, 
    ADD `recaptcha_secret_key` VARCHAR(100) NULL AFTER `recaptcha_site_key`,
    ADD `is_sidebar_enabled` BOOLEAN NOT NULL DEFAULT FALSE AFTER `recaptcha_secret_key`;

ALTER TABLE `gm_games` 
    ADD COLUMN `is_last_rewrite` TINYINT(1) NOT NULL DEFAULT '0' AFTER `video_url`;

ALTER TABLE `gm_tags` 
    ADD COLUMN `is_last_rewrite` TINYINT(1) NOT NULL DEFAULT '0',
    ADD COLUMN `is_rewrited` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_last_rewrite`;
