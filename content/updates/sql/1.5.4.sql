CREATE TABLE IF NOT EXISTS `taxsup` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(32) NOT NULL,
  `fee` DOUBLE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
