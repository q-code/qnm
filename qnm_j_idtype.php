<?php

// QNM 1.0 build:20130410

if ( empty($_GET['term']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }
$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'try other lettres'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];

include 'bin/class/qt_class_db.php';
include 'bin/config.php';

// query

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) return;

$oDBAJAX->Query('
(SELECT DISTINCT id, type as name FROM '.$qnm_prefix.'qnmelement WHERE uid>0 AND UPPER(id) like "%'.addslashes(strtoupper($_GET['term'])).'%")
UNION
(SELECT type as id, "*" as name FROM '.$qnm_prefix.'qnmelement WHERE uid>0 AND UPPER(type) like "%'.addslashes(strtoupper($_GET['term'])).'%")
');

// format:  result item + result info (as a json array with index "rItem","rInfo" )

$iItems=0; // count items (max 10)
$iTypes=0; // count types (max 10)
$json = array();
while($row=$oDBAJAX->GetRow())
{
  if ( $row['name']=='*' )
  {
    $iTypes++; if ( $iTypes>10 ) continue;
  }
  else
  {
    $iItems++; if ( $iItems>10 ) continue;
  }
  $json[] =array('rItem'=>$row['id'],'rInfo'=>$row['name']);
  if ( count($json)>=12 ) break;
}

// error handling
if ( empty($json) ) $json[]=array('rItem'=>'','rInfo'=>$e0.', '.$e1);

// response
echo json_encode($json);
