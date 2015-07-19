
DROP TABLE IF EXISTS `acl`;
CREATE TABLE `acl` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(127) NOT NULL,
  `section` varchar(63) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

LOCK TABLES `acl` WRITE;
INSERT INTO `acl` (`id`, `name`, `section`) VALUES 
(1,'task_see_all','general'),
(2,'admin_user','general'),
(3,'create_memo','general'),
(4,'create_project','general'),
(5,'task_edit_all','general'),
(6,'invoicing','general'),
(7,'view_user','general'),
(8,'create_user','general'),
(9,'view_report','general');
UNLOCK TABLES;

DROP TABLE IF EXISTS `acl_user`;
CREATE TABLE `acl_user` (
  `user_id` mediumint(8) unsigned NOT NULL,
  `acl_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`acl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `address` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

LOCK TABLES `company` WRITE;
INSERT INTO `company` (`id`, `name`, `address`) VALUES 
(1,'Team','');
UNLOCK TABLES;

DROP TABLE IF EXISTS `invoice`;
CREATE TABLE `invoice` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `invdate` date NOT NULL,
  `duedate` date NOT NULL,
  `paydate` date NOT NULL,
  `amount` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=678 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned NOT NULL,
  `class` enum('client','freelancer','staff','manager') NOT NULL,
  `nickname` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL,
  `salt` varchar(40) NOT NULL,
  `auto_login` tinyint(1) unsigned NOT NULL,
  `address` varchar(255) NOT NULL,
  `time_zone` varchar(63) NOT NULL,
  `language` enum('en','fr','de','no') NOT NULL DEFAULT 'en',
  `date_format_us` tinyint(1) NOT NULL DEFAULT '0',
  `creation_date` datetime NOT NULL,
  `expiration_date` date NOT NULL,
  `last_login_date` datetime NOT NULL,
  `last_login_address` varchar(60) NOT NULL,
  `last_change_date` datetime NOT NULL,
  `visits` int(10) unsigned NOT NULL,
  `bad_access` smallint(5) unsigned NOT NULL,
  `activation` varchar(16) NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL,
  `rate_translate` float NOT NULL,
  `rate_review` float NOT NULL,
  `rate_hourly` float NOT NULL,
  `payterms` smallint(5) unsigned NOT NULL,
  `hidden` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hidden` (`hidden`)
) ENGINE=MyISAM AUTO_INCREMENT=134 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `note` text NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '5',
  `begin` date NOT NULL,
  `deadline` date NOT NULL,
  `deadtime` varchar(5) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `archived` tinyint(3) unsigned NOT NULL,
  `member_id` mediumint(8) unsigned NOT NULL,
  `author_id` mediumint(8) unsigned NOT NULL,
  `public` tinyint(1) unsigned NOT NULL,
  `po` varchar(32) NOT NULL,
  `pid` varchar(16) DEFAULT NULL,
  `work` tinyint(3) unsigned NOT NULL,
  `words` float unsigned NOT NULL,
  `rate` decimal(8,3) unsigned NOT NULL,
  `context` tinyint(3) unsigned NOT NULL,
  `invoice_id` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `author_id` (`author_id`),
  KEY `pid` (`pid`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8294 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `timer`;
CREATE TABLE `timer` (
  `task_id` int(10) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `stop` datetime NOT NULL,
  `spent` int(10) unsigned NOT NULL,
  `manual` tinyint(3) unsigned NOT NULL,
  KEY `task_id` (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

