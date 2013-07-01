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
require_once('bin/qnm_init.php');
include(Translate('qnm_adm.php'));
if ( $oVIP->user->role!='A' ) die(Error(13));

include(Translate('qnmm_map.php'));
include(Translate('qnmm_map_adm.php'));
include('qnmm_map_lib.php');

// INITIALISE

$oVIP->selfurl = 'qnmm_map_adm.php';
$oVIP->selfname = 'Map';
$oVIP->exiturl = $oVIP->selfurl;
$oVIP->exitname = $oVIP->selfname;
$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="qnmm_map.css" />';
$strPageversion = $L['map_adm']['Version'].' 2.5';
$arrSettings = array('m_map_gkey','m_map_gcenter','m_map_gzoom','m_map_gfind','m_map_gbuttons');

// read values
foreach($arrSettings as $strValue)
{
  if ( !isset($_SESSION[QT][$strValue]) )
  {
  $arr = GetParam(true,'param="'.$strValue.'"');
  if ( empty($arr) ) die('<span class="error">Parameters not found. The module is probably not installed properly.</span><br/><br/><a href="qtf_adm_index.php">&laquo;&nbsp;'.$L['Exit'].'</a>');
  }
}

$oVIP->domains['Sys'] = 'System';
$arrSections = GetSections('A',-2); // Optimisation: get all sections at once (grouped by domain)
$arrSections['Sys'] = array(
  'S'=>array('title'=>L('Search_results')),
  'U'=>array('title'=>L('Users'))
);


// Read png in directory

$intHandle = opendir('qnmm_map');
$arrFiles = array();
while ( false!==($strFile = readdir($intHandle)) )
{
  if ( $strFile!='.' && $strFile!='..' ) {
  if ( substr($strFile,-4,4)=='.png' ) {
  if ( !strstr($strFile,'shadow') ) {
    $arrFiles[substr($strFile,0,-4)]=ucfirst(substr(str_replace('_',' ',$strFile),0,-4));
  }}}
}
closedir($intHandle);
asort($arrFiles);

// --------
// SUBMITTED for activation
// --------

if ( isset($_POST['ok']) && empty($_SESSION[QT]['m_map_gkey']) )
{
  $_SESSION[QT]['m_map_gkey'] = trim($_POST['m_map_gkey']); if ( strlen($_SESSION[QT]['m_map_gkey'])<8 ) $_SESSION[QT]['m_map_gkey']='';
  $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['m_map_gkey'].'" WHERE param="m_map_gkey"');
  $oVIP->EndMessage(NULL,$L['S_update'],'admin',2);
}

// --------
// SUBMITTED for changes
// --------

if ( isset($_POST['ok']) && !empty($_SESSION[QT]['m_map_gkey']) )
{
  $_SESSION[QT]['m_map_gkey'] = trim($_POST['m_map_gkey']); if ( strlen($_SESSION[QT]['m_map_gkey'])<8 ) $_SESSION[QT]['m_map_gkey']='';
  $_SESSION[QT]['m_map_gcenter'] = trim($_POST['m_map_gcenter']);
  $_SESSION[QT]['m_map_gzoom'] = trim($_POST['m_map_gzoom']);
  $_SESSION[QT]['m_map_gbuttons'] = substr($_POST['maptype'],0,1).(isset($_POST['streetview']) ? '1' : '0').(isset($_POST['map']) ? '1' : '0').(isset($_POST['scale']) ? '1' : '0').(isset($_POST['overview']) ? '1' : '0').(isset($_POST['mousewheel']) ? '1' : '0');
  $_SESSION[QT]['m_map_gfind'] = trim($_POST['m_map_gfind']);

  // save value
  if ( empty($error) )
  {
    foreach($arrSettings as $strKey) $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT][$strKey].'" WHERE param="'.$strKey.'"');
  }

  // save setting files
  $strFilename = 'qnmm_map/config.php';

  $content = '<?php
  $_SESSION[QT]["m_map"] = array();
  ';

  foreach($oVIP->domains as $domid=>$domain) {
  foreach($arrSections[$domid] as $key=>$strSectitle) {

    $strIcon = '0'; if ( isset($_POST['mark_'.$key]) ) { if ( !empty($_POST['mark_'.$key]) ) $strIcon = $_POST['mark_'.$key]; }
    $content .= '$_SESSION[QT]["m_map"]["s'.$key.'"] = "set='.(isset($_POST['sec_'.$key]) ? '1' : '0');
    if ( isset($_POST['sec_'.$key]) )
    {
    if ( $_POST['mark_'.$key]==='-' ) $_POST['mark_'.$key]='0'; //required for I.E.
    $content .= ';list='.(isset($_POST['list_'.$key]) ? $_POST['list_'.$key] : '0');
    $content .= ';icon='.$strIcon;
    $content .= ';shadow='.(file_exists('qnmm_map/'.$_POST['mark_'.$key].'_shadow.png') ? '1' : '0');
    $content .= ';printicon='.(file_exists('qnmm_map/'.$_POST['mark_'.$key].'.gif') ? '1' : '0');
    $content .= ';printshadow='.(file_exists('qnmm_map/'.$_POST['mark_'.$key].'_shadow.gif') ? '1' : '0');
    }
    $content .= '";
    ';
  }}

  if (!is_writable($strFilename)) $error="Impossible to write into the file [$strFilename].";
  if ( empty($error) )
  {
  if (!$handle = fopen($strFilename, 'w')) $error="Impossible to open the file [$strFilename].";
  }
  if ( empty($error) )
  {
  if ( fwrite($handle, $content)===FALSE ) $error="Impossible to write into the file [$strFilename].";
  fclose($handle);
  }

  // exit
  if ( empty($error) )
  {
  $_SESSION['pagedialog']='O|'.$L['S_save'];
  }
  else
  {
  $_SESSION['pagedialog']='E|'.$error;
  }
}

// --------
// HTML START
// --------

// prepare section settings

$_SESSION[QT]['m_map'] = array();
if ( file_exists('qnmm_map/config.php') ) require_once('qnmm_map/config.php');
foreach($oVIP->domains as $domid=>$domain) {
foreach($arrSections[$domid] as $key=>$arrSection) {
  if ( !isset($_SESSION[QT]['m_map']['s'.$key]) ) $_SESSION[QT]['m_map']['s'.$key] = 'set=0';
}}

include('qnm_adm_inc_hd.php');

echo '<form method="post" action="',$oVIP->selfurl,'">
<table class="data_o" cellspacing="0">
';
echo '<tr class="data_o">
<td class="headgroup" colspan="3">',$L['map_adm']['Mapping_settings'],'</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst"><label for="m_map_gkey">',$L['map_adm']['API_key'],'</label></td>
<td colspan="2"><input type="text" id="m_map_gkey" name="m_map_gkey" class="small" size="60" maxlength="100" value="',$_SESSION[QT]['m_map_gkey'],'" style="background-color:#FFFF99" /></td>
</tr>
';
// ----------
if ( !empty($_SESSION[QT]['m_map_gkey']) ) {
// ----------
echo '<tr class="data_o">
<td class="headfirst">',$L['map_adm']['API_ctrl'],'</td>
<td colspan="2">
<input type="checkbox" id="streetview" name="streetview"'.(substr($_SESSION[QT]['m_map_gbuttons'],1,1)=='1' ? QCHE : '').' style="vertical-align: middle" onchange="bEdited=true;"/><label for="streetview" class="small">',$L['map_adm']['Ctrl_streetview'],'</label>
&nbsp;<input type="checkbox" id="map" name="map"'.(substr($_SESSION[QT]['m_map_gbuttons'],2,1)=='1' ? QCHE : '').' style="vertical-align: middle" /> <label for="map" class="small">',$L['map_adm']['Ctrl_background'],'</label>
&nbsp;<input type="checkbox" id="scale" name="scale"'.(substr($_SESSION[QT]['m_map_gbuttons'],3,1)=='1' ? QCHE : '').' style="vertical-align: middle" /> <label for="scale" class="small">',$L['map_adm']['Ctrl_scale'],'</label>
&nbsp;<input type="checkbox" id="overview" name="overview"'.(substr($_SESSION[QT]['m_map_gbuttons'],4,1)=='1' ? QCHE : '').' style="vertical-align: middle" /> <label for="overview" class="small">',$L['map_adm']['Ctrl_overview'],'</label>
&nbsp;<input type="checkbox" id="mousewheel" name="mousewheel"'.(substr($_SESSION[QT]['m_map_gbuttons'],5,1)=='1' ? QCHE : '').' style="vertical-align: middle" /> <label for="mousewheel" class="small">',$L['map_adm']['Ctrl_mousewheel'],'</label>
</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst">',$L['map_adm']['Allowed'],'</td>
<td colspan="2">
<table class="gmapsections">
<tr>
<td class="header">&nbsp;</td>
<td class="header">',$L['Sections'],'</td>
<td class="header">',$L['map_adm']['Symbol'],'</td>
<td class="header">',$L['map_adm']['Main_list'],'</td>
</tr>
';

foreach($oVIP->domains as $domid=>$domain) {
if ( isset($arrSections[$domid]) ) {
if ( count($arrSections[$domid])>0 ) {

  echo '<tr><td class="header">&nbsp;</td><td class="domain" colspan="3">',$domain,'</td></tr>',PHP_EOL;

  foreach($arrSections[$domid] as $key=>$arrSection)
  {
    $arr = QTexplode($_SESSION[QT]['m_map']['s'.$key]); // return at least the 'set'
    if ( !isset($arr['set']) ) $arr['set']='0';
echo '
<tr>
<td class="header"><input type="checkbox" id="sec_',$key,'" name="sec_',$key,'"'.($arr['set']=='1' ? QCHE : '').' style="vertical-align: middle" onclick="mapsection(\'',$key,'\')" /></td>
<td><label for="sec_',$key,'">',$arrSection['title'],'</label></td>
<td>
<select class="small" id="mark_',$key,'" name="mark_',$key,'" size="1" style="',($arr['set']=='1' ? '' : 'visibility:hidden'),'">
',($key=='S' ? '<option value="S">'.L('Section').'</option>' : ''),'
<option value="0">',$L['map_adm']['Default'],'</option>
<option value="-" disabled="disabled">-----------</option>
',QTasTag($arrFiles,(isset($arr['icon']) ? $arr['icon'] : null)),'
</select>
</td>
<td><select class="small" id="list_',$key,'" name="list_',$key,'" size="1" style="',($arr['set']=='1' ? '' : 'visibility:hidden'),'">',QTasTag($L['map_adm']['List'],(isset($arr['list']) ? $arr['list'] : null)),'</select></td>
</tr>
';
  }
}}}

echo '</table>
</td>
</tr>
';
echo '<tr class="data_o">
<td class="headgroup" colspan="3">',$L['map_adm']['Mapping_config'],'</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst"><label for="m_map_gcenter">',$L['map_adm']['Center'],'</label></td>
<td><input type="text" id="m_map_gcenter" name="m_map_gcenter" size="28" maxlength="100" value="',$_SESSION[QT]['m_map_gcenter'],'" /><span class="small"> ',$L['map_adm']['Latlng'],'</span></td>
<td><span class="help">',$L['map_adm']['H_Center'],'</span></td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst"><label for="m_map_gzoom">',$L['map_adm']['Zoom'],'</label></td>
<td>
<input type="text" id="m_map_gzoom" name="m_map_gzoom" size="2" maxlength="2" value="',$_SESSION[QT]['m_map_gzoom'],'" /></td>
<td><span class="help">',$L['map_adm']['H_Zoom'],'</span></td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst">',$L['map_adm']['Background'],'</td>
<td><select id="maptype" name="maptype" size="1">',QTasTag(array('M'=>'Map','S'=>'Satellite','H'=>'Hybrid (satellite+labels)','P'=>'Physical (terrain)'),substr($_SESSION[QT]['m_map_gbuttons'],0,1)),'</select></td>
<td><span class="help">',$L['map_adm']['H_Background'],'</span></td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst"><label for="m_map_gfind">',$L['map_adm']['Address_sample'],'</label></td>
<td><input type="text" id="m_map_gfind" name="m_map_gfind" size="20" maxlength="100" value="',$_SESSION[QT]['m_map_gfind'],'" /></td>
<td class="colct"><span class="help">',$L['map_adm']['H_Address_sample'],'</span></td>
</tr>
';
// ----------
}
// ----------
echo '<tr class="data_o">
<td class="headgroup" colspan="3" style="padding:6px; text-align:center"><input type="submit" name="ok" value="',$L['Save'],'" /></td>
</tr>
';
echo '</table>
</form>
';


// --- Show map ---

if ( !empty($_SESSION[QT]['m_map_gkey']) )
{
  echo '<div class="gmap">',PHP_EOL;
  echo '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">',$L['map']['canmove'],' | <a class="small" href="javascript:void(0)" onclick="undoChanges(); return false;">',$L['map']['undo'],'</a></p>',PHP_EOL;
  echo '<div id="map_canvas"></div>',PHP_EOL;
  echo '<p class="small commands" style="margin:4px 0 2px 2px;text-align:right">',$L['map']['addrlatlng'];
  echo ' <input type="text" size="24" id="find" name="find" class="small" value="'.$_SESSION[QT]['m_map_gfind'].'" title="'.$L['map']['H_addrlatlng'].'" onkeypress="enterkeyPressed=qtKeyEnter(event); if (enterkeyPressed) showLocation(this.value,null);"/>';
  echo '<img id="findit" src="qnmm_map_find.png" alt="find" onclick="showLocation(document.getElementById(\'find\').value,null);" title="'.L('Search').'"/>',PHP_EOL;
  echo '</div>',PHP_EOL;
}
else
{
  echo '<p class="disabled">',$L['map']['E_disabled'],'</p>';
}


echo '<h2>',$L['map_adm']['Other_symbols'],'</h2>
<table class="data_o" cellspacing="0">
';
foreach($arrFiles as $strFile=>$strName)
{
  echo '<tr class="data_o"><td class="colct"><img src="qnmm_map/',$strFile,'.png" /></td><td class="colct"><span class="small">',$strName,'</span></td></tr>';
}
echo '
</table>
';

// HTML END

if ( !empty($_SESSION[QT]['m_map_gkey']) )
{
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_map_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_map_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();

  $gmap_markers[] = QTgmapMarker($_SESSION[QT]['m_map_gcenter'],true,$gmap_symbol,$L['map_adm']['Center'].' ('.$L['map']['canmove'].')','',$gmap_shadow);
  $gmap_events[] = '
	google.maps.event.addListener(marker, "position_changed", function() {
		if (document.getElementById("m_map_gcenter")) {document.getElementById("m_map_gcenter").value = gmapRound(marker.getPosition().lat(),10) + "," + gmapRound(marker.getPosition().lng(),10);}
	});
	google.maps.event.addListener(marker, "dragend", function() {
		map.panTo(marker.getPosition());
	});';
  $gmap_functions[] = '
  function undoChanges()
  {
  	if (infowindow) infowindow.close();
  	if (markers[0]) markers[0].setPosition(mapOptions.center);
  	if (mapOptions) map.panTo(mapOptions.center);
  	return null;
  }
  function showLocation(address,title)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] )
        {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: title});
        }
        gmapYXfield("qnm_gcenter",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  ';
  include 'qnmm_map_load.php';
}

include('qnm_adm_inc_ft.php');