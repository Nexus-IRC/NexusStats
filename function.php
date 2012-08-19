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
function create_timer ($time, $line) {
	global $dltimer;
	$ttime = time() + str2time($time);
	$dlc = count($dltimer[$ttime]) + 1;
	$dltimer[$ttime][$dlc] = $line;
}

function create_log ($channel, $data) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $botdir, $url, $aurl, $defaultlang;
	$inhalt1 = file_get_contents($botdir."noreg.cfg");	
	if ( stristr($inhalt1, $channel) == true ) {
	}else{
		$inhalt2 = file_get_contents($botdir."channel.cfg");	
		if ( stristr($inhalt2, $channel) == true ) {
			$inhalt = file_get_contents($logdir.$channel.".log");
			file_put_contents($logdir.$channel.".log", $inhalt .= $data."\n");
		}else{ 
		}
	}
}

function create_noreg ($channel, $nick) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $botdir, $url, $aurl;
	$inhalt = file_get_contents($botdir."noreg.cfg");
	file_put_contents($botdir."noreg.cfg", $inhalt .= $channel."\n");
	del_chan ($channel);
	putSocket("PART ".$channel." :Unregistered by ".$nick.".");
}

function create_chan ($channel) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $botdir, $url, $aurl, $defaultlang;
	$cha = @substr($channel, 1);
	@unlink($cfgdir.$cha.".cfg");
	$text1  = "<channel='".$channel."'>\n";
	$text2  = "Logfile = '".$logdir.$cha.".log'\n";
	$text3  = "ColorScheme = 'default'\n";
	$text4  = "Format = 'mIRC'\n";
	$text5  = "Lang = '".$defaultlang."'\n";
	$text6  = "DailyActivity = '31'\n";	
	$text7  = "Network= 'OnlineGamesNet'\n";
	$text8  = "Maintainer = '".$botnick."'\n";
	$text9  = "OutputFile = '".$statsdir.$cha.".php'\n";
	$text10 = "</channel>\n";
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
	fClose($handler);
	$inhalt = file_get_contents($botdir."channel.cfg");
	file_put_contents($botdir."channel.cfg", $inhalt .= $channel."|".$defaultlang."\n");
	putSocket("join ".$channel);
}

function set_lang ($chan, $lang = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $botdir, $url, $aurl, $defaultlang;
	if(isset($lang)){	
		$cha = @substr($chan, 1);
		if (file_exists($cfgdir.$cha.".cfg")) {
			@unlink($cfgdir.$cha.".cfg");
			$text1  = "<channel='".$chan."'>\n";
			$text2  = "Logfile = '".$logdir.$cha.".log'\n";
			$text3  = "ColorScheme = 'default'\n";
			$text4  = "Format = 'mIRC'\n";
			$text5  = "Lang = '".$lang."'\n";
			$text6  = "DailyActivity = '31'\n";	
			$text7  = "Network= 'OnlineGamesNet'\n";
			$text8  = "Maintainer = '".$botnick."'\n";
			$text9  = "OutputFile = '".$statsdir.$cha.".php'\n";
			$text10 = "</channel>\n";
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
			fClose($handler);
			$myfile = file_get_contents($botdir."channel.cfg");
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
			$fp = fopen($botdir."channel.cfg","w+");
			fwrite($fp, $myfilenew);
			fclose($fp);
			$inhalt = file_get_contents($botdir."channel.cfg");
			file_put_contents($botdir."channel.cfg", $inhalt .= $chan."|".$lang."\n");
			@unlink($statsdir.$cha.".php");
			shell_exec($pisgdir."pisg --configfile=".$cfgdir.$cha.".cfg");
			privmsg($chan,"Stats Update: ".$url.$cha);	
		}else{
		}
	}else{
		$cha = @substr($chan, 1);
		if (file_exists($cfgdir.$cha.".cfg")) {
			@unlink($cfgdir.$cha.".cfg");
			$text1  = "<channel='".$chan."'>\n";
			$text2  = "Logfile = '".$logdir.$cha.".log'\n";
			$text3  = "ColorScheme = 'default'\n";
			$text4  = "Format = 'mIRC'\n";
			$text5  = "Lang = '".$$defaultlang."'\n";
			$text6  = "DailyActivity = '31'\n";	
			$text7  = "Network= 'OnlineGamesNet'\n";
			$text8  = "Maintainer = '".$botnick."'\n";
			$text9  = "OutputFile = '".$statsdir.$cha.".php'\n";
			$text10 = "</channel>\n";
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
			fClose($handler);
			$myfile = file_get_contents($botdir."channel.cfg");
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
			$fp = fopen($botdir."channel.cfg","w+");
			fwrite($fp, $myfilenew);
			fclose($fp);
			$inhalt = file_get_contents($botdir."channel.cfg");
			file_put_contents($botdir."channel.cfg", $inhalt .= $chan."|".$defaultlang."\n");
			@unlink($statsdir.$cha.".php");
			shell_exec($pisgdir."pisg --configfile=".$cfgdir.$cha.".cfg");
			privmsg($chan,"Stats Update: ".$url.$cha);	
		}else{
		}
	}
}

function create_conf ($channel = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $botdir, $url, $aurl, $defaultlang;
	if(isset($channel)){//optional
		$cha = @substr($channel, 1);
		@unlink($cfgdir.$cha.".cfg");
		$text1  = "<channel='".$channel."'>\n";
		$text2  = "Logfile = '".$logdir.$cha.".log'\n";
		$text3  = "ColorScheme = 'default'\n";
		$text4  = "Format = 'mIRC'\n";
		$text5  = "Lang = '".$lang."'\n";
		$text6  = "DailyActivity = '31'\n";	
		$text7  = "Network= 'OnlineGamesNet'\n";
		$text8  = "Maintainer = '".$botnick."'\n";
		$text9  = "OutputFile = '".$statsdir.$cha.".php'\n";
		$text10 = "</channel>\n";
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
		fClose($handler);
	}else{
		$datei = $botdir."channel.cfg";
		$array = file($datei);
		foreach ($array as $element) {
			$a=explode("|",$element);
			@unlink($cfgdir.substr($a[0], 1).".cfg");
			$text1  = "<channel='#".substr($a[0], 1)."'>\n";
			$text2  = "Logfile = '".$logdir.substr($a[0], 1).".log'\n";
			$text3  = "ColorScheme = 'default'\n";
			$text4  = "Format = 'mIRC'\n";
			$text5  = "Lang = '".substr($a[1], 0, -1)."'\n";
			$text6  = "DailyActivity = '31'\n";	
			$text7  = "Network= 'OnlineGamesNet'\n";
			$text8  = "Maintainer = '".$botnick."'\n";
			$text9  = "OutputFile = '".$statsdir.substr($a[0], 1).".php'\n";
			$text10 = "</channel>\n";
			$dateiname = $cfgdir.substr($a[0], 1).".cfg"; 
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
}

function del_chan ($channel) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $botdir, $url, $aurl, $defaultlang;
	$myfilenew = NULL;
	$cha = @substr($channel, 1);
	@unlink($cfgdir.$cha.".cfg");
	@unlink($logdir.$cha.".log");
	@unlink($statsdir.$cha.".php");

	$myfile = file_get_contents($botdir."channel.cfg");
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
	$fp = fopen($botdir."channel.cfg","w+");
	fwrite($fp, $myfilenew);
	fclose($fp);
	putSocket("part ".$channel);
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
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $botdir, $url, $aurl, $defaultlang;
	if(isset($chan)){ //optional
		$cha = @substr($chan, 1);
		@unlink($statsdir.$cha.".php");
		shell_exec($pisgdir."pisg --configfile=".$cfgdir.$cha.".cfg");
		privmsg($chan,"Stats Update: ".$url.$cha);
	}else{	
		$datei = $botdir."channel.cfg";
		$array = file($datei);
		foreach ($array as $element) {
			$a=explode("|",$element);
			shell_exec($pisgdir."pisg --configfile=".$cfgdir.substr($a[0], 1).".cfg\n");
			privmsg($a[0],"Stats Update: ".$url.substr($a[0], 1));
		}
		create_timer("12h","stats");
	}
}

function reset_stats ($chan = null) {
	global $logdir,	$cfgdir, $statsdir, $archivdir, $pisgdir, $botdir, $url, $aurl, $defaultlang;
	if(isset($chan)){ //optional
		@unlink($logdir.substr($chan, 1).".log");
		@unlink($archivdir.substr($chan, 1).".php");
		mkdir($archivdir, 0755);
		copy($statsdir.substr($chan, 1).".php", $archivdir.substr($chan, 1).".php");
		@unlink($statsdir.substr($chan, 1).".php");
		privmsg($chan,"Stats Reset, Archiv: ".$aurl.substr($chan, 1));
		create_log(substr($chan, 1), "[".@date("H:i")."] <".$botnick."> Stats Reset, Archiv: ".$aurl.substr($chan, 1));
		create_stats ($chan);
	}else{
		$datei = $botdir."channel.cfg";
		$array = file($datei);
		foreach ($array as $element) {
			$a=explode("|",$element);
			@unlink($logdir.substr($a[0], 1).".log");
			@unlink($archivdir.substr($a[0], 1).".php");
			mkdir($archivdir, 0755);
			copy($statsdir.substr($a[0], 1).".php", $archivdir.substr($a[0], 1).".php");
			@unlink($statsdir.substr($a[0], 1).".php");
			privmsg($a[0],"Stats Reset, Archiv: ".$aurl.substr($a[0], 1));
			create_log(substr($a[0], 1), "[".@date("H:i")."] <".$botnick."> Stats Reset, Archiv: "$aurl.substr($a[0], 1));
		}
		create_stats ();
		create_timer("24h","stats");
	}
}


function debug ($chan, $data = null) {
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