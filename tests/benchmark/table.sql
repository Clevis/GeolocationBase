
CREATE TABLE `locations` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `lat` float NOT NULL,
  `lon` float NOT NULL,
  `latrad` float NOT NULL,
  `lonrad` float NOT NULL,
  `x` float NOT NULL,
  `y` float NOT NULL,
  `z` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lat_lon` (`lat`,`lon`),
  KEY `latrad_lonrad` (`latrad`,`lonrad`),
  KEY `x_y_z` (`x`,`y`,`z`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
