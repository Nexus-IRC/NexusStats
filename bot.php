<?php
/***********************************************************************
* Copyright (C) 2011  Jan Altensen (Stricted)                          *
* email: info@webhostmax.de                                            *
* This program is free software: you can redistribute it and/or modify *
* it under the terms of the GNU General Public License as published by *
* the Free Software Foundation, either version 3 of the License, or    *
* (at your option) any later version.                                  *
*                                                                      *
* This program is distributed in the hope that it will be useful,      *
* but WITHOUT ANY WARRANTY; without even the implied warranty of       *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
* GNU General Public License for more details.                         *
*                                                                      *
* You should have received a copy of the GNU General Public License    *
* along with this program. If not, see <http://www.gnu.org/licenses/>. *
*                                                                      *
***********************************************************************/
echo("##############################\n");
echo("#### Starting NexusStats  ####\n");
echo("#### version 1.8          ####\n");
echo("#### coded by Stricted    ####\n");
echo("##############################\n");
/* config start */
$server 	= "localhost";
$port 		= "8001";
$botnick 	= "NexusStats";
$pass 		= "NexusStats:xxxx";
$admin 		= "Stricted2.user.OnlineGamesNet";
/* config end */
set_time_limit(0);
$socket = fsockopen($server,$port,$errstr,$errno,2);
$dltimer = array();
$timer = time();
$stime = time();
stream_set_blocking($socket,0);
putSocket("PASS ".$pass);
putSocket("NICK ".$botnick);
putSocket("USER ".$botnick." 0 * :".$botick." (php".PHP_VERSION.")");

while (true) {
    if (feof($socket)) {
        $socket = fsockopen($server,$port,$errstr,$errno,2);
        $dltimer = array();
        $timer = time();
		$stime = time();
        stream_set_blocking($socket,0);
        putSocket("PASS ".$pass);
		putSocket("NICK ".$botnick);
		putSocket("USER ".$botnick." 0 * :".$botick." (php".PHP_VERSION.")");
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
		$fg = utf8_decode(str_replace("\r","",str_replace("\n","",$fg)));
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
            #putSocket("JOIN #nexus"); 
			create_timer("12h","stats");
        }
		eval(file_get_contents("code.php"));
		
    }
}


function create_timer ($time, $line) {
	global $dltimer;
	$ttime = time() + str2time($time);
	$dlc = count($dltimer[$ttime]) + 1;
	$dltimer[$ttime][$dlc] = $line;
}

function create_log ($channel, $data) {
	$inhalt1 = file_get_contents("/home/stats/noreg.cfg");	
	if ( stristr($inhalt1, $channel) == true ) {
	}else{
		$inhalt2 = file_get_contents("/home/stats/channel.cfg");	
		if ( stristr($inhalt2, $channel) == true ) {
			$inhalt = file_get_contents("/home/stats/pisg-0.73/log/".$channel.".log");
			file_put_contents("/home/stats/pisg-0.73/log/".$channel.".log", $inhalt .= $data."\n");
		}else{ 
		}
	}
}

function create_noreg ($channel, $nick) {
	$inhalt = file_get_contents("/home/stats/noreg.cfg");
	file_put_contents("/home/stats/noreg.cfg", $inhalt .= $channel."\n");
	del_chan ($channel);
	putSocket("PART ".$channel." :Unregistered by ".$nick.".");
}

function create_chan ($channel) {
	$cha = @substr($channel, 1);
	@unlink("/home/stats/pisg-0.73/cfg/".$cha.".cfg");
	$text1  = "<channel='".$channel."'>\n";
	$text2  = "Logfile = '/home/stats/pisg-0.73/log/".$cha.".log'\n";
	$text3  = "ColorScheme = 'default'\n";
	$text4  = "Format = 'mIRC'\n";
	$text5  = "Lang = 'EN'\n";
	$text6  = "DailyActivity = '31'\n";	
	$text7  = "Network= 'OnlineGamesNet'\n";
	$text8  = "Maintainer = 'NexusStats'\n";
	$text9  = "OutputFile = '/var/customers/webs/nexus/stats/chan/".$cha.".php'\n";
	$text10 = "</channel>\n";
	$dateiname = "/home/stats/pisg-0.73/cfg/".$cha.".cfg"; 
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
	fClose($handler);
	$inhalt = file_get_contents("/home/stats/channel.cfg");
	file_put_contents("/home/stats/channel.cfg", $inhalt .= $channel."|EN\n");
}

function set_lang ($chan, $lang) {
	$cha = @substr($chan, 1);
	if (file_exists("/home/stats/pisg-0.73/cfg/".$cha.".cfg")) {
		@unlink("/home/stats/pisg-0.73/cfg/".$cha.".cfg");
		$text1  = "<channel='".$chan."'>\n";
		$text2  = "Logfile = '/home/stats/pisg-0.73/log/".$cha.".log'\n";
		$text3  = "ColorScheme = 'default'\n";
		$text4  = "Format = 'mIRC'\n";
		$text5  = "Lang = '".$lang."'\n";
		$text6  = "DailyActivity = '31'\n";	
		$text7  = "Network= 'OnlineGamesNet'\n";
		$text8  = "Maintainer = 'NexusStats'\n";
		$text9  = "OutputFile = '/var/customers/webs/nexus/stats/chan/".$cha.".php'\n";
		$text10 = "</channel>\n";
		$dateiname = "/home/stats/pisg-0.73/cfg/".$cha.".cfg"; 
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
		fClose($handler);
		$myfile = file_get_contents("/home/stats/channel.cfg");
		$myexp  = explode("\n", $myfile);
		$i = 0;
		while (@($myexp[$i])) {
			$a= explode("|",$myexp[$i]);
			if ($a[0] == $chan) {
			} else {
				if ($myfilenew == "") {
					$myfilenew = $a[0]."|".$a[1]."\n";
				} else {
					$myfilenew = $myfilenew.$a[0]."|".$a[1]."\n";
				}
			}
			$i++;
		}
		$fp = fopen("/home/stats/channel.cfg","w+");
		fwrite($fp, $myfilenew);
		fclose($fp);
		$inhalt = file_get_contents("/home/stats/channel.cfg");
		file_put_contents("/home/stats/channel.cfg", $inhalt .= $chan."|".$lang."\n");
		@unlink("/var/customers/webs/nexus/stats/chan/".$cha.".php");
		shell_exec("/home/stats/pisg-0.73/pisg --configfile=cfg/".$cha.".cfg");
		privmsg($chan,"Stats Update: http://stats.nexus-irc.de/?c=".$cha);	
	}else{
	}
}

function create_conf () {
	$datei = "/home/stats/channel.cfg";
	$array = file($datei);
	foreach ($array as $element) {
		$a=explode("|",$element);
		@unlink("/home/stats/pisg-0.73/cfg/".substr($a[0], 1).".cfg");
		$text1  = "<channel='#".substr($a[0], 1)."'>\n";
		$text2  = "Logfile = '/home/stats/pisg-0.73/log/".substr($a[0], 1).".log'\n";
		$text3  = "ColorScheme = 'default'\n";
		$text4  = "Format = 'mIRC'\n";
		$text5  = "Lang = '".substr($a[1], 0, -1)."'\n";
		$text6  = "DailyActivity = '31'\n";	
		$text7  = "Network= 'OnlineGamesNet'\n";
		$text8  = "Maintainer = 'NexusStats'\n";
		$text9  = "OutputFile = '/var/customers/webs/nexus/stats/chan/".substr($a[0], 1).".php'\n";
		$text10 = "</channel>\n";
		$dateiname = "/home/stats/pisg-0.73/cfg/".substr($a[0], 1).".cfg"; 
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
		fClose($handler);
	}
}

function del_chan ($channel) {
	$myfilenew = NULL;
	$cha = @substr($channel, 1);
	@unlink("/home/stats/pisg-0.73/cfg/".$cha.".cfg");
	@unlink("/home/stats/pisg-0.73/log/".$cha.".log");
	@unlink("/var/customers/webs/nexus/stats/chan/".$cha.".php");

	$myfile = file_get_contents("/home/stats/channel.cfg");
	$myexp  = explode("\n", $myfile);
	$i = 0;
	while (@($myexp[$i])) {
		$a= explode("|",$myexp[$i]);
		$a= explode("|",$myexp[$i]);
		if ($a[0] == $channel) {
		} else {
			if ($myfilenew == "") {
				$myfilenew = $a[0]."|".$a[1]."\n";
			} else {
				$myfilenew = $myfilenew.$a[0]."|".$a[1]."\n";
			}
		}
		$i++;
	}
	$fp = fopen("/home/stats/channel.cfg","w+");
	fwrite($fp, $myfilenew);
	fclose($fp);
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

function create_stats ($chan) {
	if(isset($chan)){ //optional
		$cha = @substr($chan, 1);
		@unlink("/var/customers/webs/nexus/stats/chan/".$cha.".php");
		shell_exec("/home/stats/pisg-0.73/pisg --configfile=cfg/".$cha.".cfg");
		privmsg($chan,"Stats Update: http://stats.nexus-irc.de/?c=".$cha);
	}else{	
		$datei = "/home/stats/channel.cfg";
		$array = file($datei);
		foreach ($array as $element) {
			$a=explode("|",$element);
			shell_exec("/home/stats/pisg-0.73/pisg --configfile=cfg/".substr($a[0], 1).".cfg\n");
			privmsg($a[0],"Stats Update: http://stats.nexus-irc.de/?c=".substr($a[0], 1));
		}
		create_timer("12h","stats");
	}
}

function reset_stats () {
	$datei = "/home/stats/channel.cfg";
	$array = file($datei);
	foreach ($array as $element) {
		$a=explode("|",$element);
		@unlink("/home/stats/pisg-0.73/log/".substr($a[0], 1).".log");
		@unlink("/var/customers/webs/nexus/stats/archiv/".substr($a[0], 1).".php");
		mkdir("/var/customers/webs/nexus/stats/archiv/", 0755);
		copy("/var/customers/webs/nexus/stats/chan/".substr($a[0], 1).".php", "/var/customers/webs/nexus/stats/archiv/".substr($a[0], 1).".php");
		@unlink("/var/customers/webs/nexus/stats/chan/".substr($a[0], 1).".php");
		privmsg($a[0],"Stats Reset, Archiv: http://stats.nexus-irc.de/?ac=".substr($a[0], 1));
	}
	create_timer("24h","stats");
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

function putSocket ($line) {
    echo(">>$line\n");
    flush();
    global $socket;
    fwrite($socket,$line."\n");
}
?>
