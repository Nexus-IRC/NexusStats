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
echo("#### version 1.9-public   ####\n");
echo("#### coded by Stricted    ####\n");
echo("##############################\n");
/* config start */
$server 		= "localhost";
$port 			= "8001";
$botnick 		= "NexusStats";
$pass 			= "NexusStats:xxxx";
$admin 			= "Stricted2.user.OnlineGamesNet";
$logdir 		= "/home/stats/pisg-0.73/log/";
$cfgdir			= "/home/stats/pisg-0.73/cfg/";
$statsdir		= "/var/customers/webs/nexus/stats/chan/";
$archivdir		= "/var/customers/webs/nexus/stats/archiv/";
$pisgdir		= "/home/stats/pisg-0.73/";
$botdir			= "/home/stats/";
$url			= "http://stats.nexus-irc.de/?c=";
$aurl			= "http://stats.nexus-irc.de/?ac=";
$defaultlang 	= "EN";
$trigger		= "?";
/* config end */
set_time_limit(0);
$socket = fsockopen($server,$port,$errstr,$errno,2);
$dltimer = array();
$timer = time();
$stime = time();
$fgr = "";
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
		$fgr = "";
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
		global $fgr;
		$fg = utf8_decode(str_replace("\r","",str_replace("\n","",$fg)));
		$fgr = $fg
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
            #putSocket("JOIN #nexus"); //debug code
			create_timer("12h","stats");
        }
		eval(file_get_contents("code.php"));
		
    }
}
include("function.php");
?>
