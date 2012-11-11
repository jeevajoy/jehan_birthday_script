 CREATE TABLE IF NOT EXISTS `mtl_civicrm_brithday_sent` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
`contact_id` int(10) unsigned NOT NULL COMMENT 'Contact Id',
`is_sent` int(10) unsigned NOT NULL COMMENT 'Is the Email has been sent?',
`date_sent` varchar(255) ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
