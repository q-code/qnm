<?php

// QNM 1.0 build:20130410

if ( empty($_GET['term']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }
if ( empty($_GET['v2']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }

$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'try other lettres'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];
$e2 = 'try without options'; if ( isset($_GET['e2']) ) $e2 = $_GET['e2'];
$v2 = 'descr'; if ( isset($_GET['v2']) ) $v2 = $_GET['v2'];

$s = '*'; if ( isset($_GET['fs']) ) $s = $_GET['fs'];
$t = '*'; if ( isset($_GET['ft']) ) $t = $_GET['ft'];
$st = '*'; if ( isset($_GET['fst']) ) $st = $_GET['fst'];
if ( $s==='' || $s==='-1' ) $s='*';
if ( $t==='' ) $t='*';
if ( $st==='' ) $st='*';

$strWhere = 'WHERE e.uid>0';
if ( $s!=='*' ) $strWhere .= ' AND e.section='.$s;
if ( $t!=='*' ) $strWhere .= ' AND UPPER(e.type) LIKE "%'.addslashes(strtoupper($t)).'%"';
if ( $st!=='*' ) $strWhere .= ' AND e.status='.$st;

include 'bin/class/qt_class_db.php';
include 'bin/config.php';

// query

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) return;

if ( $v2=='descr' && ($oDBAJAX->type=='sqlsrv' || $oDBAJAX->type=='mssql') )
{
  $strWhere .= ' AND UPPER(CAST(e.descr AS VARCHAR(2000))) LIKE "%'.addslashes(strtoupper($_GET['term'])).'%"';
}
else
{
$strWhere .= ' AND UPPER(e.'.$v2.') LIKE "%'.addslashes(strtoupper($_GET['term'])).'%"';
}

$oDBAJAX->Query( 'SELECT e.'.$v2.' as textmsg, e.id FROM '.$qnm_prefix.'qnmelement e '.$strWhere );

$arr = array();
while($row=$oDBAJAX->GetRow())
{
  $n = stripos($row['textmsg'],$_GET['term']);
  if ($n>10) { $n-=10; } else { $n=0; }
  $str = substr($row['textmsg'],$n,20);
  if ( isset($arr[$str]) )
  {
  if ( substr($arr[$str],-3)!='...' ) $arr[$str] .= ',...';
  }
  else
  {
  $arr[$str] = $row['id'];
  }
  if ( count($arr)>8 ) break;
}

// format: result item + result info (as a json array with index "rItem","rInfo" )

$json = array();
if ( count($arr)==0 )
{
  $json[]=array('rItem'=>'','rInfo'=>$e0.', '.($s.$t.$st==='***' ? $e1 : $e2));
}
else
{
  foreach($arr as $key=>$id) $json[]=array('rItem'=>$key,'rInfo'=>$id);
}

// response

echo json_encode($json);