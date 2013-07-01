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
if ( !$oVIP->user->CanView('V4') ) HtmlPage(11);
include 'bin/qnm_fn_sql.php';

// INITIALISE

$strGroup = 'all';
$strOrder = 'name';
$strDirec = 'asc';
$intLimit = 0;
$intPage = 1;

// Security check 1

if ( isset($_GET['group']) ) $strGroup = strip_tags($_GET['group']);
if ( isset($_GET['order']) ) $strOrder = strip_tags($_GET['order']);
if ( isset($_GET['dir']) ) $strDirec = strtolower(strip_tags($_GET['dir']));
if ( isset($_GET['page']) ) $intPage = intval(strip_tags($_GET['page']));
if ( isset($_GET['view']) ) $_SESSION[QT]['viewmode'] = strip_tags($_GET['view']);

// Security check 2 (no long argument)

if ( strlen($strGroup)>4 ) die('Invalid argument #group');
if ( strlen($strOrder)>20 ) die('Invalid argument #order');
if ( strlen($strDirec)>4 ) die('Invalid argument #dir');

$intLimit = ($intPage-1)*$_SESSION[QT]['items_per_page'];
$strWhere = '';

$oVIP->selfurl = 'qnm_users.php';
$oVIP->selfname = $L['Memberlist'];

// MAP MODULE

$bMap=false;
if ( UseModule('map') )
{
  // Map configuration
  include 'qnmm_map_lib.php';
  $bMap = QTgcanmap('U',true,true); // Read the config file to initialize the $_SESSION[QT]['m_map'][] arguments // Do a main list check
  if ( $bMap )
  {
    include Translate('qnmm_map.php');
    $oHtml->links[]='<link rel="stylesheet" type="text/css" href="qnmm_map.css" />';
  }
  if ( isset($_GET['hidemap']) ) $_SESSION[QT]['m_map_hidelist']=true;
  if ( isset($_GET['showmap']) ) $_SESSION[QT]['m_map_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_map_hidelist']) ) $_SESSION[QT]['m_map_hidelist']=false;

  // Symbol
  if ( isset($_SESSION[QT]['m_map']['sU']) ) { $arrMapSymbol=QTexplode($_SESSION[QT]['m_map']['sU']); } else { $arrMapSymbol=array(); }
}

// COUNT

if ($strGroup!=='all')
{
  $strWhere = ($strGroup=='0' ? ' AND '.FirstCharCase('name','a-z') : ' AND '.FirstCharCase('name','u').'="'.$strGroup.'"' );
  $oDB->Query('SELECT count(*) as countid FROM '.TABUSER.' WHERE status>=0'.$strWhere);
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
}
else
{
  $intCount = $oVIP->stats->members;
}

// User menu

if ( $oVIP->user->IsStaff() ) include 'qnm_inc_menu.php';

// GROUP LINE

if ( $intCount>$_SESSION[QT]['items_per_page'] || isset($_GET['group']) ) $strGroups = HtmlLettres($strGroup,$L['All']);

// --------
// HTML START
// --------

include 'qnm_inc_hd.php';

// Title and top participants

$str='';
$oDB->Query( LimitSQL('name, id, (SELECT COUNT('.TABPOST.'.id) FROM '.TABPOST.' WHERE '.TABPOST.'.userid='.TABUSER.'.id AND '.TABPOST.'.status>=0) as notes FROM '.TABUSER.' WHERE id>0','notes DESC',0,5) );
for ($i=0;$i<($_SESSION[QT]['viewmode']=='C' ? 2 : 5);$i++)
{
$row = $oDB->Getrow();
if ( !$row ) break;
$str .= '<tr class="hidden"><td class="hidden" style="text-align:left"><a href="'.Href('qnm_user.php').'?id='.$row['id'].'">'.$row['name'].'</a></td><td class="hidden" style="text-align:right">'.$row['notes'].'</td></tr>'.PHP_EOL;
}
if ( !empty($str) ) $str = '<div class="legendbox"><p class="legendtitle">'.$L['Top_participants'].'</p><table class="hidden">'.$str.'</table></div>';

echo '<table class="hidden">
<tr class="hidden">
<td class="hidden">
<h2>',$oVIP->selfname,'</h2>
<p>',($strGroup=='all' ? $oVIP->stats->members.' '.$L['Users'] : $intCount.' / '.$oVIP->stats->members.' '.$L['Users'] ),(empty($strUsermenu) ? '' : ' &middot; '.$strUsermenu),'</p>
';
if ( !empty($info) ) echo '<p id="forminfo" class="info">',$info,'</p>';
echo '
</td>
<td class="hidden" style="width:150px;">',$str,'</td>
</tr>
</table>
';

if ( !empty($strUserform) ) echo $strUserform;

// --------
// Button line and pager
// --------

$strPager = MakePager("qnm_users.php?group=$strGroup&order=$strOrder&dir=$strDirec",$intCount,$_SESSION[QT]['items_per_page'],$intPage);
if ( !empty($strPager) ) $strPager = $L['Page'].$strPager;
if ( $intCount<$oVIP->stats->members ) $strPager = '<span class="small">'.$intCount.' '.$L['Selected_from'].' '.$oVIP->stats->members.' '.strtolower($L['Users']).'</span>'.(empty($strPager) ? '' : ' | '.$strPager);

if ( $oVIP->stats->members>$_SESSION[QT]['items_per_page'] ) echo '<table class="button">',N,'<tr class="button">',PHP_EOL,$strGroups,'</tr>',PHP_EOL,'</table>',PHP_EOL;

if ( !empty($strPager) ) echo '<table class="hidden"><tr class="hidden"><td class="pager_zt right" id="pager_zt">',$strPager,'</td></tr></table>',PHP_EOL;

// end if no result

if ( $intCount==0 )
{
  $table = new cTable('t2','data_t');
  $table->th[] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('No_item').'...</p>',true,'','r1');
  include 'qnm_inc_ft.php';
  exit;
 }

// --------
// Memberlist
// --------

$bCompact = FALSE;
if ( $_SESSION[QT]['avatar']=='0' ) $bCompact = true;
if ( $_SESSION[QT]['viewmode']=='C' ) $bCompact = true;

// === TABLE DEFINITION ===

$table = new cTable('t1','data_u',$intCount);
$table->activecol = $strOrder;
$table->activelink = '<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order='.$strOrder.'&amp;dir='.($strDirec=='asc' ? 'desc' : 'asc').'&amp;page=1">%s</a> <img class="i_sort" src="'.$_SESSION[QT]['skin_dir'].'/sort_'.$strDirec.'.gif" alt="+"/>';
// column headers
if ( $bCompact )
{
$table->th['name'] = new cTableHead($L['Username'],'','','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=name&amp;dir=asc&amp;page=1">%s</a>'); $table->th['name']->Add('style','width:150px');
}
else
{
$table->th['photo'] = new cTableHead($L['Avatar']); $table->th['photo']->Add('style','width:100px');
$table->th['name'] = new cTableHead($L['Username'],'','','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=name&amp;dir=asc&amp;page=1">%s</a>');
}
$table->th['role'] = new cTableHead($L['Role'],'','','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=role&amp;dir=asc&amp;page=1">%s</a>');
$table->th['contact'] = new cTableHead($L['Contact']);
$table->th['location'] = new cTableHead($L['Location'],'','','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=location&amp;dir=asc&amp;page=1">%s</a>');
$table->th['notes'] = new cTableHead($L['Messages'],'','center','<a  href="'.$oVIP->selfurl.'?group='.$strGroup.'&amp;order=notes&amp;dir=desc&amp;page=1">%s</a>');
// create column data (from headers identifiers) and add class to all
foreach($table->th as $key=>$th) { $table->th[$key]->Add('class','th'.$key); $table->td[$key] = new cTableData('','','td'.$key); }

// === TABLE START DISPLAY ===

echo PHP_EOL;
echo $table->Start().PHP_EOL;
echo '<thead>'.PHP_EOL;
echo $table->GetTHrow('','hidden').PHP_EOL;
echo '</thead>'.PHP_EOL;
echo '<tbody>'.PHP_EOL;

$oDB->Query( LimitSQL('*,(SELECT COUNT('.TABPOST.'.id) FROM '.TABPOST.' WHERE '.TABPOST.'.userid='.TABUSER.'.id AND '.TABPOST.'.status>=0) as notes FROM '.TABUSER.' WHERE id>0'.$strWhere ,$strOrder.' '.strtoupper($strDirec), $intLimit,$_SESSION[QT]['items_per_page'],$intCount) );

$intWhile=0;
$strAlt='r1';
while($row=$oDB->Getrow())
{
  // privacy control for map and location field
  if ( $oVIP->user->IsPrivate($row['privacy'],$row['id']) ) { $row['y']=null; $row['x']=null; }

  // prepare row
  $table->row = new cTableRow('','data_u '.$strAlt.' rowlight');

  // prepare values, and insert value into the cells
  $table->SetTDcontent( FormatTableRow('t1',$table->GetTHnames(),$row,false,$bMap), false ); // adding extra columns not allowed

  //show row content
  echo $table->GetTDrow().PHP_EOL;

  if ( $strAlt=='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }

  // map settings
  if ( $bMap && !QTgempty($row['x']) && !QTgempty($row['y']) )
  {
    $y = (float)$row['y']; $x = (float)$row['x'];
    $strPname = QTconv($row['name'],'U');
    $strPinfo = $row['name'].'<br/><br/><a class="gmap" href="'.Href('qnm_user.php').'?id='.$row['id'].'">Open profile &raquo;<\/a>';
    if ( !empty($row['photo']) ) $strPinfo = '<table class="gmap"><tr><td>'.AsImg(QNM_DIR_PIC.$row['photo'],'',$row['name'],'imagelist').'<\/td><td>'.$strPinfo.'<\/td><\/tr><\/table>';
    $arrExtData[(int)$row['id']] = new cMapPoint($y,$x,$strPname,$strPinfo,$arrMapSymbol);
  }

  $intWhile++;
  //odbcbreak
  if ( $intWhile>=$_SESSION[QT]['items_per_page'] ) break;
}

// === TABLE END DISPLAY ===

echo '</tbody>
</table>
';

// Define bottom page command (add csv to $intCount (max 10000))

$strCsv ='';
$oVIP->selfuri = GetURI('page');
if ( $oVIP->user->role!='V' )
{
  if ( $intCount<=$_SESSION[QT]['items_per_page'] )
  {
    $strCsv = '<a class="csv" href="'.Href('qnm_users_csv.php').'?'.$oVIP->selfuri.'&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].'</a>';
  }
  else
  {
    $strCsv = '<a class="csv" href="'.Href('qnm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=p'.$intPage.'&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' ('.L('page').')</a>';
    if ( $intCount<=1000 )                   $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_users_csv.php').'?'.$oVIP->selfuri.'&amp;n='.$intCount.'" title="'.$L['H_Csv'].'>'.$L['Csv'].' ('.L('all').')</a>';
    if ( $intCount>1000 && $intCount<=2000 ) $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m1&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-1000)</a> &middot; <a class="csv" href="'.Href('qnm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m2&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1000-'.$intCount.')</a>';
    if ( $intCount>2000 && $intCount<=5000 ) $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m5&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-5000)</a>';
    if ( $intCount>5000 )                    $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m5&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-5000)</a> &middot; < class="csv"a href="'.Href('qnm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m10&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (5000-10000)</a>';
  }
}
if ( !empty($strCsv) )
{
  $strPager = $strCsv.' &middot; '.$strPager;
  if ( substr($strPager,-10,10)==' &middot; ' ) $strPager = substr($strPager,0,-10);
}

// -- Display pager  and User menu --

if ( !empty($strPager) ) echo '<table class="hidden"><tr class="hidden"><td class="pager_zb right" id="pager_zb">',$strPager,'</td></tr></table>',PHP_EOL;

if ( $bMap )
{
  echo '<!-- Map module -->',PHP_EOL;
  if ( count($arrExtData)==0 )
  {
    echo '<div class="gmap_disabled">'.$L['map']['E_noposition'].'</div>';
    $bMap=false;
  }
  else
  {
    //select zoomto (maximum 20 items in the list)
    $str = '';
    if ( count($arrExtData)>1 )
    {
      $str = '<p class="gmap commands" style="margin:0 0 4px 0"><a class="gmap" href="javascript:void(0)" onclick="zoomToFullExtend(); return false;">'.$L['map']['zoomtoall'].'</a> | '.L('Show').' <select class="gmap" id="zoomto" name="zoomto" size="1" onchange="gmapPan(this.value);">';
      $str .= '<option class="small_gmap" value="'.$_SESSION[QT]['m_map_gcenter'].'"> </option>';
      $i=0;
      foreach($arrExtData as $oMapPoint)
      {
      $str .= '<option class="small_gmap" value="'.$oMapPoint->y.','.$oMapPoint->x.'">'.$oMapPoint->title.'</option>';
      $i++; if ( $i>20 ) break;
      }
      $str .= '</select></p>'.PHP_EOL;
    }

    echo '<div class="gmap">',PHP_EOL;
    echo ($_SESSION[QT]['m_map_hidelist'] ? '' : $str.PHP_EOL.'<div id="map_canvas"></div>'.PHP_EOL);
    echo '<p class="gmap" style="margin:4px 0 0 0">',sprintf($L['map']['items'],strtolower( L('User',count($arrExtData))),strtolower(L('User',$intCount)) ),'</p>',PHP_EOL;
    echo '</div>',PHP_EOL;

    // Show/Hide

    if ( $_SESSION[QT]['m_map_hidelist'] )
    {
    echo '<div class="canvashandler"><a class="canvashandler" href="',Href(),'?showmap"><img class="canvashandler" src="qnmm_map_dw.gif" alt="+"/>',$L['map']['Show_map'],'</a></div>',PHP_EOL;
    }
    else
    {
    echo '<div class="canvashandler"><a class="canvashandler" href="',Href(),'?hidemap"><img class="canvashandler" src="qnmm_map_up.gif" alt="-"/>',$L['map']['Hide_map'],'</a></div>',PHP_EOL;
    }
  }
  echo '<!-- Map module end -->',PHP_EOL;
}


// --------
// HTML END
// --------

// MAP MODULE

if ( $bMap && !$_SESSION[QT]['m_map_hidelist'] )
{
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_map_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_map_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_map_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_map_gcenter']));

  // center on the first item
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    break;
    }
  }
  // update center
  $_SESSION[QT]['m_map_gcenter'] = $y.','.$x;

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
      $user_symbol = $gmap_symbol; // required to reset symbol on each user
      $user_shadow = $gmap_shadow;
      if ( !empty($oMapPoint->icon) ) $user_symbol = $oMapPoint->icon;
      if ( !empty($oMapPoint->shadow) ) $user_shadow = $oMapPoint->shadow;
      $gmap_markers[] = QTgmapMarker($oMapPoint->y.','.$oMapPoint->x,false,$user_symbol,$oMapPoint->title,$oMapPoint->info,$user_shadow);
    }
  }
  $gmap_functions[] = '
  function zoomToFullExtend()
  {
    if ( markers.length<2 ) return;
    var bounds = new google.maps.LatLngBounds();
    for (var i=markers.length-1; i>=0; i--) bounds.extend(markers[i].getPosition());
    map.fitBounds(bounds);
  }
  function showLocation(address)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( marker )
        {
          marker.setPosition(results[0].geometry.location);
        } else {
          marker = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: "Move to define the default map center"});
        }
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }

  ';
  include 'qnmm_map_load.php';
}

include 'qnm_inc_ft.php';