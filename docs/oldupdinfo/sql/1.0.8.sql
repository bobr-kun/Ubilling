ALTER TABLE `banksta2_presets` ADD `payment_type_id` int(11) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS `pt_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(64) NOT NULL,
  `day` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `pt_tariffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tariff` varchar(40) NOT NULL,
  `fee` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `pt_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `login` varchar(64) NOT NULL,
  `tariff` varchar(40) NOT NULL,
  `day` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `ponifdesc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oltid` int(11) NOT NULL,
  `iface` varchar(64) DEFAULT NULL,
  `desc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY (`oltid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `ponboxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `geo` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `ponboxeslinks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boxid` int(11) NOT NULL,
  `login` varchar(64) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `onuid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;