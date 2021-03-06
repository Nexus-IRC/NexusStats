<?php     
/* code.inc.php - NexusStats v2.3
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
switch(strtolower($exp[1])) { // raw
	case "001":
		$result = mysql_send_query("SELECT * FROM `Channel` WHERE `Noreg` = '0'");
		while ( $row = mysql_fetch_array($result) ){
			putSocket("JOIN ".$row['Name']); // join channels
			who($row['Name'], "2");
		}
		putSocket("JOIN ".$debugchannel); // debug channel
		create_timer("1m","stats");
		break;
	case "354":
		switch(strtolower($exp[3])) {
			case "1":
				$auth[strtolower($exp[4])] = $exp[5];
				send_debug("[who] [user] ".$exp[4]);
				break;
			case "2":
				$users[] = $exp[4];
				$auth1[strtolower($exp[4])] = $exp[6];
				break;
		}
		break;
	case "471":
		send_debug("Cannot join channel ".$exp[3]." (+l)");
		break;
	case "473":
		send_debug("Cannot join channel ".$exp[3]." (+i)");
		break;
	case "474":
		send_debug("Cannot join channel ".$exp[3]." (+b)");
		break;
	case "475":
		send_debug("Cannot join channel ".$exp[3]." (+k)");
		break;
	case "345":
		send_debug("[".$exp[2]."] ".$exp[3]." has been invited by ".$exp[4]."");
		break;
	case "315":
		$x = explode(",",$exp[3]);
		$target = $x[0];
		$id = $x[1];
		unset($channeluser[$target]);
		if ($id == 2) {
			$i=0;
			foreach ($users as $unick) {
				$i++;
				$channeluser[$target][$unick] = $unick;
				$auth[strtolower($unick)] = $auth1[strtolower($unick)];
			}
			unset($users);
			unset($i);
			send_debug("[who] [channel] ".$target);
		}
		break;
	case "privmsg":
		$kk = explode(" ",$fg,4);
		$act = explode(" ",@substr($kk[3], 1),2);
		$cha = @substr($kk[2], 1);
		switch($act[0]) { // ctcp
			case "\001ACTION":
				if($nick == $botnick OR $nick == $botnick."|ZNC" OR $cha == $botnick OR $cha == $botnick."|ZNC" OR $cha == substr($botnick, 1) OR $cha == substr($botnick, 1)."|ZNC") {
				}else{
					create_log($kk[2],"[".@date("H:i")."] * ".$nick." ".$act[1]);
				}
				break;
			case "\001VERSION\001":
				if($gitversion){
					notice($nick,"\001VERSION NexusStats ".$version." by Stricted (".$gitversion.")");
				}else{
					notice($nick,"\001VERSION NexusStats ".$version." by Stricted");
				}
				break;
			case "\001UPTIME\001":
				notice($nick,"\001UPTIME ".time2str(time() - $stime));
				break;
			case "\001TIME\001":
				$time = @date('r');
				notice($nick,"\001Time ".$time);
				break;
			case "\001PING":
				$ping = ($act[1] - (60*60*1337 + 42*60));
				notice($nick,"\001PING ".$ping);
				break;
			default:
				if($nick == $botnick OR $nick == $botnick."|ZNC" OR $cha == $botnick OR $cha == $botnick."|ZNC" OR $cha == substr($botnick, 1) OR $cha == substr($botnick, 1)."|ZNC") {
				}else{
					create_log($kk[2],"[".@date("H:i")."] <".$nick."> ".@substr($kk[3], 1));
				}
				break;
		}
		
		if ($exp[2][0] != "#") {
			switch(strtolower($command)) { // query
				case "raw":
					if(in_array(getauth($nick),$rawallow)){
						switch(strtolower($exp[4])) { // query-raw
							case "part":
								putSocket("part ".$exp[5]);
								send_debug("[".$nick."] [RAW] part ".$exp[5]);
								break;
							case "join":
								putSocket("join ".$exp[5]);
								send_debug("[".$nick."] [RAW] join ".$exp[5]);
								break;
						}
					}
					break;
			}
		} else 
		{
			switch(strtolower($command)) { // channel
				case $trigger."kaffee":
					if($exp[4]){
						privmsg($exp[2],"\001ACTION gibt ".$exp[4]." einen hei�en Kaffee\001");
					}else{
						privmsg($exp[2],"\001ACTION gibt ".$nick." einen hei�en Kaffee");
					}
					break;
				case $trigger."kakao":
					if($exp[4]){
						privmsg($exp[2],"\001ACTION gibt ".$exp[4]." einen hei�en Kakao mit Sahne\001");
					}else{
						privmsg($exp[2],"\001ACTION gibt ".$nick." einen hei�en Kakao mit Sahne\001");
					}
					break;
				case $trigger."stats":
					create_stats($exp[2]);
					break;
				case $trigger."checkstats":
					check_stats($exp[2]);
					break;
				case $trigger."unreg":
					if(getauth($nick) == $admin){
						if($exp[4]){
							if(strtolower($exp[5]) == "force"){
								create_noreg($exp[4],$nick,true);
							}else{
								create_noreg($exp[4],$nick);
							}
						}else{
							notice($nick,"You must enter a channel name");
						}
					}else{
						break;
					}
					break;
				case $trigger."reg":
					if(getauth($nick) == $admin){
						if($exp[4]){
							if(strtolower($exp[5])=="force"){
								create_chan($exp[4],true);
							}else{
								$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($exp[4])."' AND `Noreg` = '1'");
								$row = mysql_fetch_array($a);
								if($row['Name'] == $exp[4]){
									notice($nick,"The Channel: ".$exp[4]." is on the not register list.");
								} else {						
									create_chan($exp[4]);
								}
							}
						}else{
							notice($nick,"You must enter a channel name");
						}
					}else{
						break;
					}
					break;					
				case $trigger."setlang":
					if(getauth($nick) == $admin){
						if(isset($exp[4])){
							if(isset($exp[5])){
								set_lang($exp[4],$exp[5]);
							}elseif($exp[5] == "*"){
								set_lang($exp[4],$defaultlang);
							}else{
								notice($nick,"You must enter a country code");
							}	
						}else{
							notice($nick,"You must enter a channel name");
						}
					}else{
						break;
					}
					break;
				case $trigger."nostats":
					if(getauth($nick) == $admin){
						if(isset($exp[4])){
							set_nostats($exp[4]);
						}else{
							notice($nick,"You must enter a channel name");
						}
					}else{
						break;
					}
					break;
				case $trigger."clist":
					if(getauth($nick) == $admin){
						require_once("Table.class.php");
						$table = new Table(3);
						$table->add("Name", "Lang", "URL");
						$result = mysql_send_query("SELECT * FROM `Channel` WHERE `Noreg` = '0' ORDER BY `ID` ASC");
						while ( $row = mysql_fetch_array($result) ){
							$b = @substr($row['Name'], 1);
							$c = $row['Lang'];
							$table->add($row['Name'], $c, $url.$b);
						}
						$lines = $table->end();
						$i = -1;
						foreach($lines as $line) {
							notice($nick, $line);
							$i++;
						}
						notice($nick,"\002".$i."\002 channels found");
					}else{
						break;
					}
					break;
				case $trigger."debug":
					if(getauth($nick) == $admin){
						debug($exp[2]);						
					}else{
						break;
					}
					break;
				case $trigger."resetstats":
					if(getauth($nick) == $admin){
						if(isset($exp[4])){
							reset_stats($exp[4]);
						}else{
							reset_stats();
						}
					}else{
						break;
					}
					break;
				case $trigger."version":
					if($gitversion) {
						notice($nick, "NexusStats v".$version." (".$gitversion."), written by Stricted");
					}else{
						notice($nick, "NexusStats v".$version.", written by Stricted");
					}
					notice($nick, "Build ".$creation." (".$codelines." lines, PHP ".PHP_VERSION.")");
					notice($nick, "NexusStats can be found on: http://git.nexus-irc.de/?p=NexusStats.git");
					notice($nick, "special thanks to:");
					notice($nick, " Ultrashadow  (testing and ideas)");
					notice($nick, " pk910        (ideas)");
					notice($nick, " Calisto      (ideas)");
					notice($nick, "If you found a bug or if you have a good idea report it on http://bugtracker.nexus-irc.de/");
					break;
				case $trigger."info":
					putSocket("NOTICE $nick :\002Bot information\002");
					putSocket("NOTICE $nick :\002Bot Uptime         \002:     ".time2str(time() - $stime));
					putSocket("NOTICE $nick :\002Maximal Memory Use \002:     ".round((memory_get_peak_usage()/1024/1024),2)." MBytes");
					putSocket("NOTICE $nick :\002         Right now \002:     ".round((memory_get_usage()/1024/1024),2)." MBytes");
					putSocket("NOTICE $nick :\002Incoming Traffic   \002:     ".round(($glob['dat_in']/1024/1024),2)." MBytes");
					putSocket("NOTICE $nick :\002Outgoing Traffic   \002:     ".round(($glob['dat_out']/1024/1024),2)." MBytes");
					if($gitversion) {
						putSocket("NOTICE $nick :\002Version            \002:     ".$version."  (".$gitversion.")");
					}else{
						putSocket("NOTICE $nick :\002Version            \002:     ".$version);
					}
					putSocket("NOTICE $nick :\002Parser             \002:     ".PHP_VERSION);
					putSocket("NOTICE $nick :\002Code               \002:     ".$codelines." lines PHP code (view it at http://git.nexus-irc.de/?p=NexusStats.git)");
					putSocket("NOTICE $nick :If you found a bug or if you have a good idea report it on http://bugtracker.nexus-irc.de/");
					break;
				case $trigger."google":
					if(isset($exp[4])){
						$kk2 = explode(" ",$fg,4);
						$act2 = explode(" ",@substr($kk2[3], 1),2);
						$google=from_google($act2[1]);
						if(isset($google['responseData']['results'][0]['titleNoFormatting'])){
							privmsg($exp[2],"\002Google\002: ".$google['responseData']['results'][0]['titleNoFormatting'] . " => " . $google['responseData']['results'][0]['url']);
							privmsg($exp[2],"\002Google\002: ".$google['responseData']['results'][1]['titleNoFormatting'] . " => " . $google['responseData']['results'][1]['url']);
							privmsg($exp[2],"\002Google\002: ".$google['responseData']['results'][2]['titleNoFormatting'] . " => " . $google['responseData']['results'][2]['url']);
							privmsg($exp[2],"\002Google\002: ".$google['responseData']['results'][3]['titleNoFormatting'] . " => " . $google['responseData']['results'][3]['url']);
							privmsg($exp[2],"\002Google\002: ".$google['responseData']['results'][4]['titleNoFormatting'] . " => " . $google['responseData']['results'][4]['url']);
						}else{
							privmsg($exp[2],"\002Google\002: Your search - ".$act2[1]." - did not match any documents. ");
						}
					}
					break;
				case $trigger."8ball":
					if(isset($exp[4])){
						$answer=array("Not a chance.","In your dreams.","Absolutely!","Could be, could be.","No!");
						$rand=array_rand($answer);
						privmsg($exp[2],$answer[$rand]);
					}else{
						notice($nick,"8ball requires more parameters.");
					}
					break;
				case $trigger."git":
					git($nick);
					break;
				case $trigger."php":
					if(getauth($nick) == $admin){
						$kk2 = explode(" ",$fg,4);
						$act2 = explode(" ",@substr($kk2[3], 1),2);
						if(count($phpcache) > 5) {
							notice($nick, "too many running php processes at the moment!");
							return;
						}
						$entry=array();
						$entry['channel'] = $exp[2];
						$descriptor = array(0 => array("pipe", "r"),1 => array("pipe", "w"),2 => array("pipe", "w"));
						$entry['proc'] = proc_open('php', $descriptor, $entry['pipes']);
						if(!is_resource($entry['proc'])) {
							notice($nick, "error while loading php!");
							return;
						}
						$entry['time'] = time();
						if(preg_match("#pastebin\.com/([a-zA-Z0-9]*)$#i", $act2[1])) {
							$pasteid = explode("/", $act2[1]);
							$pasteid = $pasteid[count($pasteid)-1];
							$codecontent = file_get_contents("http://pastebin.com/download.php?i=".$pasteid);
							if(preg_match("#Unknown Paste ID!#i", $codecontent)) {
								notice($nick, "Unknown Paste ID!");
								return;
							}
							$code = $codecontent;
						} else {
							$code = "<"."?php " . $act2[1] . " ?".">";
						};
						fwrite($entry['pipes'][0], $code);
						fclose($entry['pipes'][0]);
						$phpcache[] = $entry;
					}
					break;
				case $trigger."c":
					if(getauth($nick) == $admin){
						$kk2 = explode(" ",$fg,4);
						$act2 = explode(" ",@substr($kk2[3], 1),2);
						if(count($ccache) > 5) {
							notice($nick, "too many running c processes at the moment!");
							return;
						}
						$entry=array();
						$entry['channel'] = $exp[2];
						$entry['id'] = rand(1, 999999);
						if(preg_match("#pastebin\.com/([a-zA-Z0-9]*)$#i", $act2[1])) {
							$pasteid = explode("/", $act2[1]);
							$pasteid = $pasteid[count($pasteid)-1];
							$codecontent = file_get_contents("http://pastebin.com/download.php?i=".$pasteid);
							if(preg_match("#Unknown Paste ID!#i", $codecontent)) {
								notice($nick, "Unknown Paste ID!");
								return;
							}
							$code = "#include \"includes.h\"
							".$codecontent;
						} else {
							$code = "#include \"includes.h\"
							".$act2[1];
						};
						$fp = fopen("tmp/debug_".$entry['id'].".c", "w");
						fwrite($fp, $code);
						fclose($fp);
						$err = shell_exec("gcc -o tmp/debug_".$entry['id']." tmp/debug_".$entry['id'].".c 2>&1");
						if($err) {
							$err=str_replace("\r","",$err);
							$lines=explode("\n",$err);
							$i=0;
							foreach($lines as $line) {
								if($line == "") continue;
								$i++;
								if($i>100) {
									privmsg($entry['channel'], "too many lines!");
									break; 
								}
								privmsg($entry['channel'], $line);
							}
						}
						if(!file_exists("tmp/debug_".$entry['id'])) {
							unlink("tmp/debug_".$entry['id'].".c");
							break;
						}
						$descriptor = array(0 => array("pipe", "r"),1 => array("pipe", "w"),2 => array("pipe", "w"));
						$entry['proc'] = proc_open('tmp/debug_'.$entry['id'], $descriptor, $entry['pipes']);
						if(!is_resource($entry['proc'])) {
							notice($nick, "error while loading c!");
							return;
						}
						$entry['time'] = time();
						fclose($entry['pipes'][0]);
						$ccache[] = $entry;
					}
					break;
				case $trigger."slap":
					if(isset($exp[4])) {
						slap($exp[2],$exp[4],$nick);
					}else{
						slap($exp[2],$nick,$nick);
					}
					break;
			}
		}
		break;
	case "invite":
		if($exp[3][0] == ":") {
			$chan = @substr($exp[3], 1);
		}else{
			$chan = $exp[3];
		}
		$a = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($chan)."' AND `Noreg` = '1'");
		$row = mysql_fetch_array($a);
		if($row['Name'] == $chan){
		} else {
			$b = mysql_send_query("SELECT Name FROM `Channel` WHERE `Name` = '".mysql_real_escape_string($chan)."' AND `Noreg` = '0'");
			$row2 = mysql_fetch_array($b);
			if($row2['Name'] == $chan){
				putSocket("join ".$exp[3]);
				who($exp[3], "2");
				send_debug("join ".$exp[3]);
			}else{
				create_chan($exp[3]);
				who($exp[3], "2");
			}
			send_debug("[".$exp[3]."] ".$exp[2]." has been invited by ".$nick."");
		}
		break;
	case "join":
		if($nick == $botnick OR $nick == $botnick."|ZNC") {
		}else{
			if($exp[2][0] == ":") {
				$cha2 = @substr($exp[2], 1);
				$cha = @substr($cha2, 1);
				$chan = @substr($exp[2], 1);
			}else{
				$cha = @substr($exp[2], 1);
				$chan = $exp[2];
			}
			create_log($chan,"[".@date("H:i")."] *** ".$nick." (".$expB[1].") has joined ".$chan);
			if(isset($auth[strtolower($nick)])) { 
				$channeluser[$chan][$nick] = $nick;
			} else {
				who($nick, "1");
				$channeluser[$chan][$nick] = $nick;
			}
		}
		break;
	case "part":
		if($nick == $botnick OR $nick == $botnick."|ZNC") {
		}else{
			if($exp[2][0] == ":") {
				$cha2 = @substr($exp[2], 1);
				$cha = @substr($cha2, 1);
				$chan = @substr($exp[2], 1);
			}else{
				$cha = @substr($exp[2], 1);
				$chan = $exp[2];
			}
			create_log($chan,"[".@date("H:i")."] *** ".$nick." (".$expB[1].") has left ".$chan);
			unset($channeluser[$chan][$nick]);
			if(isonchannel($nick)) { }else{
				unset($auth[strtolower($nick)]);
			}
		}
		break;
	case "mode":
		$cha = @substr($exp[2], 1);
		if($nick == $botnick OR $nick == $botnick."|ZNC" OR $cha == $botnick OR $cha == $botnick."|ZNC" OR $cha == substr($botnick, 1) OR $cha == substr($botnick, 1)."|ZNC") {
		}else{
			create_log($cha,"[".@date("H:i")."] *** ".$nick." sets mode: ".$exp[3]." ".$exp[4]);
		}
		break;
	case "kick":
		if($exp[3] == $botnick OR $exp[3] == $botnick."|ZNC") {
			del_chan($exp[2]);
		}else{
			if($exp[2][0] == ":") {
				$cha2 = @substr($exp[2], 1);
				$cha = @substr($cha2, 1);
				$chan = @substr($exp[2], 1);
			}else{
				$cha = @substr($exp[2], 1);
				$chan = $exp[2];
			}
			$cha = @substr($exp[2], 1);
			create_log($chan,"[".@date("H:i")."] *** ".$exp[3]." was kicked by ".$nick." (".@substr($exp[4], 1).")");
			unset($channeluser[$chan][$exp[3]]);
			if(isonchannel($exp[3])) { }else{
				unset($auth[strtolower($exp[3])]);
			}
		}
		break;
	case "topic":
		$cha = @substr($exp[2], 1);
		$kk = explode(" ",$fg,4);
		create_log($exp[2],"[".@date("H:i")."] *** ".$nick." changes topic to '".@substr($kk[3], 1)."'");
		break;

	case "nick":
		if($nick == $botnick OR $nick == $botnick."|ZNC"){
		}else{
			$result = mysql_send_query("SELECT * FROM `Channel` WHERE `Noreg` = '0'");
			while ( $row = mysql_fetch_array($result) ){
				if(isinchan($nick, $row['Name']) == true) {
					$ni = @substr($exp[2], 1);
					create_log($row['Name'],"[".@date("H:i")."] *** ".$nick." is now known as ".$ni);
					if(isset($auth[strtolower($ni)])) { } else {
						$auth[strtolower($ni)] = $auth[strtolower($nick)];
					}
					unset($auth[strtolower($nick)]);
					$channeluser[$row['Name']][$ni] = $ni;
					unset($channeluser[$row['Name']][$nick]);
				}
			}
		}
		break;
	case "quit":
		if($nick == $botnick OR $nick == $botnick."|ZNC"){
		}else{
			$result = mysql_send_query("SELECT * FROM `Channel` WHERE `Noreg` = '0'");
			while ( $row = mysql_fetch_array($result) ){
				if(isinchan($nick, $row['Name']) == true) {
					create_log($row['Name'],"[".@date("H:i")."] *** ".$nick." (".$expB[1].") Quit (".@substr($exp[2], 1).")");
					unset($auth[strtolower($nick)]);
					unset($channeluser[$row['Name']][$nick]);
				}
			}
		}
		break;
}
?>