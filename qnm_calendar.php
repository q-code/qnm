<?php

/**
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QNM
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2013 The PHP Group
* @version    1.0 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
if ( !isset($_GET['s']) ) die('Missing section id...');
if ( !$oVIP->user->CanView('V2') ) HtmlPage(11);

$bShow = false;
if ( $_SESSION[QT]['show_calendar']=='V' ) $bShow = true;
if ( $_SESSION[QT]['show_calendar']=='U' && $oVIP->user->role!='V' ) $bShow = true;
if ( $_SESSION[QT]['show_calendar']=='M' && $oVIP->user->role=='M' ) $bShow = true;
if ( $oVIP->user->role=='A' ) $bShow = true;
if ( !$bShow ) HtmlPage(101);

// ---------
// FUNCTIONS
// ---------

include 'bin/qnm_fn_sql.php';

function FirstDayDisplay($intYear,$intMonth,$intWeekstart=1)
{
  // search date of the first 'monday' (or weekstart if not 1)
  // before the beginning of the month (to display gey-out in the calendar)
  if ( $intWeekstart<1 || $intWeekstart>7 ) die ('FirstDayDisplay: Arg #3 must be an int (1-7)');

  $arr = array(1=>'monday','tuesday','wednesday','thursday','friday','saturday','sunday'); // system weekdays reference
  $strWeekstart = $arr[$intWeekstart];
  $d = mktime(0,0,0,$intMonth,1,$intYear); // first day of the month
  if ( strtolower(date('l',$d))==$strWeekstart ) return $d;

  for($i=1;$i<8;$i++)
  {
    $d = strtotime('-1 day',$d);
    if ( strtolower(date('l',$d))==$strWeekstart ) return $d;
  }
  return $d;
}

function ArraySwap($arr,$n=1)
{
  // Move the first value to the end of the array. Action is repeated $n times. Keys are not moved.
  if ($n>0)
  {
    $arrK = array_keys($arr);
    while($n>0) { array_push($arr,array_shift($arr)); $n--; }
    $arrV = array_values($arr);
    $arr = array();
    for($i=0;$i<count($arrK);$i++) $arr[$arrK[$i]] = $arrV[$i];
  }
  return $arr;
}

// ---------
// INITIALISE
// ---------

$s = -1;
$v = 'firstpostdate';
QThttpvar('s v','int str');
if ( !in_array($v,array('firstpostdate','lastpostdate','wisheddate')) ) die('Wring calendar field');

$intYear = intval(date('Y')); if ( isset($_GET['y']) ) $intYear = intval($_GET['y']);
$intYearN  = $intYear;
$intMonth = intval(date('n')); if ( isset($_GET['m']) ) $intMonth = intval($_GET['m']);
$intMonthN = $intMonth+1; if ( $intMonthN>12 ) { $intMonthN=1; $intYearN++; }
$strMonth  = '0'.$intMonth; $strMonth = substr($strMonth,-2,2);
$strMonthN = '0'.$intMonthN; $strMonthN = substr($strMonthN,-2,2);
$arrWeekCss = array(1=>'monday','tuesday','wednesday','thursday','friday','saturday','sunday'); // system weekdays reference

$dToday  = mktime(0,0,0,date('n'),date('j'),date('Y'));

$dMonth  = mktime(0,0,0,$intMonth,1,$intYear); // First day of the month
$dMonthN = mktime(0,0,0,$intMonthN,1,$intYearN);

if ( $intYear>2100 ) die('Invalid year');
if ( $intYear<1900 ) die('Invalid year');
if ( $intMonth>12 ) die('Invalid month');
if ( $intMonth<1 ) die('Invalid month');

// moderator settings

$strOptions = '';
if ( isset($_GET['Maction']) )
{
  if ( $_GET['Maction']=='this' ) $_SESSION[QT]['cal_showall'] = false;
  if ( $_GET['Maction']=='all' ) $_SESSION[QT]['cal_showall'] = true;
  if ( $_GET['Maction']=='show_Z' ) $_SESSION[QT]['show_closed']=true;
  if ( $_GET['Maction']=='hide_Z' ) $_SESSION[QT]['show_closed']=false;
  if ( $_GET['Maction']=='hide_News' ) $_SESSION[QT]['cal_shownews'] = false;
  if ( $_GET['Maction']=='show_News' ) $_SESSION[QT]['cal_shownews'] = true;
}
if ( !$_SESSION[QT]['show_closed'] ) $strOptions .= 'status<>"Z" AND ';
if ( !$_SESSION[QT]['cal_showall'] ) $strOptions .= 'section='.$s.' AND ';
if ( !$_SESSION[QT]['cal_shownews'] ) $strOptions .= 'type<>"A" AND ';

$oSEC = new cSection($s);

$oVIP->selfurl = 'qnm_calendar.php';
$oVIP->selfuri = 'qnm_calendar.php?s='.$s.'&amp;v='.$v.'&amp;y='.$intYear.'&amp;m='.$intMonth;
$oVIP->selfname = $L['Section'].': '.$oSEC->name;

// Shift language names and cssWeek to match with weekstart setting, if not 1 (monday)

if ( QNM_WEEKSTART>1 )
{
  $L['dateDDD'] = ArraySwap($L['dateDDD'],intval(QNM_WEEKSTART)-1);
  $L['dateDD'] = ArraySwap($L['dateDD'],intval(QNM_WEEKSTART)-1);
  $L['dateD'] = ArraySwap($L['dateD'],intval(QNM_WEEKSTART)-1);
  $arrWeekCss = ArraySwap($arrWeekCss,intval(QNM_WEEKSTART)-1);
}

// MAP MODULE

if ( UseModule('map') ) { $strCheck=$s; include 'qnmm_map_ini.php'; } else { $bMap=false; }

// --------
// LIST OF ElementS PER DAY IN THIS FORUM
// --------

$arrElements=array();
$arrElementsN=array();
$intCountItems=0;
$intCountItemsN=0;
$arrCountItems=array();

$oDB->Query( 'SELECT id,section,numid,type,status,'.$v.' as eventday,y,x FROM '.TABNE.' WHERE '.$strOptions.'('.SqlDateCondition(($intYear*100+$intMonth),$v,6).' OR '.SqlDateCondition(($intYearN*100+$intMonthN),$v,6).')' );

while($row=$oDB->Getrow()) {
if ( !empty($row['eventday']) ) {
  $strM = substr($row['eventday'],4,2); $intM = intval($strM);
  $strD = substr($row['eventday'],6,2); $intD = intval($strD);
  if ( $strM==$strMonth )
  {
  $arrElements[$intD][]=$row; $intCountItems++;
  if ( !isset($arrCountItems[$row['status']]) ) { $arrCountItems[$row['status']]=1; } else { $arrCountItems[$row['status']]++; }
  }
  if ( $strM==$strMonthN ) { $arrElementsN[$intD]=1; $intCountItemsN++; }
}}

// --------
// HTML START
// --------

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_main2.css" title="cssmain" />
<script type="text/javascript">
<!--
$(function() {
  $(".ajaxmouseover").mouseover(function() {
    $.post("qnm_j_item.php",
      {id:this.id,lang:"'.GetLang().'"},
      function(data) { if ( data.length>0 ) document.getElementById("title_err").innerHTML=data; });
  });
});
//-->
</script>
';

include 'qnm_inc_hd.php';

// Moderator actions
if ( $oVIP->user->role!='A' || $oVIP->user->role!='M' )
{
echo '<form method="get" action="',Href(),'" id="modaction">
<div class="modboard">
<span class="modboard">';
echo $L['Userrole_'.strtolower($oVIP->user->role)],':&nbsp;<input type="hidden" name="s" value="',$s,'"/>';
echo '<input type="hidden" name="v" value="',$v,'"/>
<input type="hidden" name="y" value="',$intYear,'"/>
<input type="hidden" name="m" value="',$intMonth,'"/>
<select name="Maction" class="small" onchange="document.getElementById(\'modaction\').submit();">
<option value="">&nbsp;</option>
<option value="show_Z"',($_SESSION[QT]['show_closed'] ? ' class="bold"' : ''),'>',$L['Item_closed_show'],'</option>
<option value="hide_Z"',(!$_SESSION[QT]['show_closed'] ? ' class="bold"' : ''),'>',$L['Item_closed_hide'],'</option>
<option value="0" disabled="disabled">-----------------</option>
<option value="show_News"',($_SESSION[QT]['cal_shownews'] ? ' class="bold"' : ''),'>',$L['Item_news_show'],'</option>
<option value="hide_News"',(!$_SESSION[QT]['cal_shownews'] ? ' class="bold"' : ''),'>',$L['Item_news_hide'],'</option>
<option value="0" disabled="disabled">-----------------</option>
<option value="all"',($_SESSION[QT]['cal_showall'] ? ' class="bold"' : ''),'>',$L['Item_show_all'],'</option>
<option value="this"',(!$_SESSION[QT]['cal_showall'] ? ' class="bold"' : ''),'>',$L['Item_show_this'],'</option>
</select>&nbsp;<input type="submit" name="Mok" class="small" value="',$L['Ok'],'" id="action_ok"/>
<script type="text/javascript">
<!--
document.getElementById("action_ok").style.display="none";
document.getElementById("action_ok").value="";
//-->
</script>
</span>
</div>
</form>
';
}

// --------
// MAIN CALENDAR
// --------

$dFirstDay = FirstDayDisplay($intYear,$intMonth,QNM_WEEKSTART);
$intWeeknumber = intval(date('W',$dFirstDay));

// DISPLAY MAIN CALENDAR

echo '
<table class="hidden" style="width:700px"><tr class="hidden">
<td class="hidden">
<h2>';
if ( date('n',$dMonth)>1 ) echo '<a href="',Href(),'?s=',$s,'&amp;v=',$v,'&amp;y=',$intYear,'&amp;m='.(date('n',$dMonth)-1).'">&lt;</a> ';
echo $L['dateMMM'][date('n',$dMonth)],($intYear!=intval(date('Y')) ? ' '.$intYear : '');
if ( date('n',$dMonth)<12 ) echo ' <a href="',Href(),'?s=',$s,'&amp;v=',$v,'&amp;y=',$intYear,'&amp;m='.(date('n',$dMonth)+1).'">&gt;</a>';
echo '</h2>
</td>
<td style="text-align:right;">
<form class="small" method="get" action="',Href(),'">
<input type="hidden" name="s" id="s" value="',$s,'"/>
<input type="hidden" name="v" id="v" value="',$v,'"/>
<input type="hidden" name="y" id="y" value="',$intYear,'"/>
',$L['Display_at'],' <select class="small" name="v">
<option value="firstpostdate"',($v=='firstpostdate' ? QSEL : ''),'>',$L['First_message'],'</option>
<option value="lastpostdate"',($v=='lastpostdate' ? QSEL : ''),'>',$L['Last_message'],'</option>
<option value="wisheddate"',($v=='wisheddate' ? QSEL : ''),'>',$L['Wisheddate'],'</option>
</select>
',$L['Month'],' <select class="small" name="m">
';
for ($i=1;$i<13;$i++)
{
echo '<option',($i==date('n') ? ' class="bold" ' : ''),' value="',$i,'"',($i==$intMonth ? QSEL : ''),'>',$L['dateMMM'][$i],'</option>';
}
echo '</select> ';
$arrYears = array($intYear-1=>$intYear-1,$intYear,$intYear+1);
if ( !isset($arrYears[intval(date('Y'))]) ) $arrYears[intval(date('Y'))]=intval(date('Y'));
echo '<select class="small" name="y">',QTasTag($arrYears,$intYear),'</select> ';
echo '<input class="small" type="submit" name="submit" id="submit" value="',$L['Ok'],'"/></td>',PHP_EOL;
echo '</form></td>';
echo '</tr></table>';

echo '<table class="data_o" style="width:700px">';
echo '<tr class="data_o">';
echo '<th class="week date_first">&nbsp;</th>';
for ($i=1;$i<8;$i++)
{
  echo '<th class="date',($i==7 ? ' date_last' : ''),'" style="width:95px">',$L['dateDDD'][$i],'</th>';
}
echo '</tr>';

$iShift=0;
for ($intWeek=0;$intWeek<6;$intWeek++)
{
  if ( $intWeeknumber>52 ) $intWeeknumber=1;
  echo '<tr class="data_t">';
  echo '<td class="week">',$intWeeknumber,'</td>'; $intWeeknumber++;
  for ($intDay=1;$intDay<8;$intDay++)
  {
    $d = strtotime("+$iShift days",$dFirstDay); $iShift++;
    $intShiftYear = date('Y',$d);
    $intShiftMonth = date('n',$d);
    $intShiftDay = date('j',$d);

    // date number
    if ( date('n',$dMonth)==date('n',$d) )
    {
      echo '<td class="date ',$arrWeekCss[$intDay],'"',(date('Ymd',$dToday)==date('Ymd',$d) ? ' id="zone_today"' : ''),'>';
      echo '<p class="datenumber">',$intShiftDay,'</p><p class="dateicon">&nbsp;';
      // date info element
      if ( isset($arrElements[$intShiftDay]) )
      {
        $intElements = 0;
        foreach($arrElements[$intShiftDay] as $intKey=>$arrValues)
        {
          $intElements++;
          $oNE = new cNE($arrValues);

          if ( $bMap ) {
          if ( !empty($oNE->y) && !empty($oNE->x) ) {

            $strPname = $intShiftDay.' '.$L['dateMMM'][date('n',$dMonth)].' - ';
            if ( $s==$oNE->section ) { $strPname .= ($oSEC->numfield=='N' ? '' : sprintf($oSEC->numfield,$oNE->numid)); } else { $strPname .= sprintf('%03s',$oNE->numid); }
            $strPname .= ' '.$oVIP->statuses[$oNE->status]['statusname'];
            $strPlink = '<a class="gmap" href="'.Href('qnm_item.php').'?s='.$oNE->section.'&amp;t='.$oNE->id.'">'.$L['Item'].'</a> &middot; <a class="small" href="http://maps.google.com?q='.$oNE->y.','.$oNE->x.'+('.urlencode($strPname).')&z='.$_SESSION[QT]['m_map_gzoom'].'" title="'.$L['map']['In_google'].'" target="_blank">[G]</a>';
            $strPinfo = '<span class="small bold">Lat: '.QTdd2dms($oNE->y).' <br/>Lon: '.QTdd2dms($oNE->x).'</span><br/><span class="small">DD: '.round($oNE->y,8).', '.round($oNE->x,8).'</span><br/>'.$strPlink;
            $oMapPoint = new cMapPoint($oNE->y,$oNE->x,$strPname,$strPname.'<br/>'.$strPinfo);
            if ( isset($_SESSION[QT]['m_map'][$oNE->section]['icon']) )        $oMapPoint->icon        = $_SESSION[QT]['m_map'][$oNE->section]['icon'];
            if ( isset($_SESSION[QT]['m_map'][$oNE->section]['shadow']) )      $oMapPoint->shadow      = $_SESSION[QT]['m_map'][$oNE->section]['shadow'];
            if ( isset($_SESSION[QT]['m_map'][$oNE->section]['printicon']) )   $oMapPoint->printicon   = $_SESSION[QT]['m_map'][$oNE->section]['printicon'];
            if ( isset($_SESSION[QT]['m_map'][$oNE->section]['printshadow']) ) $oMapPoint->printshadow = $_SESSION[QT]['m_map'][$oNE->section]['printshadow'];
            $arrExtData[] = $oMapPoint;

          }}

          // icon
          $strTicon = cNE::GetIcon($oNE,true);

          if ( $intElements>=12 )
          {
            echo '...';
            break;
          }
          else
          {
            if ( $bMap ) {
            if ( $bMapGoogle && !$_SESSION[QT]['m_map_hidelist'] && !empty($oNE->y) && !empty($oNE->x) ) {
            $str = ' onmouseover="map.setCenter(new GLatLng('.$oNE->y.','.$oNE->x.'));"';
            }}
            echo '<a class="ajaxmouseover" id="t',$oNE->id,'"',$str,' href="',Href('qnm_item.php'),'?t=',$oNE->id,'">',$strTicon,'</a> ';
          }
        }
      }
    }
    else
    {
      echo '<td class="date_out">';
      echo '<p class="datenumber">',$intShiftDay,'</p><p class="dateicon">&nbsp;';
    }
    echo '</p></td>';
  }
  echo '</tr>';
  if ( $intShiftMonth>$intMonth && $intShiftYear==$intYear ) break;
}

echo '</table>';

// --------
// NEXT MONTH
// --------

$dFirstDay = FirstDayDisplay($intYearN,$intMonthN,QNM_WEEKSTART);

// DISPLAY SUBDATA

echo '<table class="hidden"><tr class="hidden">';
echo '<td class="hidden" style="width:220px">';

// DISPLAY NEXT MONTH

echo '<h2>',$L['dateMMM'][date('n',$dMonthN)],($intYearN!=$intYear ? ' '.$intYearN : ''),'</h2>';
echo '<table class="data_o" style="width:200px">';
echo '<tr class="data_o">';
for ($intDay=1;$intDay<8;$intDay++)
{
echo '<th class="date_next">',$L['dateD'][$intDay],'</th>';
}
echo '</tr>';

  $iShift=0;
  for ($intWeek=0;$intWeek<6;$intWeek++)
  {
    echo '<tr class="data_t">';
    for ($intDay=1;$intDay<8;$intDay++)
    {
      $d = strtotime("+$iShift days",$dFirstDay); $iShift++;
      $intShiftYear = date('Y',$d);
      $intShiftMonth = date('n',$d);
      $intShiftDay = date('j',$d);
      // date number
      if ( date('n',$dMonthN)==date('n',$d) )
      {
        echo '<td class="date_next ',$arrWeekCss[$intDay],'"',(date('Ymd',$dToday)==date('Ymd',$d) ? ' id="zone_today"' : ''),'>';
        if ( !empty($arrElementsN[$intShiftDay]) )
        {
          echo '<a class="date_next" href="',Href('qnm_calendar.php'),'?s=',$s,'&amp;y=',$intYearN,'&amp;m=',$intMonthN,'">',$intShiftDay,'</a> ';
        }
        else
        {
          echo $intShiftDay;
        }
      }
      else
      {
        echo '<td class="date_out_next">';
        echo $intShiftDay;
      }
      echo '</td>';
    }
    echo '</tr>';
    if ( $intShiftMonth>$intMonthN && $intShiftYear==$intYearN ) break;
  }

echo '</table>';

echo '</td>';
echo '<td class="hidden" style="width:220px">';

// DISPLAY Preview

echo '<h2>',$L['Preview'],'</h2>';
echo '<script type="text/javascript"></script><noscript>Your browser does not support JavaScript</noscript>';
echo '<div style="width:210px" id="title_err"></div>';

echo '</td>';

echo '<td class="hidden">';

// DISPLAY MAP

if ( $bMap )
{
  echo '<!-- Map module -->',PHP_EOL;
  if ( count($arrExtData)>0 )
  {
    if ( $_SESSION[QT]['m_map_hidelist'] )
    {
    echo '<p style="margin:2px;text-align:right"><a class="small" href="',Href($oVIP->selfuri),'&amp;showmap">',$L['map']['Show_map'],'</a></p>',PHP_EOL;
    }
    else
    {
    echo '<p style="margin:2px;text-align:right"><a class="small" href="',Href($oVIP->selfuri),'&amp;hidemap">',$L['map']['Hide_map'],'</a></p>',PHP_EOL;
    }
    $strMapHelp = sprintf($L['map']['items'],count($arrExtData),L('item',$intCountItems) );
    if ( $bMapGoogle )
    {
      echo '<div class="gmap" style="margin:0 0 0 auto;">';
      if ( !$_SESSION[QT]['m_map_hidelist'] )
      {
      echo QTgmapZoomControl($arrExtData);
      echo '<div id="map_canvas" style="width:100%; height:250px;"></div>';
      }
      if ( !empty($strMapHelp) ) echo '<p class="gmap" style="margin:4px 0 0 0">'.$strMapHelp.'</span>';
      echo '</div>';
    }
    if ( $bMapSitework )
    {
      echo '<p class="gmap">This version of SiteWork cannot display calendar items.</p>',PHP_EOL;
    }
  }
  else
  {
    echo '<p class="gmap">'.$L['map']['E_noposition'].'</p>',PHP_EOL;
  }
  echo '<!-- Map module end -->',PHP_EOL;
}
echo '</td>';

echo '</tr></table>';

$strDetailLegend = '<p class="preview_section"><b>'.$L['dateMMM'][date('n',$dMonth)].'</b> '.L('Item',$intCountItems).'</p>';
foreach($arrCountItems as $strKey=>$intValue)
{
$strDetailLegend .= '<p class="preview_section">'.$intValue.' '.$oVIP->statuses[$strKey]['name'].'</p>';
}
$strDetailLegend .= '<br/>';
$strDetailLegend .= '<p class="preview_section"><b>'.$L['dateMMM'][date('n',$dMonthN)].'</b> '.L('Item',$intCountItemsN).'</p>';

// --------
// HTML END
// --------

if ( $bMap )
{
  if ( count($arrExtData)>0 ) { $bSmallMap=true; include 'qnmm_map_load.php'; } else { echo '<script type="text/javascript">function GUnload() { return true; }</script>'; }
}

include 'qnm_inc_ft.php';