if ($exp[1] == "354") {
			if ($exp[3] == "1") {
				$authre[strtolower($exp[4])] = $exp[5];
			}
		}
		
		if ($exp[1] == "315") {
			$x = explode(",",$exp[3]);
			$target = $x[0];
			$id = $x[1];
			if ($id == 1) {
				if(!$authre[strtolower($target)]){
					$auth = false;
				} else {
					$auth = $authre[strtolower($target)];
				}
			}
		}
		
        if ($exp[1] == "PRIVMSG") {
			$cha = @substr($exp[2], 1);
			$kk = explode(":",$fg,3);
			$act = explode(" ",$kk[2],2);
			if($act[0] == "\001ACTION"){
				if($nick == "NexusStats" OR $nick == "NexusStats|ZNC" OR $cha == "NexusStats" OR $cha == "NexusStats|ZNC" OR $cha == "exusStats" OR $cha == "exusStats|ZNC") {
				}else{
					create_log($cha,"[".@date("H:i")."] * ".$nick." ".$act[1]);
				}
			}elseif($act[0] == "\001VERSION\001"){
				notice($nick,"\001VERSION 1.8 - Parser: ".PHP_VERSION);
			}elseif($act[0] == "\001UPTIME\001"){
				notice($nick,"\001UPTIME ".time2str(time() - $stime));
			}elseif($act[0] == "\001TIME\001"){
				$time = @date('r');
				notice($nick,"\001Time ".$time);
			}elseif($act[0] == "\001PING"){
				$ping = ($act[1] - (60*60*1337 + 42*60));
				notice($nick,"\001PING ".$ping);
			}else{
				if($nick == "NexusStats" OR $nick == "NexusStats|ZNC" OR $cha == "NexusStats" OR $cha == "NexusStats|ZNC" OR $cha == "exusStats" OR $cha == "exusStats|ZNC") {
				}else{
					create_log($cha,"[".@date("H:i")."] <".$nick."> ".$kk[2]);
				}
			}
			
			
            if ($exp[2][0] != "#") {
            } else 
            {
                switch($command) {
                    case "?stats":				
						create_stats($exp[2]);
                        break;
					case "?unreg":
						if($host[1] == $admin){
							if($exp[4]){
								create_noreg($exp[4],$nick);
							}else{
								notice($nick,"Du musst einen Channelnamen angeben");
							}
						}else{
							break;
						}
						break;
					case "?reg":
						if($host[1] == $admin){
							if($exp[4]){
								$inhalt = file_get_contents("/home/stats/noreg.cfg");	
								if ( stristr($inhalt, $exp[4]) == true ) {
									notice($nick,"Der Channel: ".$exp[4]." steht auf der not register liste.");
								} else {						
									putSocket("join ".$exp[4]);
									create_chan($exp[4]);
								}
							}else{
								notice($nick,"Du musst einen Channelnamen angeben");
							}
						}else{
							break;
						}
						break;					
					case "?setlang":
						if($host[1] == $admin){
							if($exp[4]){
								if($exp[5]){
									set_lang($exp[4],$exp[5]);
								}else{
									notice($nick,"Du musst einen Ländercode angeben");
								}	
							}else{
								notice($nick,"Du musst einen Channelnamen angeben");
							}
						}else{
							break;
						}
						break;
					case "?clist":
						if($host[1] == $admin){
							require_once("Table.class.php");
							$datei = "/home/stats/channel.cfg";
							$array = file($datei);
							#print_r($array);
							$table = new Table(2);
							$table->add("Name", "Lang");
							foreach ($array as $element) {
								$a = explode("|",$element);
								$table->add($a[0], $a[1]);
							}
							$lines = $table->end();
							$i = -1;
							foreach($lines as $line) {
								notice($nick, $line);
								$i++;
							}
							notice($nick,"\002".$i."\002 channel gefunden");
						}else{
							break;
						}
						break;
					case "?debug":
						if($host[1] == $admin){
							debug($exp[2]);						
						}else{
							break;
						}
						break;
                }
            }
        }
		if ($exp[1] == "INVITE") {		
			if($exp[3][0] == ":") {
				$chan = @substr($exp[3], 1);
			}else{
				$chan = $exp[3];
			}
			$inhalt = file_get_contents("/home/stats/noreg.cfg");	
			if ( stristr($inhalt, $chan) == true ) {
			} else {
				$inhalt1 = file_get_contents("/home/stats/channel.cfg");	
				if ( stristr($inhalt1, $chan) == true ) {
					putSocket("join ".$exp[3]);
				}else{
					putSocket("join ".$exp[3]);
					create_chan($exp[3]);
				}
			}
		}
        if ($exp[1] == "JOIN") {
			if($nick == "NexusStats" OR $nick == "NexusStats|ZNC") {
			}else{
				if($exp[2][0] == ":") {
					$cha2 = @substr($exp[2], 1);
					$cha = @substr($cha2, 1);
					$chan = @substr($exp[2], 1);
				}else{
					$cha = @substr($exp[2], 1);
					$chan = $exp[2];
				}
				create_log($cha,"[".@date("H:i")."] *** ".$nick." (".$expB[1].") has joined ".$chan);
			}
        }
        if ($exp[1] == "PART") {
			if($nick == "NexusStats" OR $nick == "NexusStats|ZNC") {
			}else{
				if($exp[2][0] == ":") {
					$cha2 = @substr($exp[2], 1);
					$cha = @substr($cha2, 1);
					$chan = @substr($exp[2], 1);
				}else{
					$cha = @substr($exp[2], 1);
					$chan = $exp[2];
				}
				create_log($cha,"[".@date("H:i")."] *** ".$nick." (".$expB[1].") has left ".$chan);
			}
        }
		if ($exp[1] == "MODE") {
			$cha = @substr($exp[2], 1);
			if($nick == "NexusStats" OR $nick == "NexusStats|ZNC" OR $cha == "NexusStats" OR $cha == "NexusStats|ZNC" OR $cha == "exusStats" OR $cha == "exusStats|ZNC") {
			}else{
				create_log($cha,"[".@date("H:i")."] *** ".$nick." sets mode: ".$exp[3]." ".$exp[4]);
			}
		}
		if ($exp[1] == "KICK") {
			if($exp[3] == "NexusStats" OR $exp[3] == "NexusStats|ZNC") {
				putSocket("part ".$exp[2]);
				del_chan($exp[2]);
			}else{
				$cha = @substr($exp[2], 1);
				create_log($cha,"[".@date("H:i")."] *** ".$exp[3]." was kicked by ".$nick." (".@substr($exp[4], 1).")");
			}
		}
		if ($exp[1] == "TOPIC") {
			$cha = @substr($exp[2], 1);
			$kk = explode(":",$fg,3);
			create_log($cha,"[".@date("H:i")."] *** ".$nick." changes topic to '".$kk[2]."'");
		}