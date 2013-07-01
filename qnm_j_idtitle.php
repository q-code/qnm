<?php

// QNM 1.0 build:20130410

if (!isset($_GET['q']) ) return;
if (empty($_GET['q']) ) return;
$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'try other lettres'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];

include('bin/class/qt_class_db.php');
include('bin/config.php');

// query

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) return;

$oDBAJAX->Query('
(SELECT DISTINCT id, type as name FROM '.$qnm_prefix.'qnmelement WHERE UPPER(id) like "%'.addslashes(strtoupper($_GET['q'])).'%")
UNION
(SELECT type as id, "*" as name FROM '.$qnm_prefix.'qnmelement WHERE UPPER(type) like "%'.addslashes(strtoupper($_GET['q'])).'%")
');

$i=0;
while($row=$oDBAJAX->GetRow())
{
  echo $row['id'],'|',$row['name'],PHP_EOL;
  $i++;
  if ( $i>9 ) break;
}
if ( $i==0)
{
  echo '?|',$e0,', ',$e1,PHP_EOL;
}