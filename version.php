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

// RUN THIS SCRIPT FIRST
if($createversion ==true){
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
		$maincode = str_replace('$createversion=true;', '$createversion=false;', $maincode);
		$maincode = str_replace('$codelines="";', '$codelines="'.substr($codelines, 0, -1).'";', $maincode);
		$maincode = str_replace('$creation="";', '$creation="'.substr($creation, 0, -1).'";', $maincode);
		$fp = fopen("config.inc.php", 'w');
		fwrite($fp, $maincode);
		fclose($fp);
	}else{
		$maincode=file_get_contents("config.inc.php");
		$maincode = str_replace('$createversion=true;', '$createversion=false;', $maincode);
		$maincode = str_replace('$codelines="";', '$codelines="'.substr($codelines, 0, -1).'";', $maincode);
		$maincode = str_replace('$creation="";', '$creation="'.substr($creation, 0, -1).'";', $maincode);
		$fp = fopen("config.inc.php", 'w');
		fwrite($fp, $maincode);
		fclose($fp);
	}
	echo "Please start the bot again\n";
	exit(0);
}
 ?>