CREATE TABLE `wp_MYNAME_belegungsplan` (
  `id` int(11) NOT NULL auto_increment,
  `published` int(11) NOT NULL default '0',
  `private` int(11) NOT NULL default '1',
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(125) NOT NULL,
  `email` varchar(255) NOT NULL,
  `allday` int(11) NOT NULL default '1',
  `start` int(11) NOT NULL,
  `end` int(11) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;