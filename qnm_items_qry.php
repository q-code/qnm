<?php

// QNM 1.0 build:20130410

// Uri extra arguments

if ( isset($_GET['v']) ) { $v = trim(urldecode(strip_tags($_GET['v']))); } else { $v=''; }
if ( isset($_GET['v2']) ) { $v2 = trim(urldecode(strip_tags($_GET['v2']))); } else { $v2=''; }
if ( !empty($v) ) $v = str_replace('"','',$v);
if ( !empty($v2) ) $v2 = str_replace('"','',$v2);

switch($q)
{
case 'qs':
case 'id':

  $oVIP->selfname = $L['Search_by_id'];
  break;

case 'kw':

  $oVIP->selfname = $L['Search_by_key'];
  if ( empty($v) ) $error = $L['Keywords'].S.Error(1);
  if ( $v2!=='' ) $v2='1';
  if ( strlen($v)>64 ) die('Invalid argument #v');
  break;

case 'user':

  if ( $v=='' ) $error = 'Userid '.Error(1);
  $v = (int)$v;
  if ( $v<0 ) $error = 'Userid '.Error(1);
  $v2 = trim(urldecode($v2));
  $v2 = str_replace('"','',$v2);
  break;

case 'tag': // time status tags

  $oVIP->selfname = $L['Search_by_tag'];
  if ( empty($v) ) $error = $L['Tags'].' '.Error(1);
  if ( strlen($v)>100 ) die('Invalid argument #v');
  break;

case 'fld':

  if ( $v2!=='address' && $v2!=='descr' ) die('Invalid argument #v2');
  $oVIP->selfname = $L['Search_by_field'].' '.$v2;
  if ( empty($v) ) $error = $L['Field'].' '.Error(1);
  if ( strlen($v)>64 ) die('Invalid argument #v');
  break;

case 'date':

  if ( $v2!=='on' && $v2!=='before' && $v2!=='after' && $v2!=='near' ) die('Invalid argument #v2');
  $oVIP->selfname = $L['Search_by_date'].' '.$v2;
  if ( empty($v) ) $error = $L['Date'].' '.Error(1);
  if ( strlen($v)>10 ) die('Invalid argument #v');
  break;

case 'rel':

  if ( $v!=='1' && $v!=='1*' && $v!=='2*' && $v!=='3*' && $v!=='4*' ) $v2='0';
  $oVIP->selfname = L('Items').' '.L('having').' '.L('relation',$v);
  break;

case 'sub':

  if ( $v!=='1' && $v!=='1*' && $v!=='2*' && $v!=='3*' && $v!=='4*' ) $v2='0';
  $oVIP->selfname =  L('Items').' '.L('having').' '.L('sub-item',$v);
  break;

}

// stop if error

if ( !empty($error) ) $oHtml->PageBox(NULL,$error,$_SESSION[QT]['skin_dir'],0);

// end intialise

$intLimit = ($intPage-1)*$_SESSION[QT]['items_per_page'];

// QUERY DEFINITION

// Section option (and check if ref exists)

if ( $s!=='*' ) $strWhere .= ' AND e.section='.$s;
if ( $ft!=='*' && strlen($ft)>0 ) $strWhere .= ' AND UPPER(e.type) LIKE "%'.strtoupper($ft).'%"';
if ( $fst!=='*' ) $strWhere .= ' AND e.status='.$fst;

// Format main argument $v as array of values

$arrV = array();
$arrVlbl = array();
if ( strlen(trim($v))>0 )
{
$arr = explode(QNM_QUERY_SEPARATOR,strtoupper(trim($v)));
foreach($arr as $str) { $str=trim($str); if ( $str!=='' ) $arrV[]=$str; }
$arrV = array_unique($arrV);
}

switch($q)
{
case 'user': $arrVlbl = array('"'.$v2.'"'); break;
default:     $arrVlbl = array_map( create_function('$n','return \'"\'.$n.\'"\';'), $arrV);  break;
}

// Query definition

switch($q)
{
case 'qs':
case 'id':

  if ( count($arrV)>0 )
  {
    for($i=0;$i<count($arrV);$i++)
    {
    if ( substr($arrV[$i],0,5)=='TYPE:') { $arrV[$i]='UPPER(e.type) LIKE "%'.substr($arrV[$i],5).'%"'; } else { $arrV[$i]='UPPER(e.id) LIKE "%'.$arrV[$i].'%"'; }
    }
    $strWhere .= ' AND ('.implode(' OR ',$arrV).')';
  }
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'kw':

  // full word criteria

  for($i=0;$i<count($arrV);$i++)
  {
    switch ($oDB->type)
    {
    case 'mssql':
    case 'sqlsrv': $arrV[$i] = 'UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    case 'db2':    $arrV[$i] = 'UPPER(p.textmsg2) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    default:       $arrV[$i] = 'UPPER(p.textmsg) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    }
  }
  $strFields .= ',p.id as postid,p.textmsg,p.issuedate,p.username,p.status as poststatus';
  $strFrom   = ' FROM '.TABPOST.' p INNER JOIN '.TABNE.' e ON p.pid=e.uid';
  $strWhere .= ' AND p.status'.(empty($v2) ? '>=0' : '=1').' AND ('.implode(' OR ',$arrV).')';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'last':

  // get the lastpost date

  $oDB->Query('SELECT max(p.issuedate) as f1 FROM '.TABPOST.' p ');
  $row = $oDB->Getrow();
  if ( empty($row['f1']) ) $row['f1'] = date('Ymd');
  $strDate = DateAdd($row['f1'],-7,'day');

  // query post of this day

  $strFields .= ',p.id as postid,p.textmsg,p.issuedate,p.username,p.status as poststatus';
  $strFrom   = ' FROM '.TABPOST.' p INNER JOIN '.TABNE.' e ON p.pid=e.uid';
  $strWhere .= ' AND p.status>=0 AND ';
  switch($oDB->type)
  {
  case 'mysql4':
  case 'mysql':
  case 'sqlsrv':
  case 'mssql': $strWhere .= 'LEFT(p.issuedate,8)>"'.$strDate.'"'; break;
  case 'pg':    $strWhere .= 'SUBSTRING(p.issuedate,1,8)>"'.$strDate.'"'; break;
  case 'ibase': $strWhere .= 'SUBSTRING(p.issuedate FROM 1 FOR 8)>"'.$strDate.'"'; break;
  case 'sqlite':
  case 'db2':
  case 'oci':   $strWhere .= 'SUBSTR(p.issuedate,1,8)>"'.$strDate.'"'; break;
  default: die('Unknown db type '.$oDB->type);
  }
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'user':

  for($i=0;$i<count($arrV);$i++)
  {
    $arrV[$i]='p.userid='.$arrV[$i];
  }
  $strFields .= ',p.id as postid,p.textmsg,p.issuedate,p.username,p.status as poststatus';
  $strFrom    = ' FROM '.TABNE.' e INNER JOIN '.TABPOST.' p ON p.pid=e.uid ';
  $strWhere  .= ' AND p.status>=0 AND ('.implode(' OR ',$arrV).')';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'tag':

  // full word criteria

  for($i=0;$i<count($arrV);$i++)
  {
    switch ($oDB->type)
    {
    case 'mssql':$arrV[$i] = 'UPPER(CAST(e.tags AS VARCHAR(2000))) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    default:     $arrV[$i] = 'UPPER(e.tags) LIKE "%'.strtoupper($arrV[$i]).'%"'; break;
    }
  }
  $strWhere .= ' AND ('.implode(' OR ',$arrV).')';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'fld':

  // full word criteria

  for($i=0;$i<count($arrV);$i++)
  {
    if ( $v2=='descr' && ($oDB->type=='sqlsrv' || $oDB->type=='mssql') )
    {
      $arrV[$i] =  'UPPER(CAST(e.descr AS VARCHAR(2000))) LIKE "%'.strtoupper($arrV[$i]).'%"';
    }
    else
    {
      $arrV[$i] =  'UPPER(e.'.$v2.') LIKE "%'.strtoupper($arrV[$i]).'%"';
    }
  }
  $strWhere .= ' AND ('.implode(' OR ',$arrV).')';
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'date':

  $date=str_replace('-','',trim($arrV[0]));
  $date=substr($date,0,8);
  switch($v2)
  {
  case 'on':
    if ( strlen($date)==8 ) $strWhere .= ' AND e.insertdate="'.$date.'"';
    if ( strlen($date)==6 ) $strWhere .= ' AND e.insertdate>="'.$date.'01" AND e.insertdate<="'.$date.'31"';
    if ( strlen($date)==4 ) $strWhere .= ' AND e.insertdate>="'.$date.'0101" AND e.insertdate<="'.$date.'1231"';
    break;
  case 'after':
    if ( strlen($date)==8 ) $strWhere .= ' AND e.insertdate>="'.$date.'"';
    if ( strlen($date)==6 ) $strWhere .= ' AND e.insertdate>="'.$date.'01"';
    if ( strlen($date)==4 ) $strWhere .= ' AND e.insertdate>="'.$date.'0101"';
    break;
  case 'near':
    if ( strlen($date)==8 ) $strWhere .= ' AND (e.insertdate>="'.DateAdd($date,-3,'day').'" AND e.insertdate<="'.DateAdd($date,3,'day').'")';
    if ( strlen($date)==6 ) { $date .='01'; $strWhere .= ' AND (e.insertdate>="'.DateAdd($date,-1,'month').'" AND e.insertdate<="'.DateAdd($date,1,'month').'")'; }
    if ( strlen($date)==4 ) { $date = (int)$date; $strWhere .= ' AND (e.insertdate>="'.($date-1).'0101" AND e.insertdate<="'.($date+1).'1231")'; }
    break;
  default:
    if ( strlen($date)==8 ) $strWhere .= ' AND e.insertdate>"1900" AND e.insertdate<="'.$date.'"';
    if ( strlen($date)==6 ) $strWhere .= ' AND e.insertdate>"1900" AND e.insertdate<="'.$date.'01"';
    if ( strlen($date)==4 ) $strWhere .= ' AND e.insertdate>"1900" AND e.insertdate<="'.$date.'0101"';
  }
  $strCount = 'SELECT count(*) as countid'.$strFrom.$strWhere;
  break;

case 'rel':

  // number of relation and type
  $str='';
  if ( $v!=='0' )
  {
  if ( in_array($v2,array('0','1','2','-1')) ) $str = ' AND l.ldirection='.$v2;
  if ( $v2==='3' ) $str = ' AND l.ldirection<>0';
  if ( $v2==='4' ) $str = ' AND l.ldirection<>1';
  if ( $v2==='5' ) $str = ' AND l.ldirection<>2';
  if ( $v2==='6' ) $str = ' AND l.ldirection<>-1';
  }
  $strFields .= ',(SELECT count(l.lid2) FROM '.TABNL.' l WHERE l.lid=e.uid AND l.lclass="c"'.$str.') as countlink';
  $strFrom   = ' FROM '.TABNE.' e';
  $strWhere .= ' HAVING countlink'.(strlen($v)>1 ? '>=' : '=').substr($v,0,1);
  $strCount = 'SELECT count(*) as countid FROM (SELECT e.uid,(SELECT count(l.lid2) FROM '.TABNL.' l WHERE l.lid=e.uid AND l.lclass="c"'.$str.') as countlink'.$strFrom.$strWhere.') as t';
  break;

case 'sub':

  // number of sub-item and type
  $str='';
  if ( $v!=='0' )
  {
  if ( in_array($v2,array('e','l','c')) ) $str = ' AND l.lid2class="'.$v2.'"';
  if ( in_array($v2,array('-e','-l','-c')) ) $str = ' AND l.lid2class<>"'.$v2.'"';
  }
  $strFields .= ',(SELECT count(l.lid2) FROM '.TABNL.' l WHERE l.lid=e.uid AND l.lclass="e"'.$str.') as countlink';
  $strFrom   = ' FROM '.TABNE.' e';
  $strWhere .= ' HAVING countlink'.(strlen($v)>1 ? '>=' : '=').substr($v,0,1);
  $strCount = 'SELECT count(*) as countid FROM (SELECT e.uid,(SELECT count(l.lid2) FROM '.TABNL.' l WHERE l.lid=e.uid AND l.lclass="e") as countlink'.$strFrom.$strWhere.') as t';
  break;
}
