<?php

// QNM 1.0 build:20130410

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('V4') ) HtmlPage(11);

include Translate('qnm_stat.php');
include 'bin/qnm_fn_sql.php';

// --------
// INITIALISE
// --------

$strCSV = '';

$s = -1;   // section filter
$y = date('Y'); if ( intval(date('n'))<2 ) $y--; // year filter
$type = ''; // type filter
$tag = ''; // tags filter
$tt = 'g'; // tab: g=global, gt=globaltrend, d=detail, dt=detailtrend
$ch = array('time'=>'m','type'=>'b','value'=>'a','trend'=>'a'); // chart parameters
// [0] blocktime: m=month, q=quarter, d=10days
// [1] graph type: b=bar, l=line, B=bar+variation, L=line+variation
// [2] graphics reals: a=actual, p=percent
// [3] trends reals: a=actual, p=percent

// --------
// SUBMITTED
// --------

QThttpvar('y s type tag tt','int int str str str',true,true,false);

// Check if data selected (only for user defined count stat)

if ( $tt=='n' ) {
if ( !isset($_SESSION[QT]['statF'][0]) ) {
  $oVIP->exiturl = 'qnm_stats.php';
  $oHtml->PageBox('CSV','No data...',$_SESSION[QT]['skin_dir'],3);
}}

// Check submitted value

if ( $s>=0 ) { $strSection = 'section='.$s.' AND '; } else { $strSection=''; }
if ( !empty($type) ) { $strType='type="'.strtoupper(substr($type,0,1)).'" AND '; } else { $strType=''; }
$strTags = '';
if ( !empty($tag) )
{
  if ( substr($tag,-1,1)==';' ) $tag = substr($tag,0,-1);
  $arrTags = explode(';',$tag);
  $str = '';
  foreach($arrTags as $strTag)
  {
  if ( !empty($str) ) $str .= ' OR ';
  $str .= 'UPPER(tags) LIKE "%'.strtoupper($strTag).'%"';
  }
  if ( !empty($str) ) $strTags = ' ('.$str.') AND ';
}
if ( isset($_GET['ch']) )
{
  $str = strip_tags($_GET['ch']);
  if ( strlen($str)>0 ) $ch['time'] = substr($str,0,1); // blocktime
  if ( strlen($str)>1 ) $ch['type'] = substr($str,1,1); // graph type
  if ( strlen($str)>2 ) $ch['value'] = substr($str,2,1); // value type
  if ( strlen($str)>3 ) $ch['trend'] = substr($str,3,1); // trends value type
}

// ------
// OUTPUT
// ------

if ( $tt=='gt' || $tt=='dt' ) { $arrYears = array($y-1,$y); } else { $arrYears = array($y); } // Normal is 1 year but for Trends analysis, 2 years

include 'qnm_stats_inc.php';

// Table header

$arr = QTarrget(GetSections($oVIP->user->role));
$strCSV .= '"'.implode(' ',$arrYears).($s>=0 ? ' ('.$arr[$s].')' : '').(empty($tag) ? '' : ', '.$L['With_tag'].' '.str_replace(';',' '.$L['or'].' ',$tag)).'"<br/>';

// -----
foreach($arrYears as $y) {
// -----

// Table header

$strCSV .= '"'.$y.'";';

switch($ch['time'])
{
case 'q': for ($i=1;$i<=$intMaxBt;$i++) { $strCSV .= '"Q'.$i.'";'; } break;
case 'm': for ($i=1;$i<=$intMaxBt;$i++) { $strCSV .= '"'.$L['dateMM'][$i].'";'; } break;
case 'd': for ($i=1;$i<=$intMaxBt;$i++) { $strCSV .= '"'.QTdatestr( DateAdd($strTendaysago,$i,'day'),'d M','' ).'";'; } break;
}
$strCSV .= '"'.($ch['time']=='d' ? '10 '.strtolower($L['Days']) : $L['Year']).'"<br/>';

// Table body

if ( $tt=='g' || $tt=='gt' )
{

  $strCSV .= '"'.$L['Items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++) { $strCSV .= (isset($arrT[$y][$intBt]) ? $arrT[$y][$intBt] : '0').';'; }
  $strCSV .= $arrTs[$y].'<br/>';

  $strCSV .= '"'.$L['Notes'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++) { $strCSV .= (isset($arrM[$y][$intBt]) ? $arrM[$y][$intBt] : '0').';'; }
  $strCSV .= $arrMs[$y].'<br/>';

  $strCSV .= '"'.$L['Users'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++) { $strCSV .= (isset($arrU[$y][$intBt]) ? $arrU[$y][$intBt] : '0').';'; }
  $strCSV .= $arrUs[$y].'<br/>';

}

if ( $tt=='d' )
{

  $strCSV .= '"'.$L['New_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++) { $strCSV .= (isset($arrN[$y][$intBt]) ? $arrN[$y][$intBt] : '0').';'; }
  $strCSV .= $arrNs[$y].'<br/>';

  $strCSV .= '"'.$L['Closed_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++) { $strCSV .= (isset($arrC[$y][$intBt]) ? $arrC[$y][$intBt] : '0').';'; }
  $strCSV .= $arrCs[$y].'<br/>';

  $strCSV .= '"'.$L['Backlog'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++) { $strCSV .= (isset($arrT[$y][$intBt]) ? $arrT[$y][$intBt] : '0').';'; }
  $strCSV .= $arrTs[$y].'<br/>';

}

if ( $tt=='dt' )
{

  $strCSV .= '"'.$L['New_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++) { $strCSV .= (isset($arrN[$y][$intBt]) ? $arrN[$y][$intBt] : '0').';'; }
  $strCSV .= $arrNs[$y].'<br/>';

  $strCSV .= '"'.$L['Closed_items'].'";';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++) { $strCSV .= (isset($arrC[$y][$intBt]) ? $arrC[$y][$intBt] : '0').';'; }
  $strCSV .= $arrCs[$y].'<br/><br/>';

}

// -----
}
// -----

// add trends [gt] if several years

if ( $tt=='gt' )
{
  // Table header

  $strCSV .= '"'.$L['Trends'].'";';

  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;$i++) { $strCSV .= '"Q'.$i.'";'; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;$i++) { $strCSV .= '"'.$L['dateMM'][$i].'";'; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;$i++) { $strCSV .= '"'.QTdatestr( DateAdd($strTendaysago,$i,'day'),'d M','' ).'";'; } break;
  }
  $strCSV .= '"'.($ch['time']=='d' ? '10 '.strtolower($L['Days']) : $L['Year']).'"<br/>';

  // Table body

  $strCSV .= $L['Items'].';';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
  {
    $i = 0;
    if ( isset($arrT[$y][$intBt]) && isset($arrT[$y-1][$intBt]) )
    {
      $i = $arrT[$y][$intBt]-$arrT[$y-1][$intBt];
      if ( $ch['trend']=='p' && $i!=0 )
      {
        if ( $arrT[$y-1][$intBt]==0 ) $arrT[$y-1][$intBt]=1;
        $i = intval(($i/$arrT[$y-1][$intBt])*100);
      }
    }
    $strCSV .= $i.';';
  }
  $i = 0;
  if ( isset($arrTs[$y]) && isset($arrTs[$y-1]) )
  {
    $i = $arrTs[$y]-$arrTs[$y-1];
    if ( $ch['trend']=='p' && $i!=0 )
    {
      if ( $arrTs[$y-1]==0 ) $arrTs[$y-1]=1;
      $i = intval(($i/$arrT[$y-1])*100);
    }
  }
  $strCSV .= $i.'<br/>';

  $strCSV .= $L['Notes'].';';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
  {
    $i = 0;
    if ( isset($arrM[$y][$intBt]) && isset($arrM[$y-1][$intBt]) )
    {
      $i = $arrM[$y][$intBt]-$arrM[$y-1][$intBt];
      if ( $ch['trend']=='p' && $i!=0 )
      {
        if ( $arrM[$y-1][$intBt]==0 ) $arrM[$y-1][$intBt]=1;
        $i = intval(($i/$arrM[$y-1][$intBt])*100);
      }
    }
    $strCSV .= $i.';';
  }
  $i = 0;
  if ( isset($arrMs[$y]) && isset($arrMs[$y-1]) )
  {
    $i = $arrMs[$y]-$arrMs[$y-1];
    if ( $ch['trend']=='p' && $i!=0 )
    {
      if ( $arrMs[$y-1]==0 ) $arrMs[$y-1]=1;
      $i = intval(($i/$arrMs[$y-1])*100);
    }
  }
  $strCSV .= $i.'<br/>';

  $strCSV .= $L['Users'].';';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
  {
    $i = 0;
    if ( isset($arrU[$y][$intBt]) && isset($arrU[$y-1][$intBt]) )
    {
      $i = $arrU[$y][$intBt]-$arrU[$y-1][$intBt];
      if ( $ch['trend']=='p' && $i!=0 )
      {
        if ( $arrU[$y-1][$intBt]==0 ) $arrU[$y-1][$intBt]=1;
        $i = intval(($i/$arrU[$y-1][$intBt])*100);
      }
    }
    $strCSV .= $i.';';
  }
  $i = 0;
  if ( isset($arrUs[$y]) && isset($arrUs[$y-1]) )
  {
    $i = $arrUs[$y]-$arrUs[$y-1];
    if ( $ch['trend']=='p' && $i!=0 )
    {
      if ( $arrUs[$y-1]==0 ) $arrUs[$y-1]=1;
      $i = intval(($i/$arrU[$y-1])*100);
    }
  }
  $strCSV .= $i.'<br/>';

}

// add trends [dt] if several years

if ( $tt=='dt' )
{

  // Table header

  $strCSV .= '"'.$L['Trends'].'";';

  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;$i++) { $strCSV .= '"Q'.$i.'";'; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;$i++) { $strCSV .= '"'.$L['dateMM'][$i].'";'; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;$i++) { $strCSV .= '"'.QTdatestr( DateAdd($strTendaysago,$i,'day'),'d M','' ).'";'; } break;
  }
  $strCSV .= '"'.($ch['time']=='d' ? '10 '.strtolower($L['Days']) : $L['Year']).'"<br/>';

  // Table body

  $strCSV .= $L['New_items'].';';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
  {
    $i = 0;
    if ( isset($arrN[$y][$intBt]) && isset($arrN[$y-1][$intBt]) )
    {
      $i = $arrN[$y][$intBt]-$arrN[$y-1][$intBt];
      if ( $ch['trend']=='p' && $i!=0 )
      {
        if ( $arrN[$y-1][$intBt]==0 ) $arrN[$y-1][$intBt]=1;
        $i = intval(($i/$arrN[$y-1][$intBt])*100);
      }
    }
    $strCSV .= $i.';';
  }
  $i = 0;
  if ( isset($arrNs[$y]) && isset($arrNs[$y-1]) )
  {
    $i = $arrNs[$y]-$arrNs[$y-1];
    if ( $ch['trend']=='p' && $i!=0 )
    {
      if ( $arrNs[$y-1]==0 ) $arrNs[$y-1]=1;
      $i = intval(($i/$arrNs[$y-1])*100);
    }
  }
  $strCSV .= $i.'<br/>';

  // Closed_items trends

  $strCSV .= $L['Closed_items'].';';
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
  {
    $i = 0;
    if ( isset($arrC[$y][$intBt]) && isset($arrC[$y-1][$intBt]) )
    {
      $i = $arrC[$y][$intBt]-$arrC[$y-1][$intBt];
      if ( $ch['trend']=='p' && $i!=0 )
      {
        if ( $arrC[$y-1][$intBt]==0 ) $arrC[$y-1][$intBt]=1;
        $i = intval(($i/$arrC[$y-1][$intBt])*100);
      }
    }
    $strCSV .= $i.';';
  }
  $i = 0;
  if ( isset($arrCs[$y]) && isset($arrCs[$y-1]) )
  {
    $i = $arrCs[$y]-$arrCs[$y-1];
    if ( $ch['trend']=='p' && $i!=0 )
    {
      if ( $arrCs[$y-1]==0 ) $arrCs[$y-1]=1;
      $i = intval(($i/$arrCs[$y-1])*100);
    }
  }
  $strCSV .= $i.'<br/>';

}

// ------
// Export
// ------

if ( !headers_sent() )
{
  $strCSV = str_replace('<br/>',"\r\n",$strCSV);
  header('Content-Type: text/csv; charset='.QNM_HTML_CHAR);
  header('Content-Disposition: attachment; filename="global_stat_'.$y.'.csv"');
}

echo $strCSV;