<?php

// QNM 1.0 build:20130410

if ( empty($_GET['term']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }
$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'try other lettres'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];

$strRole = ''; if ( isset($_GET['r']) ) $strRole = strtoupper($_GET['r']);
if ( $strRole=='A' ) $strRole = 'role="A" AND ';
if ( $strRole=='M' ) $strRole = '(role="A" OR role="M") AND ';

$arrValue = array();

include 'bin/class/qt_class_db.php';
include 'bin/config.php';

// query

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) return;

$oDBAJAX->Query('SELECT name,role FROM '.$qnm_prefix.'qnmuser WHERE '.$strRole.' UPPER(name) like "%'.addslashes(strtoupper($_GET['term'])).'%"');

// format: result item + result info (as a json array with index "rItem","rInfo" )

$json = array();
while($row=$oDBAJAX->GetRow())
{
  $json[] =array('rItem'=>$row['name'],'rInfo'=>$row['role']);
  if ( count($json)>=10 ) break;
}

// error handling
if ( empty($json) ) $json[]=array('rItem'=>'','rInfo'=>$e0.', '.$e1);

// response

echo json_encode($json);