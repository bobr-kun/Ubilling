CREATE TABLE IF NOT EXISTS `mg_credentials` (
 `id` INT(11) NOT NULL AUTO_INCREMENT,
 `isdn` VARCHAR(255) NOT NULL,
 `login` VARCHAR(255) NOT NULL,
 `email`  VARCHAR(255) NOT NULL,
 `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;