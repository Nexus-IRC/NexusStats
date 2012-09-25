#!/usr/bin/php
<?php
/* install.php - NexusStats v2.3
 * Copyright (C) 2012 #Nexus project
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License 
 * along with this program. If not, see <http://www.gnu.org/licenses/>. 
 */
include("config.inc.php");
if($install ==true){
	$data = array();

	$fp = fopen("php://stdin", "r");
	if(!$fp) {
		die("ERROR: can not open stdin for reading.\n");
	}


	echo"NexusStats installier\n";
	echo"\n";

	echo "DATABASE INFORMATION\n";
	echo "Please enter your database login data.\n";

	echo "Hostname [localhost]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "localhost";
	$data['host'] = $line;

	echo "Username []: ";

	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	$data['user'] = $line;

	echo "Password []: ";

	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	$data['pass'] = $line;

	echo "Database []: ";

	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	$data['base'] = $line;
	echo"\n\n";

	echo "Checking MySQL... ";
	$data['conn'] = @mysql_connect($data['host'], $data['user'], $data['pass']);
	@mysql_select_db($data['base'], $data['conn']) OR $data['conn'] = NULL;
	if($data['conn']) {
		echo"ok\n";
	} else {
		echo"fail\n";
		die();
	}

	echo "IRC Server [irc.onlinegamesnet.net]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "irc.onlinegamesnet.net";
	$data['irc_server'] = $line;

	echo "IRC port [6667]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "6667";
	$data['irc_port'] = $line;

	echo "IRC nick [NexusStats]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "NexusStats";
	$data['irc_nick'] = $line;

	echo "IRC passwort []: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	$data['irc_pass'] = $line;

	echo "Network [OnlineGamesNet]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "OnlineGamesNet";
	$data['network'] = $line;

	echo "Admin [Stricted2]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "Stricted2";
	$data['admin'] = $line;

	echo "Logdir [/home/stats/pisg-0.73/log/]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "/home/stats/pisg-0.73/log/";
	$data['logdir'] = $line;

	echo "Configdir [/home/stats/pisg-0.73/cfg/]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "/home/stats/pisg-0.73/cfg/";
	$data['cfgdir'] = $line;

	echo "Satistik output dir [/var/customers/webs/nexus/stats/chan/]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "/var/customers/webs/nexus/stats/chan/";
	$data['statsdir'] = $line;

	echo "Satistik output archiv dir [/var/customers/webs/nexus/stats/archiv/]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "/var/customers/webs/nexus/stats/archiv/";
	$data['archivdir'] = $line;

	echo "pisg dir [/home/stats/pisg-0.73/]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "/home/stats/pisg-0.73/";
	$data['pisgdir'] = $line;

	echo "url [http://stats.nexus-irc.de/?c=]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "http://stats.nexus-irc.de/?c=";
	$data['url'] = $line;

	echo "archiv url [http://stats.nexus-irc.de/?ac=]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "http://stats.nexus-irc.de/?ac=";
	$data['aurl'] = $line;

	echo "default language [EN]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "EN";
	$data['defaultlang'] = $line;

	echo "Bot trigger [~]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "~";
	$data['trigger'] = $line;

	echo "debug channel [#Nexus-debug]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "#Nexus-debug";
	$data['debugchannel'] = $line;


	echo "debug log file [/home/stats/debug.log]: ";
	$line = fgets($fp, 4096);
	$line = str_replace(array("\n", "\r"),array("", ""),$line);
	if($line == "") $line = "/home/stats/debug.log";
	$data['debuglog'] = $line;


	$maincode=file_get_contents("config.inc.php");
	$maincode = str_replace('$server="";', '$server="'.$data['irc_server'].'";', $maincode);
	$maincode = str_replace('$port="";', '$port="'.$data['irc_port'].'";', $maincode);
	$maincode = str_replace('$botnick="";', '$botnick="'.$data['irc_nick'].'";', $maincode);
	$maincode = str_replace('$pass="";', '$pass="'.$data['irc_pass'].'";', $maincode);
	$maincode = str_replace('$network="";', '$network="'.$data['network'].'";', $maincode);
	$maincode = str_replace('$admin="";', '$admin="'.$data['admin'].'";', $maincode);
	$maincode = str_replace('$logdir="";', '$logdir="'.$data['logdir'].'";', $maincode);
	$maincode = str_replace('$cfgdir="";', '$cfgdir="'.$data['cfgdir'].'";', $maincode);
	$maincode = str_replace('$statsdir="";', '$statsdir="'.$data['statsdir'].'";', $maincode);
	$maincode = str_replace('$archivdir="";', '$archivdir="'.$data['archivdir'].'";', $maincode);
	$maincode = str_replace('$pisgdir="";', '$pisgdir="'.$data['pisgdir'].'";', $maincode);
	$maincode = str_replace('$url="";', '$url="'.$data['url'].'";', $maincode);
	$maincode = str_replace('$aurl="";', '$aurl="'.$data['aurl'].'";', $maincode);
	$maincode = str_replace('$defaultlang="";', '$defaultlang="'.$data['defaultlang'].'";', $maincode);
	$maincode = str_replace('$trigger="";', '$trigger="'.$data['trigger'].'";', $maincode);
	$maincode = str_replace('$debugchannel="";', '$debugchannel="'.$data['debugchannel'].'";', $maincode);
	$maincode = str_replace('$debuglog="";', '$debuglog="'.$data['debuglog'].'";', $maincode);
	$maincode = str_replace('$mysql_host="";', '$mysql_host="'.$data['host'].'";', $maincode);
	$maincode = str_replace('$mysql_user="";', '$mysql_user="'.$data['user'].'";', $maincode);
	$maincode = str_replace('$mysql_pw="";', '$mysql_pw="'.$data['pass'].'";', $maincode);
	$maincode = str_replace('$mysql_db="";', '$mysql_db="'.$data['base'].'";', $maincode);
	$maincode = str_replace('$install=true;', '$install=false;', $maincode);
	$maincode = str_replace('$version="";', '$version="2.3";', $maincode);
	$fp = fopen("config.inc.php", 'w');
	fwrite($fp, $maincode);
	fclose($fp);
	unset($maincode);
	
	$link = @mysql_connect($data['host'], $data['user'], $data['pass']);
	$db = @mysql_select_db($data['base'], $data['conn']);
	mysql_query("CREATE TABLE IF NOT EXISTS `Channel` (
  `ID` int(255) NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL,
  `Lang` varchar(255) NOT NULL,
  `Noreg` tinyint(1) NOT NULL,
  `Nostats` tinyint(1) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
");
	mysql_query("INSERT INTO `Channel` (`ID`, `Name`, `Lang`, `Noreg`, `Nostats`) VALUES
(1, '#Nexus', 'EN', 0, 0);");
	
	$git_revision = shell_exec("git rev-list -n 1 --pretty='format:%h' --header master | grep '^[0-9a-f]*$'");
	$git_commitcount= shell_exec('git rev-list --oneline --header master | wc -l | sed "s/[ \t]//g"');
	$codelines= shell_exec("find . -type f -regex '\./.*\.php' |xargs cat|wc -l");
	$creation=shell_exec("date | \
awk '{if (NF == 6) \
	 { print $1 \" \" $2 \" \" $3 \" \" $6 \" at \" $4 \" \" $5 } \
else \
	 { print $1 \" \" $2 \" \" $3 \" \" $7 \" at \" $4 \" \" $5 \" \" $6 }}'");
	if(isset($git_revision) AND isset($git_commitcount)){
		$maincode=file_get_contents("config.inc.php");
		$maincode = str_replace('$gitversion="";', '$gitversion="git-'.substr($git_commitcount, 0, -1).'-'.$git_revision.'";', $maincode);
		$maincode = str_replace('$codelines="";', '$codelines="'.substr($codelines, 0, -1).'";', $maincode);
		$maincode = str_replace('$creation="";', '$creation="'.substr($creation, 0, -1).'";', $maincode);
		$fp = fopen("config.inc.php", 'w');
		fwrite($fp, $maincode);
		fclose($fp);
	}else{
		$maincode=file_get_contents("config.inc.php");
		$maincode = str_replace('$codelines="";', '$codelines="'.substr($codelines, 0, -1).'";', $maincode);
		$maincode = str_replace('$creation="";', '$creation="'.substr($creation, 0, -1).'";', $maincode);
		$fp = fopen("config.inc.php", 'w');
		fwrite($fp, $maincode);
		fclose($fp);
	}

	echo"\n";
	echo"finished.";
	echo"\n\n";
}
?>