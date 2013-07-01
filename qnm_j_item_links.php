<?php

// QNM 1.0 build:20130410

include 'bin/qt_lib_txt.php';
include 'bin/class/qt_class_db.php';
function jFormat($str)
{
  if ( trim($str)==='' || is_null($str) ) return '<null>';
  $str = str_replace('|',':',$str);
  if ( strlen($str)>50 ) $str = substr($str,0,48).'...';
  return $str;
}

$flds = strip_tags($_POST['flds']);
  $fldsConn = str_replace('e.posts','0 as posts',$flds);
  $fldsConn = str_replace('e.links','0 as links',$fldsConn);
  $fldsConn = str_replace('e.items+e.conns as nec','0 as nec',$fldsConn);
$e_class = 'e';
$e_uid = (int)$_POST['uid'];
$options = strip_tags($_POST['options']);

include 'bin/config.php';

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) exit;

// query links: (3)parent, (2)relations, (1)embeded e/c
$oDBAJAX->Query(
'(SELECT 3 as link,0 as ldirection,'.$flds.' FROM '.$qnm_prefix.'qnmelement e INNER JOIN '.$qnm_prefix.'qnmlink l ON e.uid=l.lid WHERE l.lidclass="e" AND l.lid>0 AND l.lclass="e" AND l.lid2class="'.$e_class.'" AND l.lid2='.$e_uid.')
UNION
(SELECT 2 as link,l.ldirection,'.$flds.' FROM '.$qnm_prefix.'qnmelement e INNER JOIN '.$qnm_prefix.'qnmlink l ON e.uid=l.lid2 WHERE l.lidclass="'.$e_class.'" AND l.lid='.$e_uid.' AND lclass="c" AND l.lid2class="e" AND l.lid2>0 )
UNION
(SELECT 1 as link,l.ldirection,'.$fldsConn.' FROM '.$qnm_prefix.'qnmconn e LEFT JOIN '.$qnm_prefix.'qnmlink l ON e.uid=l.lid AND e.class=l.lidclass WHERE e.pid='.$e_uid.' AND e.id<"~" AND e.class="c")
UNION
(SELECT 1 as link,0 as ldirection,'.$flds.' FROM '.$qnm_prefix.'qnmelement e WHERE e.pid='.$e_uid.')
ORDER BY link DESC,id ASC'
);

//output the response

$strOutput = '';
$i=0;
while($row=$oDBAJAX->GetRow())
{
  $strLine = '';
  foreach($row as $strValue) $strLine .= (empty($strLine) ? '' : '|').jFormat($strValue);
  $strOutput .= (empty($strOutput) ? '' : '||').$strLine;
  if ( $i>20 ) break;
  $i++;
}

echo $strOutput;