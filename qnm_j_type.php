<?php

// QNM 1.0 build:20130410
if ( !isset($_GET['term']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }

$s = '*'; if ( isset($_GET['fs']) ) $s = $_GET['fs'];
$class='*'; if ( isset($_GET['fc']) ) $class = $_GET['fc'];
$status='*'; if ( isset($_GET['fst']) ) $status = $_GET['fst'];
$y='*'; if ( isset($_GET['fy']) ) $y = $_GET['fy'];
if ( $s==='' || $s==='-1' ) $s='*';
if ( $class==='' ) $class='*';
if ( $status==='' ) $status='*';
if ( $y==='' ) $y='*';
$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'try other lettres'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];
$e2 = 'try without options'; if ( isset($_GET['e2']) ) $e2 = $_GET['e2'];
$strWhere = 'WHERE uid>0';
if ( $s!=='*' ) $strWhere .= ' AND e.section='.$s;
if ( $class!=='*' ) $strWhere .= ' AND e.class="'.$class.'"';
if ( $status!=='*' ) $strWhere .= ' AND e.status='.$status;
if ( $y!=='*' ) $strWhere .= ' AND e.insertdate>="'.$y.'0101" AND e.insertdate<="'.$y.'1231"';

include 'bin/class/qt_class_db.php';
include 'bin/config.php';

// query

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) return;

if ( strlen($_GET['term'])==0 )
{
  $oDBAJAX->Query( 'SELECT e.type,count(*) as counttype FROM '.$qnm_prefix.'qnmelement e '.$strWhere.' AND e.type<>"" GROUP BY e.type' );
}
else
{
  $oDBAJAX->Query( 'SELECT e.type,count(*) as counttype FROM '.$qnm_prefix.'qnmelement e '.$strWhere.' AND UPPER(e.type) like "%'.addslashes(strtoupper($_GET['term'])).'%" GROUP BY e.type' );
}

// format: result item + result info (as a json array with index "rItem","rInfo" )

$json = array();
while($row=$oDBAJAX->GetRow())
{
  $json[] =array('rItem'=>$row['type'],'rInfo'=>$row['counttype']);
  if ( count($json)>=10 ) break;
}

// error handling

if ( empty($json) ) $json[]=array('rItem'=>'','rInfo'=>$e0.', '.($s.$class.$status==='***' ? $e1 : $e2));

// response

echo json_encode($json);