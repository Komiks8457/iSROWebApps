<?php
include 'adodb.inc.php';
//include 'adodb-exceptions.inc.php';
//error_reporting(E_ALL & ~E_DEPRECATED);
$driver = 'mysqli';
$host = 'localhost';
$user = 'root';
$password = 'C0yote71';
$database = 'bugtracker';

$sql = "SELECT SQL_CALC_FOUND_ROWS id, summary FROM mantis_bug_table order by id";

$db = NewADOConnection($driver);
$db->connect($host, $user, $password, $database);
$db->setFetchMode(ADODB_FETCH_ASSOC);
//$db->pageExecuteCountRows = false;
$r = $db->PageExecute($sql, 5, 1);
print_r($r->getAll());
echo "first: " . (int)$r->atFirstPage() . PHP_EOL;
echo "last: " . (int)$r->atLastPage() . PHP_EOL;
echo $r->LastPageNo();
