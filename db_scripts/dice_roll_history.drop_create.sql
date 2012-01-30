DROP TABLE IF EXISTS `dice_roll_history`;

CREATE TABLE `dice_roll_history` (
  `dice_roll_history_id` int(11) NOT NULL AUTO_INCREMENT,
  `character_name` varchar(35) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `reroll_option` int(2) DEFAULT NULL,
  `number_of_dice` int(2) DEFAULT NULL,
  `ones_cancel` tinyint(1) DEFAULT NULL,
  `chance_die` tinyint(1) DEFAULT NULL,
  `roll_date_time` datetime DEFAULT NULL,
  `number_successes` int(11) DEFAULT NULL,
  `result` longtext,
  `is_rote` tinyint(1) DEFAULT NULL,
  `is_willpower` tinyint(1) DEFAULT NULL,
  `is_init` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`dice_roll_history_id`)
) ENGINE=InnoDB AUTO_INCREMENT=280 DEFAULT CHARSET=latin1;