CREATE TABLE IF NOT EXISTS `b_lm_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `article` varchar(17) NOT NULL,
  `original_article` varchar(17) NOT NULL,
  `brand_title` varchar(100) NOT NULL,
  `price` float(8,2) DEFAULT NULL,
  `quantity` float(8,2) DEFAULT NULL,
  `group_id` varchar(50) DEFAULT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `supplier_id` varchar(100) NOT NULL DEFAULT '0',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article` (`article`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `b_lm_wordforms` (
  `brand_title` varchar(255) NOT NULL,
  `group` varchar(100) NOT NULL,
  UNIQUE KEY `brand_title` (`brand_title`),
  KEY `group` (`group`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `b_lm_api_modifications` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `set_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
 `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
 `source_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 `parent_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
 `data` text COLLATE utf8_unicode_ci NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `unique_modification` (`set_id`,`type`,`source_id`,`parent_id`),
 KEY `set_id` (`set_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `b_lm_notepad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `article` varchar(50) NOT NULL,
  `brand_title` varchar(100) DEFAULT NULL,
  `auto` varchar(255) DEFAULT NULL,
  `auto_id` int(11) DEFAULT NULL,
  `quantity` int(4) DEFAULT NULL,
  `notes` text,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

