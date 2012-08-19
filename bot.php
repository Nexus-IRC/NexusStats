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
include("config.inc.php");
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
            putSocket("JOIN ".$debugchannel); //debug channel
			create_timer("12h","stats");
        }
		include("code.inc.php");
		
    }
}
include("function.inc.php");
?>
