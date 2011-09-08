DROP TABLE IF EXISTS `fb_url`;
CREATE TABLE `fb_url` (
  `url` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY  (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `fb_response`;
CREATE TABLE `fb_response` (
  `url` varchar(200) NOT NULL DEFAULT '',
  `response_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_text` text DEFAULT NULL,
  `got_the_point` tinyint(1) DEFAULT NULL,
  PRIMARY KEY  (`url`,`response_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `fb_question`;
CREATE TABLE `fb_question` (
  `question_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_text` varchar(200) DEFAULT NULL,
  PRIMARY KEY  (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `fb_response_to_question`;
CREATE TABLE `fb_response_to_question` (
  `url` varchar(200) NOT NULL DEFAULT '',
  `response_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `question_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `response_text` text DEFAULT NULL,
  `is_affirmative` tinyint(1) DEFAULT NULL,
  `sentiment` enum('NEGATIVE','NEUTRAL','POSITIVE') DEFAULT NULL,
  PRIMARY KEY  (`url`,`response_id`,`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
