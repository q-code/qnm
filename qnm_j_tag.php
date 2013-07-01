<?php

// QNM 1.0 build:20130410

if ( empty($_GET['term']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }

$e0 = 'No result'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];
$e1 = 'try other lettres'; if ( isset($_GET['e1']) ) $e1 = $_GET['e1'];
$e2 = 'try without options'; if ( isset($_GET['e2']) ) $e2 = $_GET['e2'];
$e3 = 'All categories'; if ( isset($_GET['e3']) ) $e3 = $_GET['e3'];
$e4 = 'Impossible'; if ( isset($_GET['e4']) ) $e4 = $_GET['e4'];
$e5 = 'Category not yet used'; if ( isset($_GET['e5']) ) $e5 = $_GET['e5'];
if ( $_GET['term']=='*' && !isset($_GET['addtags']) ) { echo '*|',$e3,PHP_EOL; return; }
if ( substr( $_GET['term'],0,1)=='*' ) { echo $e4,'|',$e1,PHP_EOL; return; }

$s = '*'; if ( isset($_GET['fs']) ) $s = $_GET['fs'];
$class='*'; if ( isset($_GET['fc']) ) $class = $_GET['fc'];
$type = '*'; if ( isset($_GET['ft']) ) $type = $_GET['ft'];
$status = '*'; if ( isset($_GET['fst']) ) $status = $_GET['fst'];
$y = '*'; if ( isset($_GET['fy']) ) $y = $_GET['fy'];

if ( $s==='' || $s==='-1' ) $s='*';
if ( $class==='' || $class==='-1' ) $class='*';
if ( $type==='' ) $type='*';
if ( $status==='' ) $status='*';
if ( $y==='' ) $y='*';

$lang = 'en'; if ( isset($_GET['lang ']) ) $lang  = $_GET['lang'];
$uids = '';  if ( isset($_GET['uids']) ) $uids = $_GET['uids']; // optional list of uids to search in
$sids = '';  if ( isset($_GET['sids']) ) $sids = $_GET['sids']; // optional list of sections to search in
$strWhere = 'WHERE e.uid>0';
if ( $s!=='*' ) $strWhere .= ' AND e.section='.$s;
if ( $class!=='*' ) $strWhere .= ' AND e.class="'.$class.'"';
if ( !empty($uids) ) $strWhere .= ' AND e.uid IN ('.$uids.')';
if ( !empty($sids) ) $strWhere .= ' AND e.section IN ('.$sids.')';
if ( $type!=='*' ) $strWhere .= ' AND UPPER(e.type) LIKE "%'.addslashes(strtoupper($type)).'%"';
if ( $status!=='*' ) $strWhere .= ' AND e.status='.$status;
if ( $y!=='*' ) $strWhere .= ' AND e.insertdate>="'.$y.'0101" AND e.insertdate<="'.$y.'1231"';


include 'bin/class/qt_class_db.php';
include 'bin/config.php';

// query

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) return;

$oDBAJAX->Query( 'SELECT e.tags, e.id FROM '.$qnm_prefix.'qnmelement e '.$strWhere.' AND UPPER(e.tags) LIKE "%'.addslashes(strtoupper( $_GET['term'])).'%"' );

$arr=array();
while($row=$oDBAJAX->GetRow())
{
  $arrTags=explode(';',$row['tags']);
  foreach($arrTags as $str)
  {
    if ( stripos($str, $_GET['term'])!==false )
    {
      if ( isset($arr[$str]) )
      {
      if ( substr($arr[$str],-3)!='...' ) $arr[$str] .= ',...';
      }
      else
      {
      $arr[$str] = $row['id'];
      }
    }
  }
  if ( count($arr)>8 ) break;
}
// search in predefined tags
if ( count($arr)<10 && $s!=='*' )
{
  require_once 'bin/qnm_fn_tags.php';
  // search matching in section tags
  $arrTags = TagsRead($lang,$s);


  foreach($arrTags as $str=>$strDesc)
  {
    if ( stripos($str, $_GET['term'])!==false ) $arr[$str] = substr($strDesc,0,64);
    if ( count($arr)>10 ) break;
  }
  // search matching in common tags
  if ( count($arr)<10 )
  {
    $arrTags = TagsRead($lang,'*');
    foreach($arrTags as $str=>$strDesc)
    {
    if ( stripos($str, $_GET['term'])!==false ) $arr[$str] = substr($strDesc,0,64);
    if ( count($arr)>10 ) break;
    }
  }
}

// format: result item + result info (as a json array with index "rItem","rInfo" )

$json = array();
if ( count($arr)==0 )
{
  if ( isset($_GET['addtags']) )
    $json[]=array('rItem'=>'','rInfo'=>$e5);
  else
    $json[]=array('rItem'=>'','rInfo'=>$e0.', '.($s.$type.$class.$status==='****' ? strtolower($e1) : strtolower($e2)));
}
else
{
  foreach($arr as $key=>$id) $json[]=array('rItem'=>$key,'rInfo'=>$id);
}

// response
echo json_encode($json);