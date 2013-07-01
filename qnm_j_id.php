<?php

// QNM 1.0 build:20130410

if ( empty($_GET['term']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }

$s = '*'; if ( isset($_GET['fs']) ) $s = $_GET['fs'];
$t = ''; if ( isset($_GET['ft']) ) $t = $_GET['ft'];
$st = ''; if ( isset($_GET['fst']) ) $st = $_GET['fst'];
if ( $s==='' || $s==='-1' ) $s='*';
if ( $t==='' ) $t='*';
if ( $st==='' ) $st='*';

$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'try other lettres'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];
$e2 = 'try without options'; if ( isset($_GET['e2']) ) $e2 = $_GET['e2'];

$strWhere = 'WHERE uid>0';
if ( $s!=='*' ) $strWhere .= ' AND section='.$s;
if ( $t!=='*' ) $strWhere .= ' AND UPPER(type) LIKE "%'.addslashes(strtoupper($t)).'%"';
if ( $st!=='*' ) $strWhere .= ' AND status='.$st;

include 'bin/class/qt_class_db.php';
include 'bin/config.php';

// query

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) return;

$oDBAJAX->Query( 'SELECT DISTINCT id, type FROM '.$qnm_prefix.'qnmelement '.$strWhere.' AND UPPER(id) like "%'.addslashes(strtoupper($_GET['term'])).'%"' );

// format: result item + result info (as a json array with index "rItem","rInfo" )

$json = array();
while($row=$oDBAJAX->GetRow())
{
  $json[] =array('rItem'=>$row['id'],'rInfo'=>$row['type']);
  if ( count($json)>=10 ) break;
}

// error handling
if ( empty($json) ) $json[]=array('rItem'=>'','rInfo'=>$e0.', '.($s.$t.$st==='***' ? $e1 : $e2));

// response

echo json_encode($json);