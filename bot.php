<?php
/* Bot.php - NexusStats v2.2
 * Copyright (C) 2012  Jan Altensen (Stricted)
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
$glob=array();
$connect = mysql_connect($mysql_host, $mysql_user, $mysql_pw);
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
        $thistime = time();
        foreach ($dltimer as $thetime => $evntarray) {
            if ($thetime <= time()) {
                timer_evnts($thetime,1);
            }
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
        if ($exp[1] == "001") {
			$result = mysql_send_query("SELECT * FROM `Channel` WHERE `Noreg` = '0'");
			while ( $row = mysql_fetch_array($result) ){
				putSocket("JOIN ".$row['Name']); //debug channel
				who($row['Name'], "2");
			}
            putSocket("JOIN ".$debugchannel); //debug channel
			create_timer("12h","stats");
        }
		include("code.inc.php");
		
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
	$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".$channel."' AND `Noreg` = '1'");
	$row = mysql_fetch_array($a);
	if($row['Name'] == $channel){
	}else{
		$b = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".$channel."' AND `Noreg` = '0'");
		$row2 = mysql_fetch_array($b);
		if($row2['Name'] == $channel){
			$cha = @substr($channel, 1);
			$inhalt = file_get_contents($logdir.$cha.".log");
			file_put_contents($logdir.$cha.".log", $inhalt .= $data."\n");
		}else{ 
		}
	}
}


function create_debug_log ($data) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang, $debuglog;
	$datei = $debuglog;
	$inhalt = file_get_contents($datei);
	file_put_contents($datei, $inhalt .= date("d.m.y")." ".date("H:i:s").": ".$data."\n");
}
 
function create_noreg ($channel, $nick) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl;
	$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".$channel."' AND `Noreg` = '0'");
	$row = mysql_fetch_array($a);
	if($row['Name'] == $channel){
		mysql_send_query("UPDATE `Channel` SET `Noreg` = '1' WHERE `Name` = '".$channel."'");
		del_chan ($channel, true);
		putSocket("PART ".$channel." :Unregistered by ".$nick.".");
		send_debug("Add ".$channel." to the no register list");
	}
}

function create_chan ($channel) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang;
	$cha = @substr($channel, 1);
	create_conf($channel);
	mysql_send_query("INSERT INTO `Channel` (`ID` ,`Name` ,`Lang` ,`Noreg` ) VALUES (NULL , '".$channel."', '".$defaultlang."', '0');");
	putSocket("join ".$channel);
	send_debug("Add channel ".$channel);
}

function set_lang ($chan, $lang = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang;
	if(isset($lang)){	
		$cha = @substr($chan, 1);
		if (file_exists($cfgdir.$cha.".cfg")) {
			create_conf($chan, $lang);
			mysql_send_query("UPDATE `Channel` SET `Lang` = '".$lang."' WHERE `Name` = '".$chan."'");
			@unlink($statsdir.$cha.".php");
			shell_exec($pisgdir."pisg --configfile=".$cfgdir.$cha.".cfg");
			privmsg($chan,"Stats Update: ".$url.$cha);	
			send_debug("Language for channel ".$chan." changed to ".$lang);
		}
	}else{
		$cha = @substr($chan, 1);
		if (file_exists($cfgdir.$cha.".cfg")) {
			create_conf ($chan);
			mysql_send_query("UPDATE `Channel` SET `Lang` = '".$defaultlang."' WHERE `Name` = '".$chan."'");
			@unlink($statsdir.$cha.".php");
			shell_exec($pisgdir."pisg --configfile=".$cfgdir.$cha.".cfg");
			privmsg($chan,"Stats Update: ".$url.$cha);	
			send_debug("Language for channel ".$chan." changed to ".$defaultlang);
		}else{
		}
	}
}

function create_conf ($channel = null, $lang = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang, $botnick, $network;
	if(isset($channel)){//optional
		$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".$channel."' AND `Noreg` = '0'");
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
				$text6  = "DailyActivity = '31'\n";	
				$text7  = "NickTracking = '1'\n";			
				$text8  = "Network= '".$network."'\n";
				$text9  = "Maintainer = '".$botnick."'\n";
				$text10  = "OutputFile = '".$statsdir.$cha.".php'\n";
				$text11 = "</channel>\n";
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
				fClose($handler);
				send_debug("Config for channel ".$channel." createt");
			}else{
				$cha = @substr($channel, 1);
				@unlink($cfgdir.$cha.".cfg");
				$text1  = "<channel='".$channel."'>\n";
				$text2  = "Logfile = '".$logdir.$cha.".log'\n";
				$text3  = "ColorScheme = 'default'\n";
				$text4  = "Format = 'mIRC'\n";
				$text5  = "Lang = 'EN'\n";
				$text6  = "DailyActivity = '31'\n";	
				$text7  = "NickTracking = '1'\n";		
				$text8  = "Network= '".$network."'\n";
				$text9  = "Maintainer = '".$botnick."'\n";
				$text10  = "OutputFile = '".$statsdir.$cha.".php'\n";
				$text11 = "</channel>\n";
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
				fClose($handler);
				send_debug("Config for channel ".$channel." createt");
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
			$text6  = "DailyActivity = '31'\n";	
			$text7  = "NickTracking = '1'\n";
			$text8  = "Network= '".$network."'\n";
			$text9  = "Maintainer = '".$botnick."'\n";
			$text10  = "OutputFile = '".$statsdir.substr($row['Name'], 1).".php'\n";
			$text11 = "</channel>\n";
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
			fClose($handler);
		}
		send_debug("Config for all channels createt");
	}
}

function del_chan ($channel, $noreg=null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang, $channeluser;
	$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".$channel."'");
	$row = mysql_fetch_array($a);
	if($row['Name'] == $channel){
		$cha = @substr($channel, 1);
		@unlink($cfgdir.$cha.".cfg");
		@unlink($logdir.$cha.".log");
		@unlink($statsdir.$cha.".php");
		if(isset($noreg)){
		}else{
			mysql_send_query("DELETE FROM `Channel` WHERE `Name` = '".$channel."'");
		}
		putSocket("part ".$channel);
		send_debug("Delete channel ".$channel);
		unset($channeluser[$channel]);
	}
}

function check_stats () {
	$reset = array("01.01","01.02","01.03","01.04","01.05","01.06","01.07","01.08","01.09","01.10","01.11","01.12");
	$stamp = time();
	if(in_array(date("d.m",$stamp), $reset)){
		reset_stats();
	}else{
		create_stats();
	}
}

function create_stats ($chan = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang;
	if(isset($chan)){ //optional
		$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".$chan."' AND `Noreg` = '0'");
		$row = mysql_fetch_array($a);
		if($row['Name'] == $chan){
			$cha = @substr($chan, 1);
			@unlink($statsdir.$cha.".php");
			shell_exec($pisgdir."pisg --configfile=".$cfgdir.$cha.".cfg");
			privmsg($chan,"Stats Update: ".$url.$cha);
			send_debug("Stats createt ".$chan);
		}
	}else{	
		$result = mysql_send_query("SELECT * FROM `Channel` WHERE `Noreg` = '0'");
		while ( $row = mysql_fetch_array($result) ){
			shell_exec($pisgdir."pisg --configfile=".$cfgdir.substr($row['Name'], 1).".cfg\n");
			privmsg($row['Name'],"Stats Update: ".$url.substr($row['Name'], 1));
		}
		create_timer("12h","stats");
		send_debug("Stats createt");
	}
}

function reset_stats ($chan = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $url, $aurl, $defaultlang;
	if(isset($chan)){ //optional
		$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".$chan."' AND `Noreg` = '0'");
		$row = mysql_fetch_array($a);
		if($row['Name'] == $chan){
			@unlink($logdir.substr($chan, 1).".log");
			@unlink($archivdir.substr($chan, 1).".php");
			mkdir($archivdir, 0755);
			copy($statsdir.substr($chan, 1).".php", $archivdir.substr($chan, 1).".php");
			@unlink($statsdir.substr($chan, 1).".php");
			privmsg($chan,"Stats Reset, Archiv: ".$aurl.substr($chan, 1));
			create_log(substr($chan, 1), "[".@date("H:i")."] <".$botnick."> Stats Reset, Archiv: ".$aurl.substr($chan, 1));
			create_stats ($chan);
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
			privmsg($row['Name'],"Stats Reset, Archiv: ".$aurl.substr($row['Name'], 1));
			create_log(substr($row['Name'], 1), "[".@date("H:i")."] <".$botnick."> Stats Reset, Archiv: ".$aurl.substr($row['Name'], 1));
			create_stats($row['Name']);
		}
		create_timer("24h","stats");
		send_debug("Stats resetet");
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
	global $debugchannel;
	if(isset($channel)){
		privmsg($channel, "[Debug] ".$data);
		create_debug_log("[Debug] ".$data);
	}else{
		privmsg($debugchannel, "[Debug] ".$data);
		create_debug_log("[Debug] ".$data);
	}
}

function check_version ($nick = null) {
	global $gitversion;
	if(isset($nick)){
		if($gitversion) {
			$version = file_get_contents("http://git.nexus-irc.de/git_version.php?git=NexusStats.git");
			if($gitversion != $version) {
				notice($nick, "[UPDATE] There is an version update available on http://git.nexus-irc.de/?p=NexusStats.git");
			}else{
				notice($nick, "no update available");
			}
		}
	}else{
		if($gitversion) {
			$version = file_get_contents("http://git.nexus-irc.de/git_version.php?git=NexusStats.git");
			if($gitversion != $version) {
				return "[UPDATE] There is an version update available on http://git.nexus-irc.de/?p=NexusStats.git";
			}
		}
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

function putSocket ($line) {
    echo(">>$line\n");
    flush();
    global $socket, $glob;
	$glob['dat_out'] = $glob['dat_out'] + strlen($line);
    fwrite($socket,$line."\n");
}
?>
