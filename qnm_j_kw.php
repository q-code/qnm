<?php

// QNM 1.0 build:20130410

if ( empty($_GET['term']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }

$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'try other lettres'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];
$e2 = 'try without options'; if ( isset($_GET['e2']) ) $e2 = $_GET['e2'];
$s = '*'; if ( isset($_GET['fs']) ) $s = $_GET['fs'];
$t = '*'; if ( isset($_GET['ft']) ) $t = $_GET['ft'];
$st = '*'; if ( isset($_GET['fst']) ) $st = $_GET['fst'];
if ( $s==='' || $s==='-1' ) $s='*';
if ( $t==='' ) $t='*';
if ( $st==='' ) $st='*';
$v2 = 0; if ( isset($_GET['v2']) ) $v2 = ($_GET['v2']=='true' ? 1 : 0); // 1=notes in process only, 0=search in notes closed + in process

$strWhere = 'WHERE e.uid>0';
if ( $s!=='*' ) $strWhere .= ' AND e.section='.$s;
if ( $t!=='*' ) $strWhere .= ' AND UPPER(e.type) LIKE "%'.addslashes(strtoupper($t)).'%"';
if ( $st!=='*' ) $strWhere .= ' AND e.status='.$st;
if ( $v2>0 ) $strWhere .= ' AND p.status=1';
if ( $v2==0 ) $strWhere .= ' AND p.status>=0'; // status 0 or 1 (in-process or closed)
if ( $v2<0 ) $strWhere .= ' AND p.status<0'; // status -1 = deleted

include 'bin/class/qt_class_db.php';
include 'bin/config.php';

// query

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) return;

switch ($oDBAJAX->type)
{
case 'mssql':$strWhere .= ' AND UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE "%'.addslashes(strtoupper($_GET['term'])).'%"'; break;
case 'db2':  $strWhere .= ' AND UPPER(p.textmsg2) LIKE "%'.addslashes(strtoupper($_GET['term'])).'%"'; break;
default:     $strWhere .= ' AND UPPER(p.textmsg) LIKE "%'.addslashes(strtoupper($_GET['term'])).'%"'; break;
}
$oDBAJAX->Query( 'SELECT p.textmsg, e.id FROM '.$qnm_prefix.'qnmelement e INNER JOIN '.$qnm_prefix.'qnmpost p ON p.pid=e.uid '.$strWhere );

$arr = array();
while($row=$oDBAJAX->GetRow())
{
  $n = stripos($row['textmsg'],$_GET['term']);
  if ($n>10) { $n-=10; } else { $n=0; }
  $str = substr($row['textmsg'],$n,20);
  $str = trim(strtr($str,',;.','   '));
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