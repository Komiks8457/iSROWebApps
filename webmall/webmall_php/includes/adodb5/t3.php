<?php
include 'adodb.inc.php';
$db = adonewconnection('mysqli');
$db->connect('localhost', 'root', 'C0yote71', 'bugtracker');

//test('select * from mantis_project_table');
test('select * from mantis_project_table where id = ?', [1,2]);
//test('select * from xxx');

function test($sql, $param = null) {
	global $db;
	foreach([true, -1, -99, 99] as $mode) {
		echo "######## DEBUG MODE $mode\n";
		$db->debug = $mode;
		if ($param) {
			$db->query($sql, $param);
		} else {
			$db->query($sql);
		}
	}
}
