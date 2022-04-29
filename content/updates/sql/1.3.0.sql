CREATE TABLE IF NOT EXISTS `rro_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `summ` VARCHAR(45) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `fiscal_engine_id` int(11) NOT NULL,
  `date_create` datetime NOT NULL,
  `retries` tinyint(2) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `operation_id` varchar(255) NOT NULL,
  `receipt_id` varchar(255) NOT NULL,
  `receipt_url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`payment_id`),
  UNIQUE KEY (`operation_id`),
  KEY (`login`),
  KEY (`agent_id`),
  KEY (`fiscal_engine_id`),
  KEY (`date_create`),
  KEY (`retries`),
  KEY (`status`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;