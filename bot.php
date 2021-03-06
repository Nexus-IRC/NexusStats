<?php
/* Bot.php - NexusStats v2.3
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
/* config start */
include("config.inc.php");
if($install ==true){
	echo"run install.php\n";
	exit(0);
}
if (function_exists('ini_get') && function_exists('ini_set')) {
	if(!isset(ini_get('date.timezone'))){
		ini_set('date.timezone', 'Europe/Berlin');
	}
}
/* config end */
echo("################################\n");
echo("#### Starting NexusStats        \n");
echo("#### version ".$version."       \n");
echo("#### coded by Stricted          \n");
echo("################################\n");
set_time_limit(0);
$socket = fsockopen($server,$port,$errstr,$errno,2);
$dltimer = array();
$timer = time();
$stime = time();
$fgr = "";
$channeluser=array();
$auth=array();
$glob=array();
$phpcache=array();
$ccache=array();
$connect = mysql_connect($mysql_host, $mysql_user, $mysql_pw);
$rawallow=array("NeonServ","Stricted2");
$db = mysql_select_db($mysql_db, $connect);
stream_set_blocking($socket,0);
putSocket("PASS ".$pass);
putSocket("NICK ".$botnick);
putSocket("USER ".$botnick." 0 * :".$botnick." (php".PHP_VERSION.")");

while (true) {
    if (feof($socket)) {
        $socket = fsockopen($server,$port,$errstr,$errno,2);
        $dltimer = array();
        $timer = time();
		$stime = time();
		$fgr = "";
        stream_set_blocking($socket,0);
        putSocket("PASS ".$pass);
		putSocket("NICK ".$botnick);
		putSocket("USER ".$botnick." 0 * :".$botnick." (php".PHP_VERSION.")");
    }    
    if (time() >= $timer + 1) {
        $timer = time();
        foreach ($dltimer as $thetime => $evntarray) {
            if ($thetime <= time()) {
                timer_evnts($thetime,1);
            }
        }
    }
	foreach($ccache as $id => $c) {
		if(!checkcstate($c)) {
			unlink("tmp/debug_".$c['id'].".c");
			unlink("tmp/debug_".$c['id']);
			unset($ccache[$id]);
		}
	}
	foreach($phpcache as $id => $php) {
		if(!checkphpstate($php)) {
			unset($phpcache[$id]);
		}
	}
    usleep(1000);
    while ($fg = fgets($socket)) {
		$glob['dat_in'] = $glob['dat_in'] + strlen($fg);
		$fg = utf8_decode(str_replace("\r","",str_replace("\n","",$fg)));
		$fgr = $fg;
        echo ("<<".$fg."\n");
        flush();
        $exp = explode(" ",$fg);
        $command = @substr($exp[3], 1);
        $expB = explode("!",$exp[0]);
        $nick = substr($expB[0],1);
		$host = @explode("@",$expB[1]);
        if ($exp[0] == "PING") {
            putSocket("PONG ".$exp[1]);
        }
		include("cmd.inc.php");
		
    }
}
function create_timer ($time, $line) {
	global $dltimer;
	$ttime = time() + str2time($time);
	$dlc = count($dltimer[$ttime]) + 1;
	$dltimer[$ttime][$dlc] = $line;
	send_debug("Timer startet");
}

function create_log ($channel, $data) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang;
	$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($channel)."' AND `Noreg` = '1'");
	$row = mysql_fetch_array($a);
	if($row['Name'] == $channel){
	}else{
		$b = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($channel)."' AND `Noreg` = '0'");
		$row2 = mysql_fetch_array($b);
		if($row2['Name'] == $channel){
			$cha = @substr($channel, 1);
			if (file_exists($logdir.$cha.".log")) {
				$cha = @substr($channel, 1);
				$inhalt = file_get_contents($logdir.$cha.".log");
				file_put_contents($logdir.$cha.".log", $inhalt .= $data."\n");	
			}else{
				$text1  = $data."\n";
				$dateiname = $logdir.$cha.".log"; 
				$handler = fOpen($dateiname , "a+");
				fWrite($handler , $text1);
				fClose($handler);
			}
		}
	}
}

function create_debug_log ($data) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang, $debuglog;
	$datei = $debuglog;
	if (file_exists($datei)) {
		$inhalt = file_get_contents($datei);
		file_put_contents($datei, $inhalt .= date("d.m.y")." ".date("H:i:s").": ".$data."\n");	
	}else{
		$text1  = date("d.m.y")." ".date("H:i:s").": ".$data."\n";
		$dateiname = $datei; 
		$handler = fOpen($dateiname , "a+");
		fWrite($handler , $text1);
		fClose($handler);
	}	
}
 
function create_noreg ($channel, $nick, $force = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl;
	$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($channel)."' AND `Noreg` = '0'");
	$row = mysql_fetch_array($a);
	if($row['Name'] == $channel){
		if(isset($force)){ //optional
			del_chan($channel);
			putSocket("PART ".$channel." :Unregistered by ".$nick.".");
		}else{
			del_chan($channel, true);
			putSocket("PART ".$channel." :Unregistered by ".$nick.".");
			send_debug("Add ".$channel." to the no register list");
		}
	}
}

function create_chan ($channel, $force=null) {
	if(isset($force)){ //optional
		global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang;
		$cha = @substr($channel, 1);
		mysql_send_query("DELETE FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($channel)."' AND `Noreg` = '1'");
		mysql_send_query("INSERT INTO `Channel` (`ID` ,`Name` ,`Lang` ,`Noreg`, `Nostats`) VALUES (NULL , '".mysql_real_escape_string($channel)."', '".mysql_real_escape_string($defaultlang)."', '0', '0');");
		putSocket("join ".$channel);
		create_conf($channel);
		send_debug("Add channel ".$channel);
	}else{
		global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang;
		$cha = @substr($channel, 1);
		mysql_send_query("INSERT INTO `Channel` (`ID` ,`Name` ,`Lang` ,`Noreg`, `Nostats`) VALUES (NULL , '".mysql_real_escape_string($channel)."', '".mysql_real_escape_string($defaultlang)."', '0', '0');");
		putSocket("join ".$channel);
		create_conf($channel);
		send_debug("Add channel ".$channel);
	}
}

function set_nostats ($channel) {
	$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($channel)."' AND `Nostats` = '0'");
	$row = mysql_fetch_array($a);
	if($row['Name'] == $channel){
		mysql_send_query("UPDATE `Channel` SET `Nostats` = '1' WHERE `Name` = '".mysql_real_escape_string($channel)."'");
		send_debug("Add channel ".$channel." to nostats list");
	}
}

function set_lang ($chan, $lang = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang;
	$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($chan)."' AND `Noreg` = '0'");
	$row = mysql_fetch_array($a);
	if($row['Name'] == $chan){
		if(isset($lang)){ //optional
			$cha = @substr($chan, 1);
			if (file_exists($cfgdir.$cha.".cfg")) {
				create_conf($chan, $lang);
				mysql_send_query("UPDATE `Channel` SET `Lang` = '".mysql_real_escape_string($lang)."' WHERE `Name` = '".mysql_real_escape_string($chan)."'");
				@unlink($statsdir.$cha.".php");
				create_stats($chan);
				send_debug("Language for channel ".$chan." changed to ".$lang);
			}
		}else{
			$cha = @substr($chan, 1);
			if (file_exists($cfgdir.$cha.".cfg")) {
				create_conf ($chan);
				mysql_send_query("UPDATE `Channel` SET `Lang` = '".mysql_real_escape_string($defaultlang)."' WHERE `Name` = '".mysql_real_escape_string($chan)."'");
				@unlink($statsdir.$cha.".php");
				create_stats($chan);
				send_debug("Language for channel ".$chan." changed to ".$defaultlang);
			}
		}
	}
}

function create_conf ($channel = null, $lang = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang, $botnick, $network;
	if(isset($channel)){//optional
		$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($channel)."' AND `Noreg` = '0'");
		$row = mysql_fetch_array($a);
		if($row['Name'] == $channel){
			if(isset($lang)){//optional
				$cha = @substr($channel, 1);
				@unlink($cfgdir.$cha.".cfg");
				$text1  = "<channel='".$channel."'>\n";
				$text2  = "Logfile = '".$logdir.$cha.".log'\n";
				$text3  = "ColorScheme = 'default'\n";
				$text4  = "Format = 'mIRC'\n";
				$text5  = "Lang = '".$lang."'\n";
				$text6  = "Network= '".$network."'\n";
				$text7  = "Maintainer = '".$botnick."'\n";
				$text8  = "OutputFile = '".$statsdir.$cha.".php'\n";
				$text9  = "NickTracking='1'\n";
				$text10 = "ActiveNicks2='50'\n";
				$text11 = "ShowSmileys='1'\n";
				$text12 = "ShowWpl='1'\n";
				$text13 = "ShowLegend='1'\n";
				$text14 = "ShowMostNicks='1'\n";
				$text15 = "ShowActiveGenders='1'\n";
				$text16 = "</channel>\n";
				$dateiname = $cfgdir.$cha.".cfg"; 
				$handler = fOpen($dateiname , "a+");
				fWrite($handler , $text1);
				fWrite($handler , $text2);
				fWrite($handler , $text3);
				fWrite($handler , $text4);
				fWrite($handler , $text5);
				fWrite($handler , $text6);
				fWrite($handler , $text7);
				fWrite($handler , $text8);
				fWrite($handler , $text9);
				fWrite($handler , $text10);
				fWrite($handler , $text11);
				fWrite($handler , $text12);
				fWrite($handler , $text13);
				fWrite($handler , $text14);
				fWrite($handler , $text15);
				fWrite($handler , $text16);
				fClose($handler);
				send_debug("Config for channel ".$channel." created");
			}else{
				$cha = @substr($channel, 1);
				@unlink($cfgdir.$cha.".cfg");
				$text1  = "<channel='".$channel."'>\n";
				$text2  = "Logfile = '".$logdir.$cha.".log'\n";
				$text3  = "ColorScheme = 'default'\n";
				$text4  = "Format = 'mIRC'\n";
				$text5  = "Lang = 'EN'\n";
				$text6  = "Network= '".$network."'\n";
				$text7  = "Maintainer = '".$botnick."'\n";
				$text8  = "OutputFile = '".$statsdir.$cha.".php'\n";
				$text9  = "NickTracking='1'\n";
				$text10 = "ActiveNicks2='50'\n";
				$text11 = "ShowSmileys='1'\n";
				$text12 = "ShowWpl='1'\n";
				$text13 = "ShowLegend='1'\n";
				$text14 = "ShowMostNicks='1'\n";
				$text15 = "ShowActiveGenders='1'\n";
				$text16 = "</channel>\n";
				$dateiname = $cfgdir.$cha.".cfg"; 
				$handler = fOpen($dateiname , "a+");
				fWrite($handler , $text1);
				fWrite($handler , $text2);
				fWrite($handler , $text3);
				fWrite($handler , $text4);
				fWrite($handler , $text5);
				fWrite($handler , $text6);
				fWrite($handler , $text7);
				fWrite($handler , $text8);
				fWrite($handler , $text9);
				fWrite($handler , $text10);
				fWrite($handler , $text11);
				fWrite($handler , $text12);
				fWrite($handler , $text13);
				fWrite($handler , $text14);
				fWrite($handler , $text15);
				fWrite($handler , $text16);
				fClose($handler);
				send_debug("Config for channel ".$channel." created");
			}
		}
	}else{
		$result = mysql_send_query("SELECT * FROM `Channel` WHERE `Noreg` = '0'");
		while ( $row = mysql_fetch_array($result) ){
			@unlink($cfgdir.substr($row['Name'], 1).".cfg");
			$text1  = "<channel='#".substr($row['Name'], 1)."'>\n";
			$text2  = "Logfile = '".$logdir.substr($row['Name'], 1).".log'\n";
			$text3  = "ColorScheme = 'default'\n";
			$text4  = "Format = 'mIRC'\n";
			$text5  = "Lang = '".$row['Lang']."'\n";
			$text6  = "Network= '".$network."'\n";
			$text7  = "Maintainer = '".$botnick."'\n";
			$text8  = "OutputFile = '".$statsdir.substr($row['Name'], 1).".php'\n";
			$text9  = "NickTracking='1'\n";
			$text10 = "ActiveNicks2='50'\n";
			$text11 = "ShowSmileys='1'\n";
			$text12 = "ShowWpl='1'\n";
			$text13 = "ShowLegend='1'\n";
			$text14 = "ShowMostNicks='1'\n";
			$text15 = "ShowActiveGenders='1'\n";
			$text16 = "</channel>\n";
			$dateiname = $cfgdir.substr($row['Name'], 1).".cfg"; 
			$handler = fOpen($dateiname , "a+");
			fWrite($handler , $text1);
			fWrite($handler , $text2);
			fWrite($handler , $text3);
			fWrite($handler , $text4);
			fWrite($handler , $text5);
			fWrite($handler , $text6);
			fWrite($handler , $text7);
			fWrite($handler , $text8);
			fWrite($handler , $text9);
			fWrite($handler , $text10);
			fWrite($handler , $text11);
			fWrite($handler , $text12);
			fWrite($handler , $text13);
			fWrite($handler , $text14);
			fWrite($handler , $text15);
			fWrite($handler , $text16);
			fClose($handler);
		}
		send_debug("Config for all channels created");
	}
}

function del_chan ($channel, $noreg=null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang, $channeluser;
	$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($channel)."'");
	$row = mysql_fetch_array($a);
	if($row['Name'] == $channel){
		$cha = @substr($channel, 1);
		@unlink($cfgdir.$cha.".cfg");
		@unlink($logdir.$cha.".log");
		@unlink($statsdir.$cha.".php");
		if(isset($noreg)){ //optional
			mysql_send_query("UPDATE `Channel` SET `Noreg` = '1' WHERE `Name` = '".mysql_real_escape_string($channel)."'");
		}else{
			mysql_send_query("DELETE FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($channel)."'");
		}
		putSocket("part ".$channel);
		send_debug("Delete channel ".$channel);
		unset($channeluser[$channel]);
	}
}

function check_stats ($chan = null) {
	$reset = array("01.01","01.02","01.03","01.04","01.05","01.06","01.07","01.08","01.09","01.10","01.11","01.12");
	$stamp = time();
	if(isset($chan)){ //optional
		if(in_array(date("d.m",$stamp), $reset)){
			reset_stats($chan);
		}else{
			create_stats($chan);
		}
	}else{
		if(in_array(date("d.m",$stamp), $reset)){
			reset_stats();
		}else{
			create_stats();
		}
	}
}

function create_stats ($chan = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang;
	if(isset($chan)){ //optional
		$a = mysql_send_query("SELECT * FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($chan)."' AND `Noreg` = '0'");
		$row = mysql_fetch_array($a);
		if($row['Name'] == $chan){
			$cha = @substr($chan, 1);
			@unlink($statsdir.$cha.".php");
			shell_exec($pisgdir."pisg --configfile=".$cfgdir.$cha.".cfg");
			if($row['Nostats'] == "0") {
				privmsg($chan,"Stats Update: ".$url.$cha);
			}
			send_debug("Stats created ".$chan);
		}
	}else{
		$result = mysql_send_query("SELECT * FROM `Channel` WHERE `Noreg` = '0'");
		while ( $row = mysql_fetch_array($result) ){
			shell_exec($pisgdir."pisg --configfile=".$cfgdir.substr($row['Name'], 1).".cfg\n");
			if($row['Nostats'] == "1") { } else {
				privmsg($row['Name'],"Stats Update: ".$url.substr($row['Name'], 1));
			}
		}
		send_debug("Stats created");
		create_timer("12h","stats");
	}
}

function reset_stats ($chan = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang, $botnick;
	if(isset($chan)){ //optional
		$a = mysql_send_query("SELECT * FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($chan)."' AND `Noreg` = '0'");
		$row = mysql_fetch_array($a);
		if($row['Name'] == $chan){
			@unlink($logdir.substr($chan, 1).".log");
			@unlink($archivdir.substr($chan, 1).".php");
			mkdir($archivdir, 0755);
			copy($statsdir.substr($chan, 1).".php", $archivdir.substr($chan, 1).".php");
			@unlink($statsdir.substr($chan, 1).".php");
			if($row['Nostats'] == "0"){
				privmsg($chan,"Stats Reset, Archiv: ".$aurl.substr($chan, 1));
			}
			create_log($chan, "[".@date("H:i")."] <".$botnick."> send a log text to create logfiles");
			create_stats($chan);
			send_debug("Stats resetet ".$chan);
		}
	}else{
		$result = mysql_send_query("SELECT * FROM `Channel` WHERE `Noreg` = '0'");
		while ( $row = mysql_fetch_array($result) ){
			$a=explode("|",$element);
			@unlink($logdir.substr($row['Name'], 1).".log");
			@unlink($archivdir.substr($row['Name'], 1).".php");
			mkdir($archivdir, 0755);
			copy($statsdir.substr($row['Name'], 1).".php", $archivdir.substr($row['Name'], 1).".php");
			@unlink($statsdir.substr($row['Name'], 1).".php");
			if($row['Nostats'] == "0"){
				privmsg($row['Name'],"Stats Reset, Archiv: ".$aurl.substr($row['Name'], 1));
			}
			create_log($row['Name'], "[".@date("H:i")."] <".$botnick."> send a log text to create logfiles");
			create_stats($row['Name']);
		}
		send_debug("Stats resetet");
		create_timer("24h","stats");
	}
}


function debug ($chan, $data = null) {
	global $server, $port, $botnick, $pass, $admin, $logdir, $cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang, $trigger, $debugchannel, $debuglog, $version, $mysql_host, $mysql_user, $mysql_pw, $mysql_db, $gitversion, $createversion;
	if(isset($data)){ //optional
		ob_start();
		$ret = eval($data);
		$out = ob_get_contents();
		ob_end_clean();
		$lines = explode("\n",$out);
		for($i=0;$i<count($lines);$i++) {
			if($lines[$i]!="") {
				privmsg($chan,$lines[$i]);
			}
		}
		$lines = explode("\n",$ret);
		for($i=0;$i<count($lines);$i++) {
			if($lines[$i]!="") {
				privmsg($chan,$lines[$i]);
			}
		}
	}else{
		global $fgr;
		$fg = $fgr;
		$ex1 = explode(":",$fg,3);
		$ex2 = explode(" ",$ex1[2],2);
		if(isset($ex2[1])){
			ob_start();
			$ret = eval($ex2[1]);
			$out = ob_get_contents();
			ob_end_clean();
			$lines = explode("\n",$out);
			for($i=0;$i<count($lines);$i++) {
				if($lines[$i]!="") {
					privmsg($chan,$lines[$i]);
				}
			}
			$lines = explode("\n",$ret);
			for($i=0;$i<count($lines);$i++) {
				if($lines[$i]!="") {
					privmsg($chan,$lines[$i]);
				}
			}
		}
	}
}

function timer_evnts ($time, $call) {
	global $dltimer;
	foreach ($dltimer["$time"] as $timenum => $timeevnt) {
		$timtime = explode(" ",$timeevnt);
		if ($timtime[0] == "stats") {
			check_stats();
		}
		unset($dltimer["$time"][$timenum]);
	}
}

function privmsg ($target, $data) {
    putSocket("PRIVMSG ".$target." :".$data);
}

function notice ($target, $data) {
    putSocket("NOTICE ".$target." :".$data);
}

function who ($target, $args) {
	if ($target[0] == "#") {
		putSocket("WHO ".$target.",".$args." D%tnaf,".$args);
	}else{
		putSocket("WHO ".$target.",".$args." %tna,".$args);
	}
}

function getauth ($nick) {
	global $auth;
	return $auth[strtolower($nick)];
}

function str2time ($line) {
    $ttime = 0;
    $x = 0;
    $cache = "";
    while ($line[$x] != "") {
        if ($line[$x] == "1" or $line[$x] == "2" or $line[$x] == "3" or $line[$x] == "4" or $line[$x] == "5" or $line[$x] == "6" or $line[$x] == "7" or $line[$x] == "8" or $line[$x] == "9" or $line[$x] == "0") {
            $cache = $cache.$line[$x];
            $y = $x + 1;
            if ($line[$y] == "") {
                $ttime = $ttime + $cache;
                $cache = "";
            }
        }
        elseif ($line[$x] == "y") {
            $ttime = $ttime + $cache * 60 * 60 * 24 * 30 * 12;
            $cache = "";
        }
        elseif ($line[$x] == "M") {
            $ttime = $ttime + $cache * 60 * 60 * 24 * 30;
            $cache = "";
        }
        elseif ($line[$x] == "w") {
            $ttime = $ttime + $cache * 60 * 60 * 24 * 7;
            $cache = "";
        }
        elseif ($line[$x] == "d") {
            $ttime = $ttime + $cache * 60 * 60 * 24;
            $cache = "";
        }
        elseif ($line[$x] == "h") {
            $ttime = $ttime + $cache * 60 * 60;
            $cache = "";
        }
        elseif ($line[$x] == "m") {
            $ttime = $ttime + $cache * 60;
            $cache = "";
        }
        elseif ($line[$x] == "s") {
            $ttime = $ttime + $cache;;
            $cache = "";
        }
        else {
            return("I");
        }
        $x++;
    }
    return($ttime);
}

function time2str ($line) {
	$str = "";
	$years = 0;
	$months = 0;
	$wks = 0;
	$days = 0;
	$hrs = 0;
	$mins = 0;
	$secs = 0;
	$secs = $line;
	while ($secs >= 60 * 60 * 24 * 30 * 12) {
		$years++;
		$secs = $secs - 60 * 60 * 24 * 30 * 12;
	}
	while ($secs >= 60 * 60 * 24 * 30) {
		$months++;
		$secs = $secs - 60 * 60 * 24 * 30;
	}
	while ($secs >= 60 * 60 * 24 * 7) {
		$wks++;
		$secs = $secs - 60 * 60 * 24 * 7;
	}
	while ($secs >= 60 * 60 * 24) {
		$days++;
		$secs = $secs - 60 * 60 * 24;
	}
	while ($secs >= 60 * 60) {
		$hrs++;
		$secs = $secs - 60 * 60;
	}
	while ($secs >= 60) {
		$mins++;
		$secs = $secs - 60;
	}
	if ($years > 0) {
		$str = $str.$years."years ";
	}
	if ($months > 0) {
		$str = $str.$months."months ";
	}
	if ($wks > 0) {
		$str = $str.$wks."weeks ";
	}
	if ($days > 0) {
		$str = $str.$days."days ";
	}
	if ($hrs > 0) {
		$str = $str.$hrs."hours ";
	}
	if ($mins > 0) {
		$str = $str.$mins."minutes ";
	}
	if ($secs > 0 or $str == "") {
		$str = $str.$secs."seconds";
	}
	if (substr($str,strlen($str) - 1) == " ") {
		$str = substr($str,0,strlen($str) - 1);
	}
	return($str);
}

function send_debug ($data, $channel = null){
	global $debugchannel, $showdebug;
	if(isset($channel)){
		if($showdebug) {
			privmsg($channel, "[Debug] ".$data);
		}
		create_debug_log("[Debug] ".$data);
	}else{
		if($showdebug) {
			privmsg($debugchannel, "[Debug] ".$data);
		}
		create_debug_log("[Debug] ".$data);
	}
}

function mysql_send_query ($data) {
	global $mysql_host, $mysql_user, $mysql_pw, $mysql_db, $connect, $db;
	if(!mysql_ping()) {
		$connect = mysql_connect($mysql_host, $mysql_user, $mysql_pw);
		$db = mysql_select_db($mysql_db, $connect);
	}
	$return = mysql_query($data, $connect) or die(mysql_error());
	return $return;
}

function object_to_array($data) {
    if(is_array($data) || is_object($data)) {
        $result = array();
        foreach($data as $key => $value) {
            $result[$key] = object_to_array($value);
        }
        return $result;
    }
    return $data;
} 

function from_google($query){
    $query=urlencode($query);
    $array=array();
    $url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=".$query."&rsz=large";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, "http://nexus-irc.de");
    $body = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($body);
    $array = object_to_array($json);
	return $array;
}

function git ($nick) {
	$a=str_replace('\r', '',str_replace('\n', '', file_get_contents("http://git.nexus-irc.de/git_v.php?git=NexusStats.git")));
	$b=explode("<br>",$a);
	notice($nick,$b[0]);
	notice($nick,$b[1]);
	notice($nick,$b[2]);
	notice($nick,$b[3]);
	notice($nick,$b[4]);
}

function checkphpstate($php) {
	$data = proc_get_status($php['proc']);
	if(!$data['running']) {
		$out="";
		while(!feof($php['pipes'][1])) {
			$out .= fgets($php['pipes'][1], 128);
		}
		$eout="";
		while(!feof($php['pipes'][2])) {
			$eout .= fgets($php['pipes'][2], 128);
		}
		if($out != "") {
			$out=str_replace("\r","",$out);
			$lines=explode("\n",$out);
			$i=0;
			foreach($lines as $line) {
				$i++;
				if($i>1000) {
					privmsg($php['channel'], "too many lines!");
					break; 
				}
				privmsg($php['channel'], $line);
			}
		}
		if($eout != "") {
			$eout=str_replace("\r","",$eout);
			$lines=explode("\n",$eout);
			$i=0;
			foreach($lines as $line) {
				$i++;
				if($i>1000) {
					privmsg($php['channel'], "too many lines!");
					break; 
				}
				privmsg($php['channel'], "4".$line."");
			}
		}
		fclose($php['pipes'][1]);
		fclose($php['pipes'][2]);
		proc_close($php['proc']);
		return false;
	} else {
		if($php['time']+10 > time()) {
			return true;
		} else {
			//TIMEOUT
			if($php['term']) {
				fclose($php['pipes'][1]);
				fclose($php['pipes'][2]);
				proc_terminate($php['proc'],9);
				privmsg($php['channel'], "php hard timeout. sending SIGKILL");
				return false;
			} else {
				proc_terminate($php['proc']);
				$php['term']=true;
				privmsg($php['channel'], "php timeout. (maximum of 10 seconds exceeded)  sending SIGTERM"); 
				return true;
			}
		}
	}
}
function checkcstate($c) {
	$data = proc_get_status($c['proc']);
	if(!$data['running']) {
		$out="";
		while(!feof($c['pipes'][1])) {
			$out .= fgets($c['pipes'][1], 128);
		}
		$eout="";
		while(!feof($c['pipes'][2])) {
			$eout .= fgets($c['pipes'][2], 128);
		}
		if($out != "") {
			$out=str_replace("\r","",$out);
			$lines=explode("\n",$out);
			$i=0;
			foreach($lines as $line) {
				if($line == "") continue;
				$i++;
				if($i>1000) {
					privmsg($c['channel'], "too many lines!");
					break; 
				}
				privmsg($c['channel'], $line);
			}
		}
		if($eout != "") {
			$eout=str_replace("\r","",$eout);
			$lines=explode("\n",$eout);
			$i=0;
			foreach($lines as $line) {
				if($line == "") continue;
				$i++;
				if($i>1000) { 
					privmsg($c['channel'], "too many lines!");
					break; 
				}
				privmsg($c['channel'], "4".$line."");
			}
		}
		fclose($c['pipes'][1]);
		fclose($c['pipes'][2]);
		proc_close($c['proc']);
		return false;
	} else {
		if($c['time']+10 > time()) {
			return true;
		} else {
			//TIMEOUT
			if($c['term']) {
				fclose($c['pipes'][1]);
				fclose($c['pipes'][2]);
				proc_terminate($c['proc'],9);
				privmsg($c['channel'], "c hard timeout. sending SIGKILL");
				return false;
			} else {
				proc_terminate($c['proc']);
				$c['term']=true;
				privmsg($c['channel'], "c timeout. (maximum of 10 seconds exceeded)  sending SIGTERM"); 
				return true;
			}
		}
	}
}

function slap ($chan, $nick, $nick2 = null) {
	if(isinchan($nick, $chan) == true) {
		privmsg($chan,"\001ACTION slaps ".$nick." around a bit with a large trout\001");
	}else{
		notice($nick2,"\002".$nick."\002 is not on \002".$chan."\002.");
	}
}

function isinchan ($nick, $channel) {
	global $channeluser;
	foreach ($channeluser[$channel] as $id => $user) {
		if (strtolower($user) == strtolower($nick)) {
			return true;
		}
	}
}

function isonchannel ($nick) {
	global $channeluser;
	foreach ($channeluser as $chan => $users) { 
		foreach ($users as $id => $user) { 
			if ($user == $nick) { 
				return true;
			}
		}
	}
}

function putSocket ($line) {
    echo(">>$line\n");
    flush();
    global $socket, $glob;
	$glob['dat_out'] = $glob['dat_out'] + strlen($line);
    fwrite($socket,$line."\n");
}
?>
