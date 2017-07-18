CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%assets_sets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `files` text NOT NULL,
  `media_query` varchar(255) NOT NULL DEFAULT '',
  `settings` text NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;