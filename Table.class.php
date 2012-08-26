<?php
/* Table.class.php - NexusStats v2.2
 * Copyright (C) 2011-2012  Philipp Kreil (pk910)
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

class Table {
	private $table;

	public function Table($colums) {
		$this->table = array();
		$this->table['set'] = array();
		$this->table['data'] = array();
		$this->table['set']['col'] = $colums;
		$this->table['set']['bold'] = array();
		for($i = 0; $i < $this->table['set']['col']; $i++) {
			$this->table['set']['max'.$i] = 0;
			$this->table['set']['bold'][$i] = false;
		}
	}

	public function setBold($colum) {
		$this->table['set']['bold'][$colum] = true;
	}

	public function add() {
		$args = func_get_args();
		$row = array();
		for($i = 0; $i < $this->table['set']['col']; $i++) {
			if(count($args) <= $i) $args[$i]= "";
			$row[] = $args[$i];
			if(count($args) >= $i)
			if(strlen($args[$i]) > $this->table['set']['max'.$i]) $this->table['set']['max'.$i] = strlen($args[$i]);
		}
		$this->table['data'][] = $row;
		return true;
	}

	public function end() {
		$space = "                                                                                       ";
		$output = array();
		for($row = 0; $row < count($this->table['data']); $row++) {
			$out = "";
			for($i = 0; $i < $this->table['set']['col']; $i++) {
				if($i < $this->table['set']['col'] - 1)
				$this->table['data'][$row][$i] .= substr($space,0,$this->table['set']['max'.$i] - strlen($this->table['data'][$row][$i]) + 1);
				$bold = $this->table['set']['bold'][$i];
				$out .= ($bold ? "\002" : "").$this->table['data'][$row][$i].($bold ? "\002" : "");
			}
			$output[] = $out;
		}
		return $output;
	}

}

?>