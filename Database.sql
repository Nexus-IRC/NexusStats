CREATE TABLE IF NOT EXISTS `Channel` (
  `ID` int(255) NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL,
  `Lang` varchar(255) NOT NULL,
  `Noreg` tinyint(1) NOT NULL,
  `Nostats` tinyint(1) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;


INSERT INTO `Channel` (`ID`, `Name`, `Lang`, `Noreg`,`Nostats`) VALUES
(1, '#Nexus', 'EN', 0, 0);
