CREATE TABLE IF NOT EXISTS `{PREFIX}campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` smallint(5) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `thumbnail` varchar(255) CHARACTER SET utf16 COLLATE utf16_unicode_ci NOT NULL,
  `created` int(11) NOT NULL,
  `target_amount` decimal(16,2) NOT NULL,
  `raised_amount` decimal(16,2) NOT NULL DEFAULT '0',
  `expire` datetime NOT NULL,
  `predefined` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `paypal` tinyint(1) NOT NULL,
  `paypal_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `bank` tinyint(1) NOT NULL,
  `bank_info` text COLLATE utf8_unicode_ci NOT NULL,
  `term` text COLLATE utf8_unicode_ci NOT NULL,
  `comment_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `dislike_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `share_count` int(11) DEFAULT '0',
  `like_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT INTO `{PREFIX}pages` (`title`, `alias`, `content`, `permission`, `params`, `created`, `modified`, `menu`, `icon_class`, `weight`, `url`, `uri`, `description`, `keywords`, `custom`, `fragment`, `layout`, `levels`, `provides`, `view_count`, `type`, `search`, `theme_id`, `core_content_count`) VALUES
('Fundraising Browse Page', 'fundraising', '', '', '', '2018-01-01 00:00:00', '2018-01-01 00:00:00', 0, '', 0, '/fundraisings/index', 'fundraisings.index', '', '', 1, 0, 1, NULL, NULL, 0, 'plugin', 0, 0, 1);

INSERT INTO `{PREFIX}acos` (`group`, `key`, `description`) VALUES
('fundraising', 'create', 'Create/Edit Fundraising'),
('fundraising', 'view', 'View Fundraising');