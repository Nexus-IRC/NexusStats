CREATE TABLE IF NOT EXISTS `Channel` (
  `ID` int(255) NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL,
  `Lang` varchar(255) NOT NULL,
  `Noreg` int(255) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;


INSERT INTO `Channel` (`ID`, `Name`, `Lang`, `Noreg`) VALUES
(1, '#Nexus', 'EN', 0);
